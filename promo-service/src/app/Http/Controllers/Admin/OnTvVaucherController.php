<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ImportVaucherJob;
use App\Models\OntvVaucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class OnTvVaucherController extends Controller
{
    public function data(Request $request)
    {
        $query = OntvVaucher::with('user') // agar user bilan bog‘langan bo‘lsa
            ->select('ontv_vauchers.*');

        return DataTables::of($query)
            ->addColumn('code', fn($item) => $item->code)
            ->addColumn('assigned', fn($item) => $item->isAssigned()
                ? '<span class="badge bg-success">Berilgan</span>'
                : '<span class="badge bg-warning">Berilmagan</span>')
            ->addColumn('used', fn($item) => $item->isUsed()
                ? '<span class="badge bg-danger">Ishlatilgan</span>'
                : '<span class="badge bg-success">Ishlatilmagan</span>')
            ->addColumn('valid', fn($item) => $item->isValid()
                ? '<span class="badge bg-success">Amalda</span>'
                : '<span class="badge bg-secondary">Amaldan chiqqan</span>')
            ->addColumn('expires_at', fn($item) => optional($item->expires_at)->format('d.m.Y') ?? '-')
            ->addColumn('user', fn($item) => optional($item->user)->name ?? '-')
            ->addColumn('actions', function ($row) {
                return view('admin.actions', [
                    'row' => $row,
                    'routes' => [
                        'edit' => "/admin/voucher/{$row->id}/edit",
                        'delete' => "/admin/voucher/{$row->id}/delete",
                        'show' => "/admin/voucher/{$row->id}/show",
                    ],
                ])->render();
            })
            ->rawColumns(['assigned', 'used', 'valid', 'actions'])
            ->make(true);

    }
    public function store(Request $request)
    {

        $validated = $request->validate([
            'voucher_code' => 'required|string|max:100|unique:ontv_vauchers,code',
        ]);



        // ✅ Voucher yaratish
        $voucher = OntvVaucher::create([
            'code' => $validated['voucher_code'],
            'user_id' => null, // hali foydalanuvchiga berilmagan
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Voucher muvaffaqiyatli yaratildi!',
            'data' => $voucher,
        ]);
    }

       public function import(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
            'created_by_user_id' => 'required|integer',
        ]);
        OntvVaucher::truncate();

        $path = $request->file('file')->store('promo-imports', 'public');
        $fullPath = storage_path('app/public/' . $path);
        try {
            $sheets = Excel::toArray(null, $fullPath);
            // $sheets = Excel::toArray(new \stdClass, $fullPath);
        } catch (\Throwable $e) {
            Log::error("❌ Excel faylni o'qishda xatolik: " . $e->getMessage());
            return response()->json([
                'message' => 'Validation Error',
                'errors' => [
                    'file' => ['Excel faylni o‘qib bo‘lmadi. Iltimos, formatni tekshiring.'],
                ],
            ], 422);
        }

        if (empty($sheets) || empty($sheets[0])) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => [
                    'file' => ['Excel fayl bo‘sh yoki noto‘g‘ri formatda.'],
                ],
            ], 422);
        }
        $rows = $sheets[0];
        $header = $sheets[0][0] ?? [];
        if (!in_array('vaucher', array_map('strtolower', $header))) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => [
                    'file' => ["Excel faylda 'vaucher' ustuni topilmadi. Birinchi qatorda ustun nomlari bo‘lishi va 'promocode' degan ustun mavjud bo‘lishi shart."],
                ],
            ], 422);
        }
        $promoRows = array_slice($rows, 1);
        if (count($promoRows) > 60000) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => [
                    'file' => ["Excel faylda promo kodlar soni 10,000 tadan oshmasligi kerak. Siz yuborgansiz: " . count($promoRows)],
                ],
            ], 422);
        }
        Queue::connection('rabbitmq')->push(new ImportVaucherJob(
            $validated['created_by_user_id'],
            $path,
        ));

        return response()->json([
            'message' => "✅ Promo kod import jarayoni queue orqali boshlandi.",
        ]);
    }

    public function getTelegram(Request $request)
    {
        $userId = $request->input('user_id');

        // Allaqachon voucher olganmi? Tekshiramiz va agar bo‘lsa darrov javob beramiz
        $userVoucher = OntvVaucher::where('user_id', $userId)->first();

        if ($userVoucher) {
            return response()->json([
                'is_new' => false,
                'code' => null,
                'url' => 'https://promobank.io/namuna/video6.mp4'
            ]);
        }

        // Agar hech qachon voucher olmagan bo'lsa — bir dona bo‘sh voucher olamiz
        $voucher = OntvVaucher::whereNull('user_id')->first();

        if (!$voucher) {
            return response()->json([
                'is_new' => false,
                'code' => null,
                'url' => null,
                'message' => 'Voucherlar tugadi'
            ]);
        }

        // Voucher’ni biriktiramiz
        $voucher->update([
            'user_id' => $userId,
            'used_at' => now()
        ]);

        return response()->json([
            'is_new' => true,
            'code' => $voucher->code,
            'url' => 'https://promobank.io/namuna/video6.mp4'
        ]);
    }
}

<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\StoreUploadedMediaJob;
use App\Models\Company;
use App\Models\SocialType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Yajra\DataTables\Facades\DataTables;

class CompanyController extends Controller
{

    public function data(Request $request)
    {
        $locale = $request->get('locale', 'uz');
        $query  = Company::select('companies.*');
        return DataTables::of($query)
            ->addColumn('name', fn($row) => $row->getTranslation('name', $locale) ?? '')
            ->addColumn('title', fn($row) => $row->getTranslation('title', $locale) ?? '')
            ->addColumn('description', fn($row) => $row->getTranslation('description', $locale) ?? '')
            ->addColumn('status', function ($data) {
                switch ((int) $data->status) {
                    case 1:
                        return '<span class="badge bg-success bg-opacity-10 text-success">Faol</span>';
                    case 0:
                        return '<span class="badge bg-secondary bg-opacity-10 text-secondary">Nofaol</span>';
                    default:
                        return '<span class="badge bg-info bg-opacity-10 text-info">Kutilmoqda</span>';
                }
            })
            ->addColumn('actions', function ($row) {
                return view('admin.actions', [
                    'row'    => $row,
                    'routes' => [
                        'edit'   => "/admin/company/{$row->id}/edit",
                        'delete' => "/admin/company/{$row->id}/delete",
                        'status' => "/admin/company/{$row->id}/status",
                    ],
                ])->render();
            })
            ->rawColumns(['actions', 'status']) // HTML ustunlar
            ->make(true);
    }
    public function delete(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $company->delete();
        return redirect()->back()->with('success', 'Foydalanuvchi o‘chirildi.');
    }

    public function changeStatus(Request $request, $id)
    {
        $company         = Company::findOrFail($id);
        $company->status = ! $company->status;
        $company->save();
        Log::info('User status changed', [
            'user_id'    => $company->id,
            'new_status' => $company->status,
        ]);
        return response()->json([
            'message' => 'Status yangilandi',
            'status'  => $company->status,
        ]);
    }

    public function edit($id)
    {
        $company     = Company::findOrFail($id);
        $social_type = SocialType::select("id", "name")->get();
        return response()->json([
            'data'         => $company,
            "select_types" => $social_type,
        ]);
    }
    public function update(Request $request, $id)
    {
        $company   = Company::findOrFail($id);
        $validated = $request->validate([
            'name'           => 'required|array',
            'name.uz'        => 'required|string|max:255',
            'name.ru'        => 'required|string|max:255',
            'name.kr'        => 'required|string|max:255',

            'title'          => 'nullable|array',
            'title.uz'       => 'nullable|string|max:255',
            'title.ru'       => 'nullable|string|max:255',
            'title.kr'       => 'nullable|string|max:255',

            'description'    => 'nullable|array',
            'description.uz' => 'nullable|string|max:1000',
            'description.ru' => 'nullable|string|max:1000',
            'description.kr' => 'nullable|string|max:1000',
            'email'          => 'required|email|max:255|unique:companies,email,' . $company->id, 'region' => 'required|string|max:255',
            'address'        => 'required|string|max:255',
            'user_id'        => 'required|string|max:255',
            'status'         => 'nullable|boolean', // we'll parse manually
            'logo'           => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

if ($request->hasFile('logo')) {
    $file     = $request->file('logo');
    $tempPath = $file->store('tmp', 'public');
    Log::info("📎 logo mavjud. Yuklanmoqda..." . $tempPath);
    Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'logo', $company->id));
}

        $company->update($validated);

        return response()->json(['message' => 'Foydalanuvchi yangilandi.']);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Translatable JSON fieldlar
            'name'               => 'required|array',
            'name.uz'            => 'required|string|max:255',
            'name.ru'            => 'required|string|max:255',
            'name.kr'            => 'required|string|max:255',

            'title'              => 'nullable|array',
            'title.uz'           => 'nullable|string|max:255',
            'title.ru'           => 'nullable|string|max:255',
            'title.kr'           => 'nullable|string|max:255',

            'description'        => 'nullable|array',
            'description.uz'     => 'nullable|string|max:1000',
            'description.ru'     => 'nullable|string|max:1000',
            'description.kr'     => 'nullable|string|max:1000',
            'created_by_user_id' => 'required|string|max:255',
            'email'              => 'required|email|max:255|unique:companies,email',
            'region'             => 'required|string|max:255',
            'address'            => 'required|string|max:255',
            'user_id'            => 'required|string|max:255',
            'status'             => 'required|boolean', // we'll parse manually
            'logo'               => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);
        Log::info("Yangi logo mavjud: ", ['data' => $validated]);

        // Fayl bor bo‘lsa saqlaymiz
        if ($request->hasFile('logo')) {
            $file     = $request->file('logo');
            $tempPath = $file->store('tmp', 'public');
            Log::info("Yangi logo mavjud: " . $tempPath);
            // Queue::connection('rabbitmq')->push(new CompanyCreateLogoJob($validated['user_id'], $tempPath));
            // Agar sizga `logo` ni to‘g‘ridan-to‘g‘ri companyga saqlash kerak bo‘lsa, bu yerda yozing
        }

        // Yangi yozuv yaratish
        $company = Company::create($validated);

        return response()->json([
            'message' => 'Kompaniya yaratildi.',
            'data'    => $company,
        ]);
    }
}

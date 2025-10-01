<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{

    /**
     * DataTables uchun ma’lumotlar
     */


    public function index(){
        $query = Contact::query()
            ->orderBy('position')
            ->orderByDesc('id')->get();
        return $this->successResponse($query,"success");

    }
    public function data(Request $request)
    {
        $query = Contact::query()
            ->orderBy('position')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', (int) $request->status);
        }

        return DataTables::of($query)
            ->addColumn('id', fn($item) => $item->id)
            ->addColumn('type', fn($item) => e($item->type))
            ->addColumn('url', fn($item) => e($item->url))
            ->addColumn('label', fn($item) => $item->getTranslation('label', 'uz') ?? '-')
            ->addColumn('position', fn($item) => $item->position)
            ->addColumn('status', fn($item) =>
                $item->status
                    ? '<span class="badge bg-success">Faol</span>'
                    : '<span class="badge bg-secondary">Nofaol</span>'
            )
            ->addColumn('actions', function ($row) {
                return view('admin.actions', [
                    'row' => $row,
                    'routes' => [
                    'edit'   => "/admin/contacts/{$row->id}/edit",
                    'delete' => "/admin/contacts/{$row->id}/delete",
                    'status' => "/admin/contacts/{$row->id}/status",
                    ],
                ])->render();
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    /**
     * Yaratish (store)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'       => 'required|string|max:50',
            'url'        => 'required|string|max:1024',
            'label.uz'   => 'nullable|string|max:255',
            'label.ru'   => 'nullable|string|max:255',
            'label.kr'   => 'nullable|string|max:255',
            'position'   => 'required|integer|min:0',
            'status'     => 'nullable|boolean',
        ]);

        $contact = Contact::create([
            'type'     => $validated['type'] ?? null,
            'url'      => $validated['url'],
            'label'    => $validated['label'] ?? [],
            'position' => $validated['position'],
            'status'   => $validated['status'] ?? 1,
        ]);

        return response()->json([
            'message' => 'Kontakt muvaffaqiyatli qo‘shildi ✅',
            'data'    => $contact,
        ]);
    }

    /**
     * Edit uchun ma’lumot
     */
    public function edit($id)
    {
        $contact = Contact::findOrFail($id);

        return response()->json([
            'contact' => [
                'id'       => $contact->id,
                'type'     => $contact->type,
                'url'      => $contact->url,
                'label'    => $contact->getTranslations('label'),
                'position' => $contact->position,
                'status'   => $contact->status,
            ],
        ]);
    }

    /**
     * Yangilash
     */
    public function update(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);

        $validated = $request->validate([
            'type'       => [
                'nullable',
                'string',
                'max:50',
                Rule::in([
                    'address',
                    'phone',
                    'email',
                    'whatsapp',
                    'telegram',
                    'linkedin',
                    'facebook',
                    'instagram',
                ]),
            ],
            'url'        => 'required|string|max:1024',
            'label.uz'   => 'nullable|string|max:255',
            'label.ru'   => 'nullable|string|max:255',
            'label.kr'   => 'nullable|string|max:255',
            'position'   => 'required|integer|min:0',
            'status'     => 'nullable|boolean',
        ]);

        $contact->update([
            'type'     => $validated['type'] ?? $contact->type,
            'url'      => $validated['url'],
            'label'    => $validated['label'] ?? $contact->label,
            'position' => $validated['position'],
            'status'   => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kontakt muvaffaqiyatli yangilandi ✅',
            'data'    => $contact->fresh(),
        ]);
    }

    /**
     * Statusni almashtirish
     */
    public function changeStatus($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->status = !$contact->status;
        $contact->save();

        return response()->json([
            'success' => true,
            'status'  => $contact->status,
        ]);
    }

    /**
     * O‘chirish
     */
    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        return response()->json([
            'success' => "Kontakt muvaffaqiyatli o‘chirildi ✅",
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SocialLink;
use Yajra\DataTables\DataTables;
class SocialsController extends Controller
{
    /**
     * DataTables uchun ma’lumotlar
     */
    public function data(Request $request)
    {
        $query = SocialLink::query()
            ->orderBy('position')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('id', fn($item) => $item->id)
            ->addColumn('type', fn($item) => $item->type)
            ->addColumn('url', fn($item) => e($item->url))
            ->addColumn('label', fn($item) => $item->getTranslation('label', 'uz') ?? '-')
            ->addColumn('position', fn($item) => $item->position)
            ->addColumn(
                'status',
                fn($item) =>
                $item->status
                    ? '<span class="badge bg-success">Faol</span>'
                    : '<span class="badge bg-secondary">Nofaol</span>'
            )
            ->addColumn('actions', function ($row) {
                return view('admin.actions', [
                    'row'    => $row,
                    'routes' => [
                        'edit'   => "/admin/socials/{$row->id}/edit",
                        'delete' => "/admin/socials/{$row->id}/delete",
                        'status' => "/admin/socials/{$row->id}/status",
                    ],
                ])->render();
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    /**
     * Yaratish
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'         => 'required|string|max:50',
            'url'          => 'required|url|max:1024',
            'label.uz'     => 'nullable|string|max:255',
            'label.ru'     => 'nullable|string|max:255',
            'label.kr'     => 'nullable|string|max:255',
            'label.en' => 'nullable|string|max:255',
            'position'     => 'required|integer|min:0',
            'status'       => 'nullable|boolean',
        ]);

        $socialLink = SocialLink::create([
            'type'     => $validated['type'],
            'url'      => $validated['url'],
            'label'    => $validated['label'] ?? [],
            'position' => $validated['position'],
            'status'   => $validated['status'] ?? 1,
        ]);

        return response()->json([
            'message' => 'Social link muvaffaqiyatli qo‘shildi ✅',
            'data'    => $socialLink,
        ]);
    }

    /**
     * Edit
     */
    public function edit($id)
    {
        $socialLink = SocialLink::findOrFail($id);

        return response()->json([
            'socialLink' => [
                'id'       => $socialLink->id,
                'type'     => $socialLink->type,
                'url'      => $socialLink->url,
                'label'    => $socialLink->getTranslations('label'),
                'position' => $socialLink->position,
                'status'   => $socialLink->status,
            ],
        ]);
    }

    /**
     * Yangilash
     */
    public function update(Request $request, $id)
    {
        $socialLink = SocialLink::findOrFail($id);

        $validated = $request->validate([
            'type'         => 'required|string|max:50',
            'url'          => 'required|url|max:1024',
            'label.uz'     => 'nullable|string|max:255',
            'label.ru'     => 'nullable|string|max:255',
            'label.kr'     => 'nullable|string|max:255',
            'label.en' => 'nullable|string|max:255',
            'position'     => 'required|integer|min:0',
            'status'       => 'required|boolean',
        ]);

        $socialLink->update([
            'type'     => $validated['type'],
            'url'      => $validated['url'],
            'label'    => $validated['label'] ?? $socialLink->label,
            'position' => $validated['position'],
            'status'   => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Social link muvaffaqiyatli yangilandi ✅',
            'data'    => $socialLink->fresh(),
        ]);
    }

    /**
     * Statusni almashtirish
     */
    public function changeStatus($id)
    {
        $socialLink = SocialLink::findOrFail($id);
        $socialLink->status = !$socialLink->status;
        $socialLink->save();

        return response()->json(['success' => true, 'status' => $socialLink->status]);
    }

    /**
     * O‘chirish
     */
    public function destroy($id)
    {
        $socialLink = SocialLink::findOrFail($id);
        $socialLink->delete();

        return response()->json(['success' => "Social link muvaffaqiyatli o‘chirildi."]);
    }
}

<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotions;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PromotionController extends Controller
{
    public function __construct()
    {

    }
    public function companydata(Request $request, $id)
    {
        $locale = $request->get('locale', app()->getLocale());

        $query = Promotions::with('company')
            ->where('company_id', $id)
            ->select('promotions.*');

        return DataTables::of($query)
            ->addColumn('name', fn($item) => $item->getTranslation('name', $locale) ?? '-')
            ->addColumn('title', fn($item) => $item->getTranslation('title', $locale) ?? '-')
            ->addColumn('description', fn($item) => $item->getTranslation('description', $locale) ?? '-')
            ->addColumn('is_active', fn($item) => $item->is_active
                ? '<span class="badge bg-success">Faol</span>'
                : '<span class="badge bg-danger">Nofaol</span>'
            )
            ->addColumn('is_public', fn($item) => $item->is_public
                ? '<i class="ph ph-check-circle text-success"></i>'
                : '<i class="ph ph-x-circle text-danger"></i>'
            )
            ->addColumn('start_date', fn($item) => optional($item->start_date)->format('d.m.Y') ?? '-')
            ->addColumn('end_date', fn($item) => optional($item->end_date)->format('d.m.Y') ?? '-')
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
            ->rawColumns(['is_active', 'is_public', 'actions'])
            ->make(true);
    }
    // public function edit($id)
    // {
    //     $user     = Promotions::with('roles:id,name')->findOrFail($id);
    //     $region   = Region::select(['id', 'name'])->get();
    //     $district = District::where('region_id', $user['region_id'])->select('id', 'name')->get();
    //     $allRoles = \Spatie\Permission\Models\Role::select(['id', 'name'])->get();
    //     Log::info('user', ['user' => $user]);
    //     return response()->json([
    //         'user'     => $user,
    //         'region'   => $region,
    //         'roles'    => $user->roles->pluck('name'),
    //         'allRoles' => $allRoles,
    //         'district' => $district,
    //     ]);
    // }
    // public function update(Request $request, $id)
    // {
    //     $user      = Promotions::findOrFail($id);
    //     $validated = $request->validate([
    //         'name'        => 'required|string|max:255',
    //         'email'       => 'nullable|email|max:255|unique:users,email,' . $user->id,
    //         'phone'       => 'required|string|max:50|regex:/^\+?\d{7,50}$/|unique:users,phone,' . $user->id,
    //         'phone2'      => 'nullable|string|max:50|regex:/^\+?\d{7,50}$/',
    //         'region_id'   => 'required|exists:regions,id',
    //         'district_id' => 'required|exists:districts,id',
    //         'birthdate'   => [
    //             'required',
    //             'date_format:Y-m-d',
    //             'before_or_equal:today',
    //         ],
    //         'chat_id'     => 'required|string|max:50|unique:users,chat_id,' . $user->id,
    //         'gender'      => 'required|in:M,F',
    //         'image'       => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
    //     ]);
    //     if ($request->hasFile('image')) {
    //         $file     = $request->file('image');
    //         $tempPath = $file->store('tmp', 'public');
    //         Log ::info("image mavjud" . $tempPath);
    //         // Queue::connection('rabbitmq')->push(new UserUpdateMediaJob($user['id'], $tempPath));
    //     }

    //     $user->update($validated);

    //     return response()->json(['message' => 'Foydalanuvchi yangilandi.']);
    // }

    // public function delete(Request $request, $id)
    // {
    //     $user = Promotions::findOrFail($id);
    //     $user->delete();
    //     return redirect()->back()->with('success', 'Foydalanuvchi o‘chirildi.');
    // }

    // public function changeStatus(Request $request, $id)
    // {
    //     $user         = Promotions::findOrFail($id);
    //     $user->status = ! $user->status;
    //     $user->save();
    //     Log::info('User status changed', [
    //         'user_id'    => $user->id,
    //         'new_status' => $user->status,
    //     ]);
    //     return response()->json([
    //         'message' => 'Status yangilandi',
    //         'status'  => $user->status,
    //     ]);
    // }
}
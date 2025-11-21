<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\StoreUploadedMediaJob;
use App\Models\District;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function data(Request $request)
    {
        $query = User::with(['roles', 'region:id,name', 'district:id,name'])->select('users.*');
        return DataTables::of($query)
            ->addColumn('region', fn($user) => optional($user->region)->name ?? '-')
            ->addColumn('district', fn($user) => optional($user->district)->name ?? '-')
            ->addColumn('roles', fn($user) => $user->roles->pluck('name')->join(', '))
            ->addColumn('status', function ($user) {
                switch ((int) $user->status) {
                    case 1:
                        return '<span class="badge bg-success bg-opacity-10 text-success">Faol</span>';
                    case 0:
                        return '<span class="badge bg-secondary bg-opacity-10 text-secondary">Nofaol</span>';
                    default:
                        return '<span class="badge bg-info bg-opacity-10 text-info">Kutilmoqda</span>';
                }
            })
            ->addColumn('actions', function ($user) {
                return view('admin.actions', compact('user'))->render();
            })
            ->editColumn('gender', fn($user) => match ($user->gender) {
                'M' => 'Erkak',
                'F' => 'Ayol',
                default => 'Nomaʼlum'
            })
            ->editColumn('birthdate', function ($user) {
                try {
                    return $user->birthdate ? \Carbon\Carbon::parse($user->birthdate)->format('d.m.Y') : '-';
                } catch (\Exception $e) {
                    return '-';
                }
            })->rawColumns(['actions', 'status']) // HTML ustunlar
            ->make(true);
    }
    public function edit($id)
    {
        $user = User::with(['roles:id,name', 'region:id,name', 'district:id,name'])
            ->findOrFail($id);
        $regions = Region::select('id', 'name')->get();
        $districts = $user->region_id
            ? District::where('region_id', $user->region_id)->select('id', 'name')->get()
            : collect();
        $allRoles = Role::select('id', 'name')->get();
        return response()->json([
            'user' => $user,
            'region' => $regions,
            'district' => $districts,
            'roles' => $user->roles->pluck('name'),
            'allRoles' => $allRoles,
        ]);
    }


    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:50|regex:/^\+?\d{7,50}$/|unique:users,phone,' . $user->id,
            'phone2' => 'nullable|string|max:50|regex:/^\+?\d{7,50}$/',
            'region_id' => 'nullable|exists:regions,id',
            'district_id' => 'nullable|exists:districts,id',
            'birthdate' => 'nullable|date_format:Y-m-d|before_or_equal:today',
            'chat_id' => 'nullable|string|max:50|unique:users,chat_id,' . $user->id,
            'gender' => 'nullable|in:M,F,U',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $user->update($validated);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('tmp', 'public');
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($path, 'user_avatar', $user->id));
        }

        return response()->json(['message' => 'Foydalanuvchi yangilandi.']);
    }


    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Foydalanuvchi o‘chirildi.']);
    }
    public function changeStatus($id)
    {
        $user = User::findOrFail($id);
        $user->status = (int) !$user->status;
        $user->save();

        return response()->json([
            'message' => 'Status yangilandi',
            'status' => $user->status,
        ]);
    }
    public function getClients()
    {
        $clients = User::role('client')
            ->with(['roles:id,name', 'region:id,name', 'district:id,name'])
            ->get();
        return response()->json($clients);
    }
}
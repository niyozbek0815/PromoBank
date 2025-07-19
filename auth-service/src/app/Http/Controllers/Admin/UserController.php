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
                'M'                              => 'Erkak',
                'F'     => 'Ayol',
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
        $user     = User::with('roles:id,name')->findOrFail($id);
        $region   = Region::select(['id', 'name'])->get();
        $district = District::where('region_id', $user['region_id'])->select('id', 'name')->get();
        $allRoles = \Spatie\Permission\Models\Role::select(['id', 'name'])->get();
        Log::info('user', ['user' => $user]);
        return response()->json([
            'user'     => $user,
            'region'   => $region,
            'roles'    => $user->roles->pluck('name'),
            'allRoles' => $allRoles,
            'district' => $district,
        ]);
    }
    public function update(Request $request, $id)
    {
        $user      = User::findOrFail($id);
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'phone'       => 'required|string|max:50|regex:/^\+?\d{7,50}$/|unique:users,phone,' . $user->id,
            'phone2'      => 'nullable|string|max:50|regex:/^\+?\d{7,50}$/',
            'region_id'   => 'required|exists:regions,id',
            'district_id' => 'required|exists:districts,id',
            'birthdate'   => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'chat_id'     => 'required|string|max:50|unique:users,chat_id,' . $user->id,
            'gender'      => 'required|in:M,F',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $tempPath = $file->store('tmp', 'public');
            Log::info("image mavjud" . $tempPath);
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'user_avatar', $user->id));
        }

        $user->update($validated);

        return response()->json(['message' => 'Foydalanuvchi yangilandi.']);
    }

    public function delete(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->back()->with('success', 'Foydalanuvchi o‘chirildi.');
    }

    public function changeStatus(Request $request, $id)
    {
        $user         = User::findOrFail($id);
        $user->status = ! $user->status;
        $user->save();
        Log::info('User status changed', [
            'user_id'    => $user->id,
            'new_status' => $user->status,
        ]);
        return response()->json([
            'message' => 'Status yangilandi',
            'status'  => $user->status,
        ]);
    }
    public function getClients()
    {
        $clients = User::role('client')->with(['roles', 'region:id,name', 'district:id,name'])->get();
        Log::info('Clients fetched', ['clients' => $clients]);
        return response()->json(['clients' => $clients]);
    }
}

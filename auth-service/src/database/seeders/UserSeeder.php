<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // User roli

        // User foydalanuvchisi

        $userPermission   = Permission::firstOrCreate(['name' => 'read:own_data', 'guard_name' => 'web']);
        $adminPermission1 = Permission::firstOrCreate(['name' => 'create:admin', 'guard_name' => 'web']);
        $adminPermission2 = Permission::firstOrCreate(['name' => 'read:admin', 'guard_name' => 'web']);
        $adminPermission3 = Permission::firstOrCreate(['name' => 'update:admin', 'guard_name' => 'web']);
        $adminPermission4 = Permission::firstOrCreate(['name' => 'delete:admin', 'guard_name' => 'web']);

        //RoleModel
        $rolePermission1 = Permission::firstOrCreate(['name' => 'create:role', 'guard_name' => 'web']);
        $rolePermission2 = Permission::firstOrCreate(['name' => 'read:role', 'guard_name' => 'web']);
        $rolePermission3 = Permission::firstOrCreate(['name' => 'update:role', 'guard_name' => 'web']);
        $rolePermission4 = Permission::firstOrCreate(['name' => 'delete:role', 'guard_name' => 'web']);
        //PermissionModel
        $Permission1 = Permission::firstOrCreate(['name' => 'create:permission', 'guard_name' => 'web']);
        $Permission2 = Permission::firstOrCreate(['name' => 'read:permission', 'guard_name' => 'web']);
        $Permission3 = Permission::firstOrCreate(['name' => 'update:permission', 'guard_name' => 'web']);
        $Permission4 = Permission::firstOrCreate(['name' => 'delete:permission', 'guard_name' => 'web']);

        $clientPermission = Permission::firstOrCreate(['name' => 'read:own_data', 'guard_name' => 'web']);

        //Admins
        //Create Roles
        $superAdminRole = Role::create(['name' => 'superadmin', 'guard_name' => 'web'])->syncPermissions([
            $adminPermission1,
            $adminPermission2,
            $adminPermission3,
            $adminPermission4,
            $rolePermission1,
            $rolePermission2,
            $rolePermission3,
            $rolePermission4,
            $Permission1,
            $Permission2,
            $Permission3,
            $Permission4,

        ]);
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web'])->syncPermissions([
            $adminPermission1,
            $adminPermission2,
            $adminPermission3,
            $adminPermission4,
            $rolePermission1,
            $rolePermission2,
            $rolePermission3,
            $rolePermission4,
            $Permission1,
            $Permission2,
            $Permission3,
            $Permission4,
        ]);
        $contentMenagerRole = Role::create(['name' => 'contentmenager', 'guard_name' => 'web'])->syncPermissions([
            $adminPermission2,
            $rolePermission2,
            $Permission2,
            $adminPermission2,
        ]);
        $devoloperRole = Role::create(['name' => 'devoloper', 'guard_name' => 'web'])->syncPermissions([
            $adminPermission1,
            $adminPermission2,
            $adminPermission3,
            $adminPermission4,
            $rolePermission1,
            $rolePermission2,
            $rolePermission3,
            $rolePermission4,
            $Permission1,
            $Permission2,
            $Permission3,
            $Permission4,
        ]);
        $clientRole = Role::create(['name' => 'client', 'guard_name' => 'web'])->syncPermissions([
            $clientPermission,
        ]);
        $userRole = Role::create(['name' => 'user', 'guard_name' => 'web'])->syncPermissions([
            $userPermission,
        ]);

        User::create([
            'name'     => 'super admin',
            'email'    => "superadmin@admin.com",
            'password' => Hash::make('password'),
            'phone'    => '900000000',
            'chat_id'  => 'tg_900000000',
            'is_guest' => false,
            'status'   => true,
        ])->assignRole($superAdminRole);

        User::create([
            'name'     => 'admin',
            'email'    => "admin@admin.com",
            'password' => Hash::make('password'),
            'phone'    => '900000001',
            'chat_id'  => 'tg_900000001',
            'is_guest' => false,
            'status'   => true,
        ])->assignRole($adminRole);

        User::create([
            'name'     => 'contentMenager',
            'email'    => "moderator@admin.com",
            'password' => Hash::make('password'),
            'phone'    => '900000002',
            'chat_id'  => 'tg_900000002',
            'is_guest' => false,
            'status'   => true,
        ])->assignRole($contentMenagerRole);

        User::create([
            'name'     => 'devoloper',
            'email'    => "devoloper@admin.com",
            'password' => Hash::make('password'),
            'phone'    => '900000003',
            'chat_id'  => 'tg_900000003',
            'is_guest' => false,
            'status'   => true,
        ])->assignRole($devoloperRole);

        User::create([
            'name'     => 'client',
            'email'    => "client@admin.com",
            'password' => Hash::make('password'),
            'phone'    => '900000004',
            'chat_id'  => 'tg_900000004',
            'is_guest' => false,
            'status'   => true,
        ])->assignRole($clientRole);
        foreach (range(1, 30) as $i) {
            User::create([
                'name'     => 'oddiy foydalanuvchi',
                'email'    => "user{$i}@admin.com",
                'password' => Hash::make('password'),
                'phone'    => "90000001{$i}",
                'chat_id'  => "tg_90000001{$i}",
                'is_guest' => false,
                'status'   => true,
            ])->assignRole($userRole);
        }

    }

}
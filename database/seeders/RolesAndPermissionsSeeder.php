<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        // app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view_dashboard',
            'roles_and_permissions',
            'add_product',
            'view_product',
            'edit_product',
            'delete_product',
            'upload_product',
            'view_requisition',
            'approve_requisition',
            'view_user',
            'add_user',
            'edit_user',
            'delete_user',
            'base_user',
            'add_base_user',
            'edit_base_user',
            'delete_base_user',
            'view_report',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        // Create roles and assign permissions

        $role = Role::updateOrCreate(['name' => 'Base Users', 'guard_name' => 'web']);
        $role->givePermissionTo([
            'view_dashboard',
            'add_product',
            'view_product',
            'edit_product',
            'upload_product',
            'view_user',
            'add_user',
            'edit_user',
            'base_user',
            'add_base_user',
            'edit_base_user',
            'view_report',
        ]);

        $role = Role::updateOrCreate(['name' => 'Sub Accounts', 'guard_name' => 'web']);
        $role->givePermissionTo([
            'view_dashboard',
            'add_product',
            'view_product',
            'edit_product',
            'upload_product',
            'view_user',
            'add_user',
            'edit_user',
            'base_user',
            'add_base_user',
            'edit_base_user',
            'view_report',
        ]);

        // Management
        $role = Role::updateOrCreate(['name' => 'Administration', 'guard_name' => 'web']);
        $role->givePermissionTo([
            'view_dashboard',
            'add_product',
            'view_product',
            'edit_product',
            'delete_product',
            'upload_product',
            'view_requisition',
            'approve_requisition',
            'view_user',
            'add_user',
            'edit_user',
            'delete_user',
            'view_report',
            'base_user',
            'add_base_user',
            'edit_base_user',
            'delete_base_user',
        ]);

        // System Administrator
        $role = Role::updateOrCreate(['name' => 'System Administrator', 'guard_name' => 'web']);
        $role->givePermissionTo(Permission::all()->pluck('name'));

        // Assign role to model (e.g., Admin with ID 1)
        DB::table('model_has_roles')->insert([
            'role_id' => 4, // role_id for the Sales Supervisor
            'model_type' => 'App\Models\User',
            'model_id' => 1, // model_id for the specific Admin user
        ]);
    }
}

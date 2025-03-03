<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class RolesAndPermissionsController extends Controller
{
    // Fetch roles with pagination and search
    public function getRoles(Request $request)
    {
        $perPage = $request->per_page ?? 10; // Default to 10 items per page
        $roles = Role::orderBy('name')
            ->with('permissions')
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            });

        if ($request->list) {
            $roles = $roles->get();
        } else {
            $roles = $roles->paginate($perPage);
        }

        return ResponseBuilder::asSuccess()
            ->withMessage('Roles fetched successfully')
            ->withData(['roles' => $roles])
            ->build();
    }

    // Fetch permissions with pagination and search
    public function getPermissions(Request $request)
    {


        $permissions = Permission::all();
        return ResponseBuilder::asSuccess()
            ->withMessage('Permissions fetched successfully')
            ->withData(['permissions' => $permissions])
            ->build();

        // $perPage = $request->per_page ?? 10; // Default to 10 items per page
        // $permissions = Permission::orderBy('name')
        //     ->when($request->search, function ($query) use ($request) {
        //         $query->where('name', 'like', '%' . $request->search . '%')
        //             ->orWhere('action', 'like', '%' . $request->search . '%');
        //     });

        // if ($request->list) {
        //     $permissions = $permissions->get();
        // } else {
        //     $permissions = $permissions->paginate($perPage);
        // }

        // return ResponseBuilder::asSuccess()
        //     ->withMessage('Permissions fetched successfully')
        //     ->withData(['permissions' => $permissions])
        //     ->build();
    }

    // Fetch permissions for a specific role
    public function getRolePermissions($role_id)
    {
        $role = Role::findOrFail($role_id);
        $permissions = $role->permissions->pluck('id');

        return ResponseBuilder::asSuccess()
            ->withMessage('Role permissions fetched successfully')
            ->withData(['permissions' => $permissions])
            ->build();
    }

    // Update an existing role's permissions
    public function updateRolePermissions(Request $request, $role_id)
    {
        $validatedData = $request->validate([
            'permissions' => 'array|required',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role = Role::findOrFail($role_id);
        $role->syncPermissions($request->permissions);

        return ResponseBuilder::asSuccess()
            ->withMessage('Role permissions updated successfully')
            ->withData(['role' => $role])
            ->build();
    }

    // Create a new role and assign permissions
    public function createRole(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array|required',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web', // Adjust if necessary
        ]);

        $role->syncPermissions($request->permissions);

        return ResponseBuilder::asSuccess()
            ->withMessage('Role created successfully')
            ->withData(['role => $role'])
            ->build();
    }

    // Update an existing role
    public function updateRole(Request $request)
    {
        $validatedData = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'array|required',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role = Role::findOrFail($request->role_id);
        $role->syncPermissions($request->permissions);

        return ResponseBuilder::asSuccess()
            ->withMessage('Role updated successfully')
            ->withData(['role => $role'])
            ->build();
    }

    // Change a user's role
    public function changeRole(Request $request)
    {
        $validatedData = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'user_id' => 'required|exists:admins,id',
        ]);

        $admin = Admin::find($request->user_id);
        if (!$admin) {
            return ResponseBuilder::asError(404)
                ->withMessage('Admin not found')
                ->build();
        }

        $role = Role::find($request->role_id);
        if (!$role) {
            return ResponseBuilder::asError(404)
                ->withMessage('Role not found')
                ->build();
        }

        // Detach existing roles and assign new role
        $admin->roles()->detach();
        $admin->assignRole($role);

        return ResponseBuilder::asSuccess()
            ->withMessage('Role updated successfully')
            ->withData(['admin' => $admin])
            ->build();
    }

    public function getUserPermissions(Request $request)
    {
        // // Use the 'admin' guard to get the authenticated user
        // $user = $request->user();

        // // Check if user is authenticated
        // if (!$user) {
        //     return response()->json(['error' => 'User not authenticated'], 401);
        // }

        // // Fetch roles and permissions
        // $roles = $user->roles()->with('permissions')->get();
        // $directPermissions = $user->permissions; // Directly assigned permissions

        // // Collect permissions from roles
        // $rolePermissions = $roles->flatMap(function ($role) {
        //     return $role->permissions;
        // });

        // // Merge all permissions and remove duplicates
        // $allPermissions = $rolePermissions->merge($directPermissions)->pluck('name')->unique();
        // // dd($allPermissions);

        // return response()->json(['permissions' => $allPermissions]);

        $userId = Auth::id();

        // Fetch the roles assigned to the user
        $roles = DB::table('model_has_roles')
            ->where('model_id', $userId)
            ->pluck('role_id');

        // Fetch the permissions for the user's roles
        $permissions = DB::table('role_has_permissions')
            ->whereIn('role_id', $roles)
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->pluck('permissions.name'); // Adjust 'name' to the column that contains permission names

        return response()->json(['permissions' => $permissions]);
    }
}

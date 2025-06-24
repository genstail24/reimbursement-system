<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Reimbursements
            'reimbursement.create',
            'reimbursement.view_all',
            'reimbursement.view_own',
            'reimbursement.view_single',
            'reimbursement.delete',
            'reimbursement.approve',

            // Users
            'user.create',
            'user.view',
            'user.update',
            'user.delete',
            'user.assign_role',

            // Categories
            'category.create',
            'category.view',
            'category.update',
            'category.delete',

            // Roles
            'role.create',
            'role.view',
            'role.update',
            'role.delete',
            'role.sync_permissions',

            // Permissions
            'permission.create',
            'permission.view',
            'permission.update',
            'permission.delete',
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $admin    = Role::firstOrCreate(['name' => 'admin']);
        $manager  = Role::firstOrCreate(['name' => 'manager']);
        $employee = Role::firstOrCreate(['name' => 'employee']);

        $admin->syncPermissions($permissions);
        $manager->syncPermissions([
            'reimbursement.view_all',
            'reimbursement.view_single',
            'reimbursement.approve',

            'user.view',

            'category.view',

            // 'reimbursement.delete',
        ]);
        $employee->syncPermissions([
            'reimbursement.create',
            'reimbursement.view_own',
            'reimbursement.view_single',
            'reimbursement.delete',
        ]);
    }
}

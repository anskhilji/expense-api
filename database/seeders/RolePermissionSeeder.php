<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * The full permission catalog for the app. Add new slugs here as new
     * modules (budgets, expenses, reports, ...) come online — nothing else
     * needs to change to introduce a new permission.
     */
    private const PERMISSIONS = [
        'organization.manage'   => 'Invite members, change roles, rename the organization',
        'incomes.view'          => 'View income entries',
        'incomes.create'        => 'Log new income',
        'incomes.edit'          => 'Edit income entries',
        'incomes.delete'        => 'Delete income entries',
        'expenses.view'         => 'View expenses',
        'expenses.create'       => 'Log new expenses',
        'expenses.edit'         => 'Edit expenses',
        'expenses.delete'       => 'Delete expenses',
        'categories.manage'     => 'Create and edit expense categories',
        'budgets.manage'        => 'Allocate income into category budgets',
        'reports.view'          => 'View reports and the multi-month ledger',
        'reports.export'        => 'Export or print reports as PDF',
    ];

    /**
     * Which permissions each role gets, matching the access matrix from the
     * build plan: Owner and Admin (everything — Admin is a full-control
     * role that, unlike Owner, can be assigned to any member rather than
     * only the person who created the organization), Editor (view/create/
     * edit/delete data, no org management), Contributor (view + create
     * only), Viewer (view only).
     */
    private const ROLE_PERMISSIONS = [
        'owner' => ['*'], // every permission in PERMISSIONS
        'admin' => ['*'], // same as owner — assignable to any member
        'editor' => [
            'incomes.view', 'incomes.create', 'incomes.edit', 'incomes.delete',
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
            'reports.view', 'reports.export',
        ],
        'contributor' => [
            'incomes.view', 'incomes.create',
            'expenses.view', 'expenses.create',
            'reports.view',
        ],
        'viewer' => [
            'incomes.view', 'expenses.view', 'reports.view',
        ],
    ];

    private const ROLES = [
        'owner' => 'Owner',
        'admin' => 'Admin',
        'editor' => 'Editor',
        'contributor' => 'Contributor',
        'viewer' => 'Viewer',
    ];

    public function run(): void
    {
        $permissions = collect(self::PERMISSIONS)->map(
            fn (string $name, string $slug) => Permission::updateOrCreate(['slug' => $slug], ['name' => $name])
        );

        foreach (self::ROLES as $slug => $name) {
            $role = Role::updateOrCreate(['slug' => $slug], ['name' => $name]);

            $allowed = self::ROLE_PERMISSIONS[$slug];
            $ids = $allowed === ['*']
                ? $permissions->pluck('id')
                : $permissions->filter(fn ($p) => in_array($p->slug, $allowed, true))->pluck('id');

            $role->permissions()->sync($ids);
        }
    }
}
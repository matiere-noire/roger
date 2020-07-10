<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        
        $role1 = Role::create(['name' => 'super-admin']);
        $permissions = [];
        $permissions[0] = Permission::create(['name' => 'create_user']);
        $permissions[1] = Permission::create(['name' => 'delete_user']);
        $permissions[2] = Permission::create(['name' => 'edit_user']);
        
        $permissions[3] = Permission::create(['name' => 'create_role']);
        $permissions[4] = Permission::create(['name' => 'delete_role']);
        $permissions[5] = Permission::create(['name' => 'edit_role']);
        $role1->syncPermissions($permissions);
    }
}
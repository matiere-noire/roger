<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Http\Resources\RoleResourceCollection;

/**
 * @group Role management
 * @authenticated
 */
class RoleController extends Controller
{
    /**
     * Lists all roles
     * @return App\Http\Resources\RoleResourceCollection
     */
    public function index() : RoleResourceCollection
    {
        return new RoleResourceCollection(Role::paginate());
    }

    /**
     * store of new role
     * @param  \Illuminate\Http\Request  $request
     * @return App\Http\Resources
     */
    public function store(Request $request) : RoleResource
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
        ]);
        $role = Role::create($validatedData);
        $role->syncPermissions($request->input('permissions'));
        
        return new RoleResource($role);
    }

    /**
     * Display role by id
     *
     * @param Spatie\Permission\Models\Role $role
     * @return \App\Http\Resources
     * @throws \Exception
     */
    public function show(Role $role) : RoleResource
    {
        return new RoleResource($role);
    }

    /**
     * Update role by id
     *
     * @param Spatie\Permission\Models\Role $role
     * @param  \Illuminate\Http\Request $request
     * @return App\Http\Resources
     */
    public function update(Role $role, Request $request) : RoleResource
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
        ]);
        $role->update($request->all());
        $role->syncPermissions($request->input('permissions'));
        return new RoleResource($role);
    }

    /**
     * Remove a role
     *
     * @param Spatie\Permission\Models\Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json();
    }
}

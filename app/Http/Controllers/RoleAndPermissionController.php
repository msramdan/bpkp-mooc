<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleAndPermissionController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware(middleware: 'permission:role & permission view', only: ['index', 'show']),
            new Middleware(middleware: 'permission:role & permission create', only: ['create', 'store']),
            new Middleware(middleware: 'permission:role & permission edit', only: ['edit', 'update']),
            new Middleware(middleware: 'permission:role & permission delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::eloquent(Role::query())
                ->addColumn('action', fn (Role $role) => view('roles.include.action', ['model' => $role])->render())
                ->removeColumn('created_at')
                ->removeColumn('updated_at')
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('roles.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view(view: 'roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        return DB::transaction(callback: function () use ($request): RedirectResponse {
            $role = Role::create(attributes: ['name' => $request->name]);
            $role->givePermissionTo(permissions: $request->permissions);

            return to_route(route: 'roles.index')->with(key: 'success', value: __(key: 'The role was created successfully.'));
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Role $role): RedirectResponse|JsonResponse
    {
        $role->load('permissions');

        if ($request->expectsJson()) {
            return response()->json([
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values()->all(),
                'created_at' => $role->created_at?->format('Y-m-d H:i'),
                'updated_at' => $role->updated_at?->format('Y-m-d H:i'),
                'edit_url' => $request->user()->can('role & permission edit') ? route('roles.edit', $role) : null,
            ]);
        }

        return to_route('roles.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $role = Role::with(relations: ['permissions'])->findOrFail(id: $id);

        return view(view: 'roles.edit', data: compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, string $id): RedirectResponse
    {
        return DB::transaction(callback: function () use ($request, $id): RedirectResponse {
            $role = Role::findOrFail(id: $id);
            $role->update(attributes: ['name' => $request->name]);
            $role->syncPermissions(permissions: $request->permissions);

            return to_route(route: 'roles.index')->with(key: 'success', value: __(key: 'The role was updated successfully.'));
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        return DB::transaction(callback: function () use ($id): RedirectResponse {
            $role = Role::withCount(relations: 'users')->findOrFail(id: $id);

            if ($role->users_count < 1) {
                $role->delete();

                return to_route(route: 'roles.index')->with(key: 'success', value: __(key: 'The role was deleted successfully.'));
            }

            return to_route(route: 'roles.index')->with(key: 'error', value: __("Can't delete role."));
        });
    }
}

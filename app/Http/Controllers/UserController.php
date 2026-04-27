<?php

namespace App\Http\Controllers;

use App\Generators\Services\ImageServiceV2;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller implements HasMiddleware
{
    public function __construct(public ImageServiceV2 $imageServiceV2, public string $avatarPath = 'avatars', public string $disk = 'storage.public')
    {
        //
    }

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware(middleware: 'permission:user view', only: ['index', 'show']),
            new Middleware(middleware: 'permission:user create', only: ['create', 'store']),
            new Middleware(middleware: 'permission:user edit', only: ['edit', 'update']),
            new Middleware(middleware: 'permission:user delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = User::query()->with(['roles:id,name']);

            return DataTables::eloquent($query)
                ->addColumn('role', function (User $user) {
                    $names = $user->getRoleNames();

                    return $names->isNotEmpty() ? e($names->first()) : '—';
                })
                ->addColumn('action', fn (User $user) => view('users.include.action', ['model' => $user])->render())
                ->removeColumn('created_at')
                ->removeColumn('updated_at')
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request): RedirectResponse {
            $validated = $request->validated();
            $validated['avatar'] = $this->imageServiceV2->upload(name: 'avatar', path: $this->avatarPath);
            $validated['password'] = bcrypt(value: $request->password);

            $user = User::create(attributes: $validated);

            $role = Role::select(columns: ['id', 'name'])->find(id: $request->role);

            $user->assignRole(roles: $role->name);

            return to_route(route: 'users.index')->with(key: 'success', value: __('The user was created successfully.'));
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $user->load(relations: ['roles:id,name']);

        if ($request->expectsJson()) {
            return response()->json([
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'role' => $user->getRoleNames()->isNotEmpty() ? $user->getRoleNames()->first() : null,
                'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i'),
                'created_at' => $user->created_at?->format('Y-m-d H:i'),
                'updated_at' => $user->updated_at?->format('Y-m-d H:i'),
                'edit_url' => $request->user()->can('user edit') ? route('users.edit', $user) : null,
            ]);
        }

        return to_route('users.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $user->load(relations: ['roles:id,name']);

        return view(view: 'users.edit', data: compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        return DB::transaction(function () use ($request, $user): RedirectResponse {
            $validated = $request->validated();
            $validated['avatar'] = $this->imageServiceV2->upload(name: 'avatar', path: $this->avatarPath, defaultImage: $user?->avatar);

            if (! $request->password) {
                unset($validated['password']);
            } else {
                $validated['password'] = bcrypt(value: $request->password);
            }

            $user->update(attributes: $validated);

            $role = Role::select(columns: ['id', 'name'])->find(id: $request->role);

            $user->syncRoles(roles: $role->name);

            return to_route(route: 'users.index')->with(key: 'success', value: __('The user was updated successfully.'));
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($user): RedirectResponse {
                $avatar = $user->avatar;

                $user->delete();

                $this->imageServiceV2->delete(path: $this->avatarPath, image: $avatar, disk: $this->disk);

                return to_route(route: 'users.index')->with(key: 'success', value: __('The user was deleted successfully.'));
            });
        } catch (\Exception $e) {
            return to_route(route: 'users.index')->with(key: 'error', value: __("The user can't be deleted because it's related to another table."));
        }
    }
}

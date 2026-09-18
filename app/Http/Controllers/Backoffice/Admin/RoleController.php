<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roleService)
    {
    }

    public function index(Request $request): View
    {
        return $this->indexView($request);
    }

    public function trash(Request $request): View
    {
        $this->authorizeAction('roles.view');

        $roles = Role::onlyTrashed()
            ->where('guard_name', 'admin')
            ->with('permissions')
            ->withCount(['permissions', 'admins'])
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->trim().'%'))
            ->latest('deleted_at')
            ->paginate(15)
            ->withQueryString();

        return view('backoffice.admin.roles.trash', [
            'title' => 'Role Trash',
            'breadcrumb' => [
                ['text' => 'Roles', 'url' => route('admin.roles.index')],
                ['text' => 'Trash', 'url' => null],
            ],
            'roles' => $roles,
        ]);
    }

    public function create(Request $request): View
    {
        return $this->indexView($request, null, true);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->roleService->create($request->validated());

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function show(Role $role): View
    {
        $this->authorizeAction('roles.view');

        abort_unless($role->guard_name === 'admin', 404);

        return view('backoffice.admin.roles.partials.show', [
            'role' => $role->load('permissions'),
            'standalone' => true,
        ]);
    }

    public function edit(Request $request, Role $role): View
    {
        $this->authorizeAction('roles.manage');
        abort_unless($role->guard_name === 'admin', 404);

        return $this->indexView($request, $role->load('permissions'), true);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        abort_unless($role->guard_name === 'admin', 404);
        $this->roleService->update($role, $request->validated());

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorizeAction('roles.manage');
        abort_unless($role->guard_name === 'admin', 404);
        $this->roleService->delete($role);

        return back()->with('success', 'Role moved to trash.');
    }

    public function restore(int $role): RedirectResponse
    {
        $this->authorizeAction('roles.manage');
        $trashedRole = Role::onlyTrashed()->where('guard_name', 'admin')->findOrFail($role);
        $this->roleService->restore($trashedRole);

        return back()->with('success', 'Role restored successfully.');
    }

    public function forceDelete(int $role): RedirectResponse
    {
        $this->authorizeAction('roles.manage');
        $trashedRole = Role::onlyTrashed()->where('guard_name', 'admin')->findOrFail($role);
        $this->roleService->forceDelete($trashedRole);

        return back()->with('success', 'Role permanently deleted.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $action = $request->string('action')->toString();
        $this->authorizeAction('roles.manage');

        $validated = Validator::make($request->all(), [
            'action' => ['required', 'in:delete,restore,force-delete'],
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['integer'],
        ])->validate();

        $result = $this->roleService->bulk($action, $validated['role_ids']);
        $message = sprintf('%d role(s) processed.', $result['processed']);

        if ($result['skipped'] > 0) {
            $message .= sprintf(' %d skipped because they were protected or unavailable.', $result['skipped']);
        }

        return back()->with('success', $message);
    }

    private function indexView(Request $request, ?Role $formRole = null, bool $openForm = false): View
    {
        $this->authorizeAction('roles.view');

        $roles = Role::query()
            ->where('guard_name', 'admin')
            ->with('permissions')
            ->withCount(['permissions', 'admins'])
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->trim().'%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $permissions = Permission::query()
            ->where('guard_name', 'admin')
            ->orderBy('group_name')
            ->orderBy('name')
            ->get();

        return view('backoffice.admin.roles.index', [
            'title' => 'Roles & Permissions Management',
            'breadcrumb' => [['text' => 'Roles', 'url' => null]],
            'roles' => $roles,
            'permissions' => $permissions,
            'formRole' => $formRole,
            'formMode' => $formRole ? 'edit' : 'create',
            'openForm' => $openForm,
        ]);
    }

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }
    }
}

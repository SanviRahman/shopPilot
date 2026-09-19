<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roleService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        return $this->indexView($request);
    }

    public function trash(Request $request): View|JsonResponse
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

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.roles.partials.table', [
                    'roles' => $roles,
                    'isTrash' => true,
                ])->render(),
                'pagination' => $roles->hasPages() ? (string) $roles->links() : '',
            ]);
        }

        return view('backoffice.admin.roles.trash', [
            'title' => 'Role Trash',
            'breadcrumb' => [
                ['text' => 'Roles', 'url' => route('admin.roles.index')],
                ['text' => 'Trash', 'url' => null],
            ],
            'roles' => $roles,
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse|RedirectResponse
    {
        $role = $this->roleService->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Role created successfully.',
                'role' => $role,
            ]);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function show(Request $request, Role $role): View|JsonResponse
    {
        $this->authorizeAction('roles.view');
        abort_unless($role->guard_name === 'admin', 404);

        $role->load('permissions')->loadCount('admins');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => strtoupper($role->guard_name),
                    'is_protected' => method_exists($role, 'isProtected') ? $role->isProtected() : false,
                    'admins_count' => $role->admins_count ?? 0,
                    'permissions' => $role->permissions->pluck('name')->toArray(),
                    'created_at' => optional($role->created_at)->format('d M Y, h:i A'),
                ],
            ]);
        }

        return view('backoffice.admin.roles.show', compact('role'));
    }

    public function edit(Request $request, Role $role): View|JsonResponse
    {
        $this->authorizeAction('roles.manage');
        abort_unless($role->guard_name === 'admin', 404);

        $role->load('permissions');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('id')->toArray(),
                ],
            ]);
        }

        return $this->indexView($request, $role, true);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse|RedirectResponse
    {
        abort_unless($role->guard_name === 'admin', 404);
        $this->roleService->update($role, $request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully.',
            ]);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Request $request, Role $role): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('roles.manage');
        abort_unless($role->guard_name === 'admin', 404);
        $this->roleService->delete($role);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Role moved to trash successfully.',
            ]);
        }

        return back()->with('success', 'Role moved to trash.');
    }

    public function restore(Request $request, int $role): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('roles.manage');
        $trashedRole = Role::onlyTrashed()->where('guard_name', 'admin')->findOrFail($role);
        $this->roleService->restore($trashedRole);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Role restored successfully.',
            ]);
        }

        return back()->with('success', 'Role restored successfully.');
    }

    public function forceDelete(Request $request, int $role): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('roles.manage');
        $trashedRole = Role::onlyTrashed()->where('guard_name', 'admin')->findOrFail($role);
        $this->roleService->forceDelete($trashedRole);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Role permanently deleted.',
            ]);
        }

        return back()->with('success', 'Role permanently deleted.');
    }

    public function bulkAction(Request $request): JsonResponse|RedirectResponse
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

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    private function indexView(Request $request, ?Role $formRole = null, bool $openForm = false): View|JsonResponse
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

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.roles.partials.table', [
                    'roles' => $roles,
                    'isTrash' => false,
                ])->render(),
                'pagination' => $roles->hasPages() ? (string) $roles->links() : '',
            ]);
        }

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
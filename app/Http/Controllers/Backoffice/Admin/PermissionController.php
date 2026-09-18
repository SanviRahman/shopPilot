<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Models\Permission;
use App\Services\PermissionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function __construct(private readonly PermissionService $permissionService)
    {
    }

    public function index(Request $request): View
    {
        return $this->indexView($request);
    }

    public function trash(Request $request): View
    {
        $this->authorizeAction('permissions.view');

        $permissions = Permission::onlyTrashed()
            ->where('guard_name', 'admin')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->trim().'%'))
            ->latest('deleted_at')
            ->paginate(20)
            ->withQueryString();

        return view('backoffice.admin.permissions.trash', [
            'title' => 'Permission Trash',
            'breadcrumb' => [
                ['text' => 'Permissions', 'url' => route('admin.permissions.index')],
                ['text' => 'Trash', 'url' => null],
            ],
            'permissions' => $permissions,
        ]);
    }

    public function create(Request $request): View
    {
        return $this->indexView($request, null, true);
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $this->permissionService->create($request->validated());

        return redirect()->route('admin.permissions.index')->with('success', 'Permission created successfully.');
    }

    public function show(Permission $permission): View
    {
        $this->authorizeAction('permissions.view');
        abort_unless($permission->guard_name === 'admin', 404);

        return view('backoffice.admin.permissions.show', [
            'permission' => $permission,
        ]);
    }

    public function edit(Request $request, Permission $permission): View
    {
        $this->authorizeAction('permissions.manage');
        abort_unless($permission->guard_name === 'admin', 404);

        return $this->indexView($request, $permission, true);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        abort_unless($permission->guard_name === 'admin', 404);
        $this->permissionService->update($permission, $request->validated());

        return redirect()->route('admin.permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $this->authorizeAction('permissions.manage');
        abort_unless($permission->guard_name === 'admin', 404);
        $this->permissionService->delete($permission);

        return back()->with('success', 'Permission moved to trash.');
    }

    public function restore(int $permission): RedirectResponse
    {
        $this->authorizeAction('permissions.manage');
        $trashedPermission = Permission::onlyTrashed()->where('guard_name', 'admin')->findOrFail($permission);
        $this->permissionService->restore($trashedPermission);

        return back()->with('success', 'Permission restored successfully.');
    }

    public function forceDelete(int $permission): RedirectResponse
    {
        $this->authorizeAction('permissions.manage');
        $trashedPermission = Permission::onlyTrashed()->where('guard_name', 'admin')->findOrFail($permission);
        $this->permissionService->forceDelete($trashedPermission);

        return back()->with('success', 'Permission permanently deleted.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $this->authorizeAction('permissions.manage');

        $validated = Validator::make($request->all(), [
            'action' => ['required', 'in:delete,restore,force-delete'],
            'permission_ids' => ['required', 'array', 'min:1'],
            'permission_ids.*' => ['integer'],
        ])->validate();

        $result = $this->permissionService->bulk($validated['action'], $validated['permission_ids']);
        $message = sprintf('%d permission(s) processed.', $result['processed']);

        if ($result['skipped'] > 0) {
            $message .= sprintf(' %d unavailable.', $result['skipped']);
        }

        return back()->with('success', $message);
    }

    private function indexView(Request $request, ?Permission $formPermission = null, bool $openForm = false): View
    {
        $this->authorizeAction('permissions.view');

        $permissions = Permission::query()
            ->where('guard_name', 'admin')
            ->when($request->filled('group_name'), fn ($query) => $query->where('group_name', $request->string('group_name')->toString()))
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->trim().'%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $groups = Permission::query()
            ->where('guard_name', 'admin')
            ->whereNotNull('group_name')
            ->distinct()
            ->orderBy('group_name')
            ->pluck('group_name');

        return view('backoffice.admin.permissions.index', [
            'title' => 'Permissions Management',
            'breadcrumb' => [['text' => 'Permissions', 'url' => null]],
            'permissions' => $permissions,
            'groups' => $groups,
            'formPermission' => $formPermission,
            'formMode' => $formPermission ? 'edit' : 'create',
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

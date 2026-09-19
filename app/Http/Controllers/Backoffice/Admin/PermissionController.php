<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Models\Permission;
use App\Services\PermissionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function __construct(private readonly PermissionService $permissionService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        return $this->indexView($request);
    }

    public function trash(Request $request): View|JsonResponse
    {
        $this->authorizeAction('permissions.view');

        $permissions = Permission::onlyTrashed()
            ->where('guard_name', 'admin')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->trim().'%'))
            ->latest('deleted_at')
            ->paginate(20)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.permissions.partials.table', [
                    'permissions' => $permissions,
                    'isTrash' => true,
                ])->render(),
                'pagination' => $permissions->hasPages() ? (string) $permissions->links() : '',
            ]);
        }

        return view('backoffice.admin.permissions.trash', [
            'title' => 'Permission Trash',
            'breadcrumb' => [
                ['text' => 'Permissions', 'url' => route('admin.permissions.index')],
                ['text' => 'Trash', 'url' => null],
            ],
            'permissions' => $permissions,
        ]);
    }

    public function store(StorePermissionRequest $request): JsonResponse|RedirectResponse
    {
        $permission = $this->permissionService->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully.',
                'permission' => $permission,
            ]);
        }

        return redirect()->route('admin.permissions.index')->with('success', 'Permission created successfully.');
    }

    public function show(Request $request, Permission $permission): View|JsonResponse
    {
        $this->authorizeAction('permissions.view');
        abort_unless($permission->guard_name === 'admin', 404);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'permission' => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'guard_name' => strtoupper($permission->guard_name),
                    'group_name' => $permission->group_name ?: 'Other',
                    'created_at' => optional($permission->created_at)->format('d M Y, h:i A'),
                    'updated_at' => optional($permission->updated_at)->format('d M Y, h:i A'),
                ],
            ]);
        }

        return view('backoffice.admin.permissions.show', compact('permission'));
    }

    public function edit(Request $request, Permission $permission): View|JsonResponse
    {
        $this->authorizeAction('permissions.manage');
        abort_unless($permission->guard_name === 'admin', 404);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'permission' => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'group_name' => $permission->group_name,
                    'guard_name' => $permission->guard_name,
                ],
            ]);
        }

        return $this->indexView($request, $permission, true);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse|RedirectResponse
    {
        abort_unless($permission->guard_name === 'admin', 404);
        $this->permissionService->update($permission, $request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Permission updated successfully.',
            ]);
        }

        return redirect()->route('admin.permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy(Request $request, Permission $permission): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('permissions.manage');
        abort_unless($permission->guard_name === 'admin', 404);
        $this->permissionService->delete($permission);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Permission moved to trash successfully.',
            ]);
        }

        return back()->with('success', 'Permission moved to trash.');
    }

    public function restore(Request $request, int $permission): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('permissions.manage');
        $trashedPermission = Permission::onlyTrashed()->where('guard_name', 'admin')->findOrFail($permission);
        $this->permissionService->restore($trashedPermission);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Permission restored successfully.',
            ]);
        }

        return back()->with('success', 'Permission restored successfully.');
    }

    public function forceDelete(Request $request, int $permission): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('permissions.manage');
        $trashedPermission = Permission::onlyTrashed()->where('guard_name', 'admin')->findOrFail($permission);
        $this->permissionService->forceDelete($trashedPermission);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Permission permanently deleted.',
            ]);
        }

        return back()->with('success', 'Permission permanently deleted.');
    }

    public function bulkAction(Request $request): JsonResponse|RedirectResponse
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

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    private function indexView(Request $request, ?Permission $formPermission = null, bool $openForm = false): View|JsonResponse
    {
        $this->authorizeAction('permissions.view');

        $permissions = Permission::query()
            ->where('guard_name', 'admin')
            ->when($request->filled('group_name'), fn ($query) => $query->where('group_name', $request->string('group_name')->toString()))
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->trim().'%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.permissions.partials.table', [
                    'permissions' => $permissions,
                    'isTrash' => false,
                ])->render(),
                'pagination' => $permissions->hasPages() ? (string) $permissions->links() : '',
            ]);
        }

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
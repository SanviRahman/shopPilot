<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Models\Admin;
use App\Models\Role;
use App\Services\AdminService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(private readonly AdminService $adminService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        return $this->indexView($request);
    }

    public function trash(Request $request): View|JsonResponse
    {
        $this->authorizeAction('staff.view');

        $admins = Admin::onlyTrashed()
            ->with('roles')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->latest('deleted_at')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.admins.partials.table', [
                    'admins' => $admins,
                    'isTrash' => true,
                ])->render(),
                'pagination' => $admins->hasPages() ? (string) $admins->links() : '',
            ]);
        }

        return view('backoffice.admin.admins.trash', [
            'title' => 'Admin Trash',
            'breadcrumb' => [
                ['text' => 'Admin Users', 'url' => route('admin.admins.index')],
                ['text' => 'Trash', 'url' => null],
            ],
            'admins' => $admins,
        ]);
    }

    public function store(StoreAdminRequest $request): JsonResponse|RedirectResponse
    {
        $admin = $this->adminService->create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Admin created successfully.',
                'admin' => $admin,
            ]);
        }

        return redirect()->route('admin.admins.index')->with('success', 'Admin created successfully.');
    }

    public function show(Request $request, Admin $admin): View|JsonResponse
    {
        $this->authorizeAction('staff.view');

        $admin->load('roles');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'admin' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'status' => ucfirst($admin->status),
                    'roles' => $admin->roles->pluck('name')->map(fn($r) => strtoupper($r))->toArray(),
                    'created_at' => optional($admin->created_at)->format('d M Y, h:i A'),
                    'updated_at' => optional($admin->updated_at)->format('d M Y, h:i A'),
                ],
            ]);
        }

        return view('backoffice.admin.admins.show', compact('admin'));
    }

    public function edit(Request $request, Admin $admin): JsonResponse|View
    {
        $this->authorizeAction('staff.update');

        $admin->load('roles');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'admin' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'status' => $admin->status,
                    'roles' => $admin->roles->pluck('id')->toArray(),
                ],
            ]);
        }

        return $this->indexView($request, $admin, true);
    }

    public function update(UpdateAdminRequest $request, Admin $admin): JsonResponse|RedirectResponse
    {
        $this->adminService->update($admin, $request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Admin updated successfully.',
            ]);
        }

        return redirect()->route('admin.admins.index')->with('success', 'Admin updated successfully.');
    }

    public function destroy(Request $request, Admin $admin): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('staff.delete');
        $this->adminService->delete($admin);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Admin moved to trash successfully.',
            ]);
        }

        return back()->with('success', 'Admin moved to trash.');
    }

    public function restore(Request $request, int $admin): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('staff.restore');
        $trashedAdmin = Admin::onlyTrashed()->findOrFail($admin);
        $this->adminService->restore($trashedAdmin);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Admin restored successfully.',
            ]);
        }

        return back()->with('success', 'Admin restored successfully.');
    }

    public function forceDelete(Request $request, int $admin): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('staff.force-delete');
        $trashedAdmin = Admin::onlyTrashed()->findOrFail($admin);
        $this->adminService->forceDelete($trashedAdmin);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Admin permanently deleted.',
            ]);
        }

        return back()->with('success', 'Admin permanently deleted.');
    }

    public function bulkAction(Request $request): JsonResponse|RedirectResponse
    {
        $action = $request->string('action')->toString();
        $permission = match ($action) {
            'delete' => 'staff.delete',
            'restore' => 'staff.restore',
            'force-delete' => 'staff.force-delete',
            default => null,
        };

        if (! $permission) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Invalid bulk action.'], 422);
            }
            return back()->withErrors(['action' => 'Invalid bulk action.']);
        }

        $this->authorizeAction($permission);

        $validated = Validator::make($request->all(), [
            'action' => ['required', 'in:delete,restore,force-delete'],
            'admin_ids' => ['required', 'array', 'min:1'],
            'admin_ids.*' => ['integer'],
        ])->validate();

        $result = $this->adminService->bulk($validated['action'], $validated['admin_ids']);
        $message = sprintf('%d admin(s) processed.', $result['processed']);

        if ($result['skipped'] > 0) {
            $message .= sprintf(' %d skipped because they were protected.', $result['skipped']);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    private function indexView(Request $request, ?Admin $formAdmin = null, bool $openForm = false): View|JsonResponse
    {
        $this->authorizeAction('staff.view');

        $admins = Admin::query()
            ->with('roles')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($request->filled('role_id'), fn ($query) => $query->whereHas(
                'roles', fn ($roleQuery) => $roleQuery->whereKey($request->integer('role_id'))
            ))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.admins.partials.table', [
                    'admins' => $admins,
                    'isTrash' => false,
                ])->render(),
                'pagination' => $admins->hasPages() ? (string) $admins->links() : '',
            ]);
        }

        return view('backoffice.admin.admins.index', [
            'title' => 'Admin Users Management',
            'breadcrumb' => [
                ['text' => 'Admin Users', 'url' => null],
            ],
            'admins' => $admins,
            'roles' => $this->adminRoles(),
            'formAdmin' => $formAdmin,
            'formMode' => $formAdmin ? 'edit' : 'create',
            'openForm' => $openForm,
        ]);
    }

    private function adminRoles()
    {
        return Role::query()->where('guard_name', 'admin')->orderBy('name')->get();
    }

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }
    }
}
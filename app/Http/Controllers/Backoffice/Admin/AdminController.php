<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Models\Admin;
use App\Models\Role;
use App\Services\AdminService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(private readonly AdminService $adminService)
    {
    }

    public function index(Request $request): View
    {
        return $this->indexView($request);
    }

    public function trash(Request $request): View
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

        return view('backoffice.admin.admins.trash', [
            'title' => 'Admin Trash',
            'breadcrumb' => [
                ['text' => 'Admin Users', 'url' => route('admin.admins.index')],
                ['text' => 'Trash', 'url' => null],
            ],
            'admins' => $admins,
        ]);
    }

    public function create(Request $request): View
    {
        return $this->indexView($request, null, true);
    }

    public function store(StoreAdminRequest $request): RedirectResponse
    {
        $this->adminService->create($request->validated());

        return redirect()->route('admin.admins.index')->with('success', 'Admin created successfully.');
    }

    public function show(Admin $admin): View
    {
        $this->authorizeAction('staff.view');

        return view('backoffice.admin.admins.partials.show', [
            'admin' => $admin->load('roles'),
            'standalone' => true,
        ]);
    }

    public function edit(Request $request, Admin $admin): View
    {
        $this->authorizeAction('staff.update');

        return $this->indexView($request, $admin->load('roles'), true);
    }

    public function update(UpdateAdminRequest $request, Admin $admin): RedirectResponse
    {
        $this->adminService->update($admin, $request->validated());

        return redirect()->route('admin.admins.index')->with('success', 'Admin updated successfully.');
    }

    public function destroy(Admin $admin): RedirectResponse
    {
        $this->authorizeAction('staff.delete');
        $this->adminService->delete($admin);

        return back()->with('success', 'Admin moved to trash.');
    }

    public function restore(int $admin): RedirectResponse
    {
        $this->authorizeAction('staff.restore');
        $trashedAdmin = Admin::onlyTrashed()->findOrFail($admin);
        $this->adminService->restore($trashedAdmin);

        return back()->with('success', 'Admin restored successfully.');
    }

    public function forceDelete(int $admin): RedirectResponse
    {
        $this->authorizeAction('staff.force-delete');
        $trashedAdmin = Admin::onlyTrashed()->findOrFail($admin);
        $this->adminService->forceDelete($trashedAdmin);

        return back()->with('success', 'Admin permanently deleted.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $action = $request->string('action')->toString();
        $permission = match ($action) {
            'delete' => 'staff.delete',
            'restore' => 'staff.restore',
            'force-delete' => 'staff.force-delete',
            default => null,
        };

        if (! $permission) {
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
            $message .= sprintf(' %d skipped because they were protected or unavailable.', $result['skipped']);
        }

        return back()->with('success', $message);
    }

    private function indexView(Request $request, ?Admin $formAdmin = null, bool $openForm = false): View
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

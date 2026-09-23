<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        return $this->indexView($request);
    }

    public function trash(Request $request): View|JsonResponse
    {
        $this->authorizeAction('blogs.view');

        $blogs = Blog::onlyTrashed()
            ->with('author')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%"));
            })
            ->latest('deleted_at')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.blogs.partials.table', [
                    'blogs'   => $blogs,
                    'isTrash' => true,
                ])->render(),
                'pagination' => $blogs->hasPages() ? (string) $blogs->links() : '',
            ]);
        }

        return view('backoffice.admin.blogs.trash', [
            'title' => 'Blog Trash Bin',
            'breadcrumb' => [
                ['text' => 'Blogs', 'url' => route('admin.blogs.index')],
                ['text' => 'Trash', 'url' => null],
            ],
            'blogs' => $blogs,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('blogs.create');

        $validated = $request->validate([
            'title'   => ['required', 'string', 'max:255'],
            'slug'    => ['nullable', 'string', 'max:255', 'unique:blogs,slug'],
            'content' => ['nullable', 'string'],
        ]);

        $author = auth('admin')->user();

        $blog = Blog::create([
            'author_type' => $author ? get_class($author) : null,
            'author_id'   => $author?->id,
            'title'       => $validated['title'],
            'slug'        => ! empty($validated['slug'])
                ? Str::slug($validated['slug'])
                : Str::slug($validated['title']) . '-' . uniqid(),
            'content'     => $validated['content'] ?? null,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Blog created successfully.',
                'blog'    => $blog,
            ]);
        }

        return redirect()->route('admin.blogs.index')->with('success', 'Blog created successfully.');
    }

    public function show(Request $request, Blog $blog): View|JsonResponse
    {
        $this->authorizeAction('blogs.view');

        $blog->load('author');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'blog'    => [
                    'id'         => $blog->id,
                    'title'      => $blog->title,
                    'slug'       => $blog->slug,
                    'content'    => $blog->content,
                    'author'     => $blog->author?->name ?? 'System',
                    'created_at' => optional($blog->created_at)->format('d M Y, h:i A'),
                    'updated_at' => optional($blog->updated_at)->format('d M Y, h:i A'),
                ],
            ]);
        }

        return view('backoffice.admin.blogs.partials.show', compact('blog'));
    }

    public function edit(Request $request, Blog $blog): JsonResponse|View
    {
        $this->authorizeAction('blogs.update');

        $blog->load('author');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'blog'    => [
                    'id'      => $blog->id,
                    'title'   => $blog->title,
                    'slug'    => $blog->slug,
                    'content' => $blog->content,
                ],
            ]);
        }

        return $this->indexView($request, $blog, true);
    }

    public function update(Request $request, Blog $blog): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('blogs.update');

        $validated = $request->validate([
            'title'   => ['required', 'string', 'max:255'],
            'slug'    => ['nullable', 'string', 'max:255', Rule::unique('blogs', 'slug')->ignore($blog->id)],
            'content' => ['nullable', 'string'],
        ]);

        $blog->update([
            'title'   => $validated['title'],
            'slug'    => ! empty($validated['slug'])
                ? Str::slug($validated['slug'])
                : Str::slug($validated['title']) . '-' . $blog->id,
            'content' => $validated['content'] ?? null,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Blog updated successfully.',
            ]);
        }

        return redirect()->route('admin.blogs.index')->with('success', 'Blog updated successfully.');
    }

    public function destroy(Request $request, Blog $blog): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('blogs.delete');

        $blog->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Blog moved to trash successfully.',
            ]);
        }

        return back()->with('success', 'Blog moved to trash successfully.');
    }

    public function restore(Request $request, int $blog): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('blogs.restore');

        $trashedBlog = Blog::onlyTrashed()->findOrFail($blog);
        $trashedBlog->restore();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Blog restored successfully.',
            ]);
        }

        return back()->with('success', 'Blog restored successfully.');
    }

    public function forceDelete(Request $request, int $blog): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('blogs.force-delete');

        $trashedBlog = Blog::onlyTrashed()->findOrFail($blog);
        $trashedBlog->forceDelete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Blog permanently deleted.',
            ]);
        }

        return back()->with('success', 'Blog permanently deleted.');
    }

    public function bulkAction(Request $request): JsonResponse|RedirectResponse
    {
        $action = $request->string('action')->toString();
        $permission = match ($action) {
            'delete'       => 'blogs.delete',
            'restore'      => 'blogs.restore',
            'force-delete' => 'blogs.force-delete',
            default        => null,
        };

        if (! $permission) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Invalid bulk action.'], 422);
            }
            return back()->withErrors(['action' => 'Invalid bulk action.']);
        }

        $this->authorizeAction($permission);

        $validated = Validator::make($request->all(), [
            'action'     => ['required', 'in:delete,restore,force-delete'],
            'blog_ids'   => ['required', 'array', 'min:1'],
            'blog_ids.*' => ['integer'],
        ])->validate();

        $processed = 0;
        $uniqueIds = array_unique(array_map('intval', $validated['blog_ids']));

        foreach ($uniqueIds as $id) {
            $blog = match ($validated['action']) {
                'delete'                  => Blog::find($id),
                'restore', 'force-delete' => Blog::onlyTrashed()->find($id),
            };

            if (! $blog) {
                continue;
            }

            match ($validated['action']) {
                'delete'       => $blog->delete(),
                'restore'      => $blog->restore(),
                'force-delete' => $blog->forceDelete(),
            };

            $processed++;
        }

        $message = sprintf('%d blog(s) processed.', $processed);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    private function indexView(Request $request, ?Blog $formBlog = null, bool $openForm = false): View|JsonResponse
    {
        $this->authorizeAction('blogs.view');

        $blogs = Blog::query()
            ->with('author')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.blogs.partials.table', [
                    'blogs'   => $blogs,
                    'isTrash' => false,
                ])->render(),
                'pagination' => $blogs->hasPages() ? (string) $blogs->links() : '',
            ]);
        }

        return view('backoffice.admin.blogs.index', [
            'title' => 'Blog Management',
            'breadcrumb' => [
                ['text' => 'Blogs', 'url' => null],
            ],
            'blogs'     => $blogs,
            'formBlog'  => $formBlog,
            'formMode'  => $formBlog ? 'edit' : 'create',
            'openForm'  => $openForm,
        ]);
    }

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }
    }
}
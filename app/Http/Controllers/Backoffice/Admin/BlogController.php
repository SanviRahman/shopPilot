<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\StoreBlogRequest;
use App\Http\Requests\Blog\UpdateBlogRequest;
use App\Models\Blog;
use App\Services\BlogService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function __construct(private readonly BlogService $blogService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        return $this->indexView($request);
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('blogs.create');
        if ($request->ajax()) return response()->json(['success' => true, 'blog' => $this->emptyPayload()]);
        return redirect()->route('admin.blogs.index', ['create' => 1]);
    }

    public function store(StoreBlogRequest $request): JsonResponse|RedirectResponse
    {
        $blog = $this->blogService->create($request->validated(), $request->user('admin'));
        if ($request->ajax()) return response()->json(['success' => true, 'message' => 'Blog created successfully.', 'blog' => $this->payload($blog)]);
        return redirect()->route('admin.blogs.index')->with('success', 'Blog created successfully.');
    }

    public function show(Request $request, Blog $blog): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('blogs.view');
        $blog->load('author');
        if ($request->ajax()) return response()->json(['success' => true, 'blog' => $this->payload($blog)]);
        return redirect()->route('admin.blogs.index', ['show' => $blog->getKey()]);
    }

    public function edit(Request $request, Blog $blog): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('blogs.update');
        $blog->load('author');
        if ($request->ajax()) return response()->json(['success' => true, 'blog' => $this->payload($blog)]);
        return redirect()->route('admin.blogs.index', ['edit' => $blog->getKey()]);
    }

    public function update(UpdateBlogRequest $request, Blog $blog): JsonResponse|RedirectResponse
    {
        $this->blogService->update($blog, $request->validated());
        if ($request->ajax()) return response()->json(['success' => true, 'message' => 'Blog updated successfully.', 'blog' => $this->payload($blog->refresh())]);
        return redirect()->route('admin.blogs.index')->with('success', 'Blog updated successfully.');
    }

    public function destroy(Request $request, Blog $blog): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('blogs.delete');
        $this->blogService->moveToTrash($blog);
        if ($request->ajax()) return response()->json(['success' => true, 'message' => 'Blog moved to trash successfully.']);
        return back()->with('success', 'Blog moved to trash successfully.');
    }

    public function trash(Request $request): View|JsonResponse
    {
        $this->authorizeAction('blogs.view');
        $filters = ['search' => $request->string('search')->trim()->toString()];
        $blogs = $this->blogService->getPaginatedBlogs($filters, true);
        if ($request->ajax()) return $this->tableResponse($blogs, true);
        return view('backoffice.admin.blogs.trash', ['title' => 'Blog Trash Bin', 'breadcrumb' => [['text' => 'Blogs', 'url' => route('admin.blogs.index')], ['text' => 'Trash', 'url' => null]], 'blogs' => $blogs, 'filters' => $filters]);
    }

    public function restore(Request $request, int $blog): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('blogs.restore');
        $this->blogService->restore($blog);
        if ($request->ajax()) return response()->json(['success' => true, 'message' => 'Blog restored successfully.']);
        return back()->with('success', 'Blog restored successfully.');
    }

    public function forceDelete(Request $request, int $blog): JsonResponse|RedirectResponse
    {
        $this->authorizeAction('blogs.force-delete');
        $this->blogService->forceDelete($blog);
        if ($request->ajax()) return response()->json(['success' => true, 'message' => 'Blog permanently deleted.']);
        return back()->with('success', 'Blog permanently deleted.');
    }

    public function bulkAction(Request $request): JsonResponse|RedirectResponse
    {
        $action = $request->string('action')->toString();
        $permission = match ($action) { 'delete' => 'blogs.delete', 'restore' => 'blogs.restore', 'force-delete' => 'blogs.force-delete', default => null };
        if (! $permission) return $request->ajax() ? response()->json(['success' => false, 'message' => 'Invalid bulk action.'], 422) : back()->withErrors(['action' => 'Invalid bulk action.']);
        $this->authorizeAction($permission);
        $validated = Validator::make($request->all(), ['action' => ['required', 'in:delete,restore,force-delete'], 'blog_ids' => ['required', 'array', 'min:1'], 'blog_ids.*' => ['integer']])->validate();
        $processed = $this->blogService->bulkAction($validated['action'], $validated['blog_ids']);
        $message = sprintf('%d blog(s) processed.', $processed);
        if ($request->ajax()) return response()->json(['success' => true, 'message' => $message]);
        return back()->with('success', $message);
    }

    private function indexView(Request $request): View|JsonResponse
    {
        $this->authorizeAction('blogs.view');
        $filters = ['search' => $request->string('search')->trim()->toString()];
        $blogs = $this->blogService->getPaginatedBlogs($filters);
        if ($request->ajax()) return $this->tableResponse($blogs, false);
        return view('backoffice.admin.blogs.index', ['title' => 'Blog Management', 'breadcrumb' => [['text' => 'Blogs', 'url' => null]], 'blogs' => $blogs, 'filters' => $filters]);
    }

    private function tableResponse($blogs, bool $isTrash): JsonResponse
    {
        return response()->json(['success' => true, 'html' => view('backoffice.admin.blogs.partials.table', ['blogs' => $blogs, 'isTrash' => $isTrash])->render(), 'pagination' => $blogs->hasPages() ? (string) $blogs->links() : '']);
    }

    private function emptyPayload(): array
    {
        return ['id' => null, 'title' => '', 'slug' => '', 'description' => '', 'content' => ''];
    }

    private function payload(Blog $blog): array
    {
        return ['id' => $blog->getKey(), 'title' => $blog->title, 'slug' => $blog->slug, 'description' => $blog->description, 'content' => $blog->content, 'author' => $blog->author?->name ?? 'System', 'author_label' => $blog->author ? class_basename($blog->author_type).': '.$blog->author->name : 'System', 'created_at' => $blog->created_at?->format('d M Y, h:i A'), 'updated_at' => $blog->updated_at?->format('d M Y, h:i A')];
    }

    private function authorizeAction(string $permission): void
    {
        if (! auth('admin')->user()?->can($permission)) throw new AuthorizationException('You are not authorized to perform this action.');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class CategoryController
{
    public function index(): Response
    {
        $categories = Category::query()
            ->withCount('books')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Categories/Index', ['categories' => $categories]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::query()->create($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', trans('ui.flash.category_created'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', trans('ui.flash.category_updated'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->books()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', trans('ui.flash.category_has_books'));
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', trans('ui.flash.category_deleted'));
    }
}

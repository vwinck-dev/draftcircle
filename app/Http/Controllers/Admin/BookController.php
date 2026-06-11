<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\DataTransferObjects\BookData;
use App\Http\Requests\BookRequest;
use App\Models\Book;
use App\Services\BookService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final readonly class BookController
{
    public function __construct(private BookService $books) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $books = Book::query()
            ->with('categories')
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Admin/Books/Index', ['books' => $books]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Books/Create', ['categories' => $this->books->homeCatalog()['categories']]);
    }

    public function store(BookRequest $request): RedirectResponse
    {
        $coverPath = $request->file('cover')?->store(path: 'covers', options: 'public');
        $this->books->create(data: BookData::fromArray($request->validated() + ['cover_path' => $coverPath]));

        return redirect()->route('admin.books.index')->with('success', trans('ui.flash.book_created'));
    }

    public function edit(Book $book): Response
    {
        return Inertia::render('Admin/Books/Edit', ['book' => $this->books->details(book: $book), 'categories' => $this->books->homeCatalog()['categories']]);
    }

    public function update(BookRequest $request, Book $book): RedirectResponse
    {
        $coverPath = $request->file('cover')?->store(path: 'covers', options: 'public');
        $this->books->update(book: $book, data: BookData::fromArray($request->validated() + ['cover_path' => $coverPath]));

        return redirect()->route('admin.books.index')->with('success', trans('ui.flash.book_updated'));
    }

    public function destroy(Book $book): RedirectResponse
    {
        if ($book->hasActiveLoans()) {
            return back()->with('error', trans('ui.errors.book_has_active_loans'));
        }

        $this->books->delete(book: $book);

        return back()->with('success', trans('ui.flash.book_deleted'));
    }
}

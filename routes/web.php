<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\BookCommentController as AdminBookCommentController;
use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LoanController as AdminLoanController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\TrashController as AdminTrashController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\BookCommentController;
use App\Http\Controllers\BookCommentVoteController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/', '/home');

Route::put('/locale', LocaleController::class)->name('locale.update');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/home', HomeController::class)->name('home');

    Route::get('/dashboard', HomeController::class)->name('dashboard');

    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
    Route::get('/books/{book}/reader', [BookController::class, 'reader'])->name('books.reader');
    Route::put('/books/{book}/progress', [BookController::class, 'progress'])->name('books.progress');
    Route::post('/books/{book}/ratings', [BookController::class, 'rate'])->name('books.ratings.store');
    Route::post('/books/{book}/reserve', [LoanController::class, 'store'])->name('loans.store');
    Route::post('/books/{book}/comments', [BookCommentController::class, 'store'])->name('comments.store');
    Route::post('/comments/{comment}/reply', [BookCommentController::class, 'reply'])->name('comments.reply');
    Route::put('/comments/{comment}/vote', [BookCommentVoteController::class, 'update'])->name('comments.vote');
    Route::delete('/comments/{comment}', [BookCommentController::class, 'destroy'])->name('comments.destroy');

    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::put('/loans/{loan}/return', [LoanController::class, 'update'])->middleware('can:update,loan')->name('loans.return');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::put('/notifications/read-all', [NotificationController::class, 'update'])->name('notifications.read-all');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/security', fn () => Inertia::render('Profile/Security'))->name('profile.security');
    Route::get('/profile/settings', fn () => Inertia::render('Profile/Settings'))->name('profile.settings');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/personal-data', [ProfileController::class, 'destroyPersonalData'])->name('profile.personal-data.destroy');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')->name('admin.')->middleware('role:admin,librarian')->group(function (): void {
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        Route::resource('books', AdminBookController::class)->except(['show']);

        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');

        Route::get('/loans', [AdminLoanController::class, 'index'])->name('loans.index');

        Route::get('/comments', [AdminBookCommentController::class, 'index'])->name('comments.index');
        Route::patch('/comments/{comment}', [AdminBookCommentController::class, 'update'])->name('comments.update');
        Route::delete('/comments/{comment}', [AdminBookCommentController::class, 'destroy'])->name('comments.destroy');

        Route::get('/trash', [AdminTrashController::class, 'index'])->name('trash.index');
        Route::patch('/trash/books/{book}', [AdminTrashController::class, 'restoreBook'])->name('trash.books.restore');
        Route::delete('/trash/books/{book}', [AdminTrashController::class, 'destroyBook'])->name('trash.books.destroy');
        Route::patch('/trash/comments/{comment}', [AdminTrashController::class, 'restoreComment'])->name('trash.comments.restore');
        Route::delete('/trash/comments/{comment}', [AdminTrashController::class, 'destroyComment'])->name('trash.comments.destroy');

        Route::get('/reports', AdminDashboardController::class)->name('reports.index');

        Route::get('/settings', [AdminSettingController::class, 'index'])->middleware('role:admin')->name('settings.index');
        Route::put('/settings', [AdminSettingController::class, 'update'])->middleware('role:admin')->name('settings.update');
    });
});

require __DIR__.'/auth.php';

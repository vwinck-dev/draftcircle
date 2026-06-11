<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\BookService;
use Inertia\Inertia;
use Inertia\Response;

final readonly class HomeController
{
    public function __construct(private BookService $books) {}

    public function __invoke(): Response
    {
        return Inertia::render('Home/Index', $this->books->homeCatalog());
    }
}

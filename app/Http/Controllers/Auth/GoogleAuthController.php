<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Services\GoogleAuthService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

final readonly class GoogleAuthController
{
    public function __construct(private GoogleAuthService $googleAuth) {}

    public function redirect(): SymfonyRedirectResponse
    {
        return $this->googleAuth->redirect();
    }

    public function callback(): RedirectResponse
    {
        return $this->googleAuth->callback();
    }
}

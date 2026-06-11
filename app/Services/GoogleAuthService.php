<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Auth\FindOrCreateGoogleUserAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

final readonly class GoogleAuthService
{
    public function __construct(private FindOrCreateGoogleUserAction $findOrCreateUser) {}

    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();
        $user = $this->findOrCreateUser->execute(googleUser: $googleUser);
        Auth::login($user, remember: true);

        return redirect()->route('home');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $default = config('app.locale');
        $default = is_string($default) ? $default : 'en';

        $user = $request->user();

        $preferred = $user instanceof User ? $user->preferredLocale() : $default;

        $locale = $request->session()->get('locale', $preferred);

        $supported = ['en', 'pt_BR'];

        App::setLocale(is_string($locale) && in_array($locale, $supported, true) ? $locale : $default);

        return $next($request);
    }
}

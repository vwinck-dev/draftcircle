<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final readonly class LocaleController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(['en', 'pt_BR'])],
        ]);

        $request->session()->put('locale', $validated['locale']);

        $user = $request->user();

        if ($user instanceof User) {
            $user->update(['locale' => $validated['locale']]);
        }

        return back();
    }
}

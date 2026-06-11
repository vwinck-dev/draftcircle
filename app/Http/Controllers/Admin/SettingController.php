<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\SettingGroup;
use App\Http\Requests\SettingsRequest;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class SettingController
{
    public function __construct(private SettingService $settings) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Settings/Index', ['settings' => $this->settings->all()]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        $this->settings->save(group: SettingGroup::from($request->string('group')->toString()), settings: $request->array('settings'));

        return back()->with('success', trans('ui.flash.settings_saved'));
    }
}

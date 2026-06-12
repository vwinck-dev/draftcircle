<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

final class DemoController extends Controller
{
    public function startAttack(): JsonResponse
    {
        abort_unless(app()->environment('local'), 404);

        Http::timeout(3)->post('http://attacker:8888/attack')->throw();

        return response()->json(['status' => 'attacking']);
    }

    public function stopAttack(): JsonResponse
    {
        abort_unless(app()->environment('local'), 404);

        Http::timeout(3)->post('http://attacker:8888/stop')->throw();

        return response()->json(['status' => 'stopped']);
    }
}

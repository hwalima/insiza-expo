<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DeployWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        if ($request->header('X-Deploy-Token') !== config('services.deploy.token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Log tail mode — return last error messages only
        if ($request->header('X-Log-Tail')) {
            $log   = storage_path('logs/laravel.log');
            $lines = file_exists($log) ? file($log) : ['no log'];
            // Get last 200 lines then extract just ERROR lines
            $tail   = array_slice($lines, -200);
            $errors = array_filter($tail, fn($l) => str_contains($l, 'production.ERROR'));
            return response()->json(['errors' => array_values(array_slice($errors, -5))]);
        }

        $appRoot   = base_path();
        $publicHtml = '/home/insizaex/public_html';
        $log        = [];

        $commands = [
            "cd {$appRoot} && git pull origin master 2>&1",
            "rm -rf {$publicHtml}/build && cp -r {$appRoot}/public/build {$publicHtml}/build 2>&1",
            "cd {$appRoot} && php artisan storage:link --force 2>&1",
            "cd {$appRoot} && php artisan migrate --force 2>&1",
            "cd {$appRoot} && php artisan optimize:clear 2>&1",
            "cd {$appRoot} && php artisan optimize 2>&1",
        ];

        foreach ($commands as $cmd) {
            $output = [];
            $code   = 0;
            exec($cmd, $output, $code);
            $log[] = ['cmd' => $cmd, 'output' => implode("\n", $output), 'code' => $code];
        }

        return response()->json(['status' => 'deployed', 'log' => $log]);
    }
}

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to EasyStock API',
        'version' => '1.0.0',
    ]);
});

<?php

use Illuminate\Support\Facades\Route;

Route::get('/avatar.png', function () {
    return response()->file(__DIR__.'/../../docs/images/salman-hijazi.png', [
        'Content-Type' => 'image/png',
        'Cache-Control' => 'public, max-age=86400',
    ]);
});

<?php

use Illuminate\Support\Facades\Route;

// SPA Route - serves the Vue application
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');

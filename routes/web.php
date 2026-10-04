<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth()->user();

    return redirect()->route($user?->isAdmin() ? 'admin.dashboard' : 'admin.login');
});

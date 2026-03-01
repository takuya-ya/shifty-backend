<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

// 未認証時のリダイレクト先（メール認証リンクを異なる端末のブラウザで開いた場合など）
Route::get('/login', function () {
    return redirect(config('app.frontend_url') . '/?message=login_required');
})->name('login');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpellCheckerController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/spell-check', [SpellCheckerController::class, 'check']);
Route::post('/store-dictionary', [SpellCheckerController::class, 'storeDictionary'])->withoutMiddleware([VerifyCsrfToken::class]);
Route::get('/suggest-words', [SpellCheckerController::class, 'suggestWords']);
Route::get('/spell-check-text', [SpellCheckerController::class, 'checkText']);
Route::delete('/clear-dictionary', [SpellCheckerController::class, 'clearDictionary'])->withoutMiddleware([VerifyCsrfToken::class]);
Route::get('/spell-checker-view', function () {
    return view('spell-checker-template');
});

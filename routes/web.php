<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/calorie', [App\Http\Controllers\CalorieController::class, 'index'])->name('calorie');
Route::post('/calorie', [App\Http\Controllers\CalorieController::class, 'index'])->name('calorie');
Route::get('/calorie/show/{tgtdate}', [App\Http\Controllers\CalorieController::class, 'show'])->name('calorie.show');
Route::get('/calorie/makegraph}', [App\Http\Controllers\CalorieController::class, 'makegraph'])->name('calorie.makegraph');
Route::get('/calorie/chartgraph', [App\Http\Controllers\CalorieController::class, 'chartgraph'])->name('calorie.chartgraph');
Route::post('/calorie/chartgraph', [App\Http\Controllers\CalorieController::class, 'chartgraph'])->name('calorie.chartgraph');
Route::post('/calorie/store', [App\Http\Controllers\CalorieController::class, 'store'])->name('calorie.store');
Route::post('/calorie/update', [App\Http\Controllers\CalorieController::class, 'update'])->name('calorie.update');
Route::post('/calorie/destroy', [App\Http\Controllers\CalorieController::class, 'destroy'])->name('calorie.destroy');
Route::get('/getdata', [App\Http\Controllers\CalorieController::class, 'getdata'])->name('getdata');
Route::post('/getdata', [App\Http\Controllers\CalorieController::class, 'getdata'])->name('getdata');
Auth::routes();


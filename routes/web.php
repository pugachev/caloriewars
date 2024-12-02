<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalorieController;
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
Route::get('/', function () {
    return redirect('/calorie');
});
Route::get('/max-calorie', [CalorieController::class, 'getMaxColorie'])->name('calorie.max');
Route::get('/max-steps', [CalorieController::class, 'getMaxSteps'])->name('calorie.steps');
Route::get('/max-distance', [CalorieController::class, 'getMaxDistance'])->name('calorie.distance');
Route::get('/calorie', [App\Http\Controllers\CalorieController::class, 'index'])->name('calorie');
Route::post('/calorie', [App\Http\Controllers\CalorieController::class, 'index'])->name('calorie');
Route::get('/calorie/show/{tgtdate}', [App\Http\Controllers\CalorieController::class, 'show'])->name('calorie.show');
Route::get('/calorie/showphysical/{tgtdate}', [App\Http\Controllers\CalorieController::class, 'showphysical'])->name('calorie.showphysical');
Route::get('/calorie/makegraph}', [App\Http\Controllers\CalorieController::class, 'makegraph'])->name('calorie.makegraph');
Route::get('/calorie/makegraph2}', [App\Http\Controllers\CalorieController::class, 'makegraph2'])->name('calorie.makegraph2');
Route::get('/calorie/makegraph3}', [App\Http\Controllers\CalorieController::class, 'makegraph3'])->name('calorie.makegraph3');
Route::get('/calorie/makegraph4}', [App\Http\Controllers\CalorieController::class, 'makegraph4'])->name('calorie.makegraph4');
Route::get('/calorie/chartgraph', [App\Http\Controllers\CalorieController::class, 'chartgraph'])->name('calorie.chartgraph');
Route::post('/calorie/chartgraph', [App\Http\Controllers\CalorieController::class, 'chartgraph'])->name('calorie.chartgraph');
Route::post('/calorie/store', [App\Http\Controllers\CalorieController::class, 'store'])->name('calorie.store');
Route::post('/calorie/store_physical_info', [App\Http\Controllers\CalorieController::class, 'store_physical_info'])->name('calorie.store_physical_info');
Route::post('/calorie/update', [App\Http\Controllers\CalorieController::class, 'update'])->name('calorie.update');
Route::post('/calorie/updatephysical', [App\Http\Controllers\CalorieController::class, 'updatephysical'])->name('calorie.updatephysical');
Route::post('/calorie/destroy', [App\Http\Controllers\CalorieController::class, 'destroy'])->name('calorie.destroy');
Route::post('/calorie/destroyphysical', [App\Http\Controllers\CalorieController::class, 'destroyphysical'])->name('calorie.destroyphysical');
Auth::routes();


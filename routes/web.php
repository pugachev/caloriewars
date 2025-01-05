<?php

use App\Http\Controllers\CalorieController;
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
Route::get('/', function () {
    return redirect('/calorie');
});

// ルートの定義
Route::group([], function () {
    Route::get('/calorie', [CalorieController::class, 'index'])->name('calorie');
    Route::get('/calorie/show/{tgtdate}', [CalorieController::class, 'show'])->name('calorie.show');
    Route::get('/calorie/showphysical/{tgtdate}', [CalorieController::class, 'showphysical'])->name('calorie.showphysical');

    // グラフ関連のルート
    Route::get('/calorie/makegraph', [CalorieController::class, 'makegraph'])->name('calorie.makegraph');
    Route::get('/calorie/makegraph2', [CalorieController::class, 'makegraph2'])->name('calorie.makegraph2');
    Route::get('/calorie/makegraph3', [CalorieController::class, 'makegraph3'])->name('calorie.makegraph3');
    Route::get('/calorie/makegraph4', [CalorieController::class, 'makegraph4'])->name('calorie.makegraph4');

    // Ajax関連のルート
    Route::get('/calorie/makegraphajax', [CalorieController::class, 'makegraphajax'])->name('calorie.makegraphajax');
    Route::get('/calorie/makegraph2ajax', [CalorieController::class, 'makegraph2ajax'])->name('makegraph2ajax');
    Route::get('/calorie/makegraph3ajax', [CalorieController::class, 'makegraph3ajax'])->name('makegraph3ajax');
    Route::get('/calorie/makegraph4ajax', [CalorieController::class, 'makegraph4ajax'])->name('makegraph4ajax');

    // その他のルート
    Route::get('/calorie/chartgraph', [CalorieController::class, 'chartgraph'])->name('calorie.chartgraph');
    Route::post('/calorie/store', [CalorieController::class, 'store'])->name('calorie.store');
    Route::post('/calorie/store_physical_info', [CalorieController::class, 'store_physical_info'])->name('calorie.store_physical_info');
    Route::post('/calorie/update', [CalorieController::class, 'update'])->name('calorie.update');
    Route::post('/calorie/updatephysical', [CalorieController::class, 'updatephysical'])->name('calorie.updatephysical');
    Route::post('/calorie/destroy', [CalorieController::class, 'destroy'])->name('calorie.destroy');
    Route::post('/calorie/destroyphysical', [CalorieController::class, 'destroyphysical'])->name('calorie.destroyphysical');
});

// MAX関連のルート
Route::get('/max-calorie', [CalorieController::class, 'getMaxColorie'])->name('calorie.max');
Route::get('/max-steps', [CalorieController::class, 'getMaxSteps'])->name('calorie.steps');
Route::get('/max-distance', [CalorieController::class, 'getMaxDistance'])->name('calorie.distance');

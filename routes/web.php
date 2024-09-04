<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
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
    return view('welcome');
});

Route::get('/', [TaskController::class, 'index']);
Route::post('/tasks', [TaskController::class, 'store']);
//Route::post('/tasks/status/{task}', [TaskController::class,'update']);
Route::post('/tasks/status/{task}', [TaskController::class, 'update'])->name('tasks.updateStatus');

Route::post('/tasks/{task}', [TaskController::class,'destroy']);

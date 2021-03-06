<?php

use App\Http\Controllers\BatikController;
use App\Http\Controllers\QRController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', [App\Http\Controllers\UserController::class, 'store']);
Route::post('/login', [App\Http\Controllers\UserController::class, 'login']);

// Route::prefix('v1')->group(function() {
//     Route::post('/register', [UserController::class], 'store');
// });

Route::group(['middleware' => ['jwt.verify']], function () {
    // batik api
    Route::post('/batik', [BatikController::class, 'store']);
    Route::put('/batik/{id}', [BatikController::class, 'edit']);
    Route::delete('/batik/{id}', [BatikController::class, 'destroy']);
    Route::post('/fetchuser', [UserController::class, 'fetchUser']);
});

Route::get('/batik/{qr}', [BatikController::class, 'show']);
Route::get('/batik', [BatikController::class, 'index']);


// qr code
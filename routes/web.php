<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Events\NewMessageEvent;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Broadcast::routes(['middleware' => ['auth:sanctum']]);


Route::get('/test-broadcast', function () {
    $message = \App\Models\Message::latest()->with('sender')->first(); // اجلب آخر رسالة مع العلاقة
    event(new NewMessageEvent($message));
    return 'Broadcasted!';
});

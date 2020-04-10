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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::prefix('subscription')->name('subscription.')->group(function () {
    Route::get('/', 'SubscriptionController@index')->name('index');
    Route::get('/createplan', 'SubscriptionController@createPlan')->name('createPlan');

    Route::post('/proccess', 'SubscriptionController@proccess')->name('proccess');
});

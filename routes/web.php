<?php

use Illuminate\Support\Facades\Route;

/*
* Here is where you can register web routes for your application. 
* These routes are loaded by the RouteServiceProvider within a group which contains the "web" middleware group. 
* The `Route::get` method is used to define a route that responds to HTTP GET requests.
* In this case, when a user visits the root URL (`/`), the application will return the `welcome` view. 
*/
Route::get('/', function () {
    return view('welcome');
});

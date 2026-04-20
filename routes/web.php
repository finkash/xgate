<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

/*
* Here is where you can register web routes for your application. 
* These routes are loaded by the RouteServiceProvider within a group which contains the "web" middleware group. 
* The `Route::get` method is used to define a route that responds to HTTP GET requests.
* In this case, when a user visits the root URL (`/`), the application will return the `welcome` view. 
*/

// Redirects the root URL to the dashboard if the user is authenticated, or to the login page if they are not.
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

/*
* The `Route::middleware('guest')` method is used to apply the `guest` middleware to the routes defined within the group.
* This middleware ensures that only unauthenticated users can access these routes.
*/
Route::middleware('guest')->group(function () {

    /*
    * `Route::get` method is used to define a route that responds to HTTP GET requests. 
    * The first argument is the URL pattern (`/login`), and the second argument is an array that specifies the controller and method to handle the request. 
    * The `name` method is used to assign a name to the route, which can be used to generate URLs or redirect to this route elsewhere in the application.
    */
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

    /*
    * The `Route::post` method is used to define a route that responds to HTTP POST requests.
    * This route will handle the form submission from the login page, where users enter their credentials to log in.
    * The `login` method in the `AuthController` will be responsible for processing the login form data, validating the credentials, and authenticating the user.
    * The `login` method will also handle the "remember me" functionality.
    * The `login` method will return a redirect response, either redirecting back to the login page with an error message 
    * if authentication fails, or redirecting to the dashboard if authentication is successful.
    * The `login` method will also regenerate the session to prevent session fixation attacks, which is a security measure to protect against unauthorized access to user sessions.
    * The `login` method will use the `Auth::attempt` method to attempt to authenticate the user with the provided credentials.
    */
    Route::post('/login', [AuthController::class, 'login'])->name('login.perform');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.perform');
});

Route::middleware('auth')->group(function () {

    /*
    *`Route::middleware('auth')` method is used to apply the `auth` middleware to the routes defined within the group.
    * This middleware ensures that only authenticated users can access these routes.
    * The `Route::get` method is used to define a route that responds to HTTP GET requests. 
    */
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/users/{user}/follow', [FollowController::class, 'store'])->name('users.follow');
    Route::delete('/users/{user}/follow', [FollowController::class, 'destroy'])->name('users.unfollow');

    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/feed', [FeedController::class, 'index'])->name('feed.index');
});

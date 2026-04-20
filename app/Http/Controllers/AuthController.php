<?php

namespace App\Http\Controllers;

use App\Domain\Content\Services\FeedService;
use App\Domain\IdentityAndAccess\Actions\RegisterUserAction;
use App\Domain\IdentityAndAccess\DTOs\RegisterUserDTO;

// Represents the users in the application and is used for authentication and user management.

// Used for handling HTTP responses and rendering views, respectively.
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

// Used to handle incoming HTTP requests and access request data.
use Illuminate\Http\Request;

// Provides authentication services, such as logging in and out users, checking authentication status, and managing user sessions.
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    public function __construct(
        private readonly RegisterUserAction $registerUserAction
    ) {
    }

    public function showLogin(): View
    {
        // Returns the view located at `resources/views/auth/login.blade.php`, 
        // which contains the login form for users to enter their credentials.
        return view('auth.login');
    }

    public function showRegister(): View
    {
        // Returns the view located at `resources/views/auth/register.blade.php`,
        // which contains the registration form for new users to create an account.
        return view('auth.register');
    }

    public function login(Request $request): RedirectResponse
    {
        // Validates the incoming request data to ensure that the email and password fields meet the specified criteria.
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // If the authentication fails, it redirects back to the login page with an error message. 
        // If successful, it regenerates the session to prevent session fixation attacks and redirects the user to the dashboard.
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'These credentials do not match our records.',
                ]);
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $dto = RegisterUserDTO::fromValidated($validated);
        $user = $this->registerUserAction->execute($dto);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function dashboard(Request $request, FeedService $feedService): View
    {
        $user = $request->user();
        $feed = $feedService->getFeed($user, 15);
        $feedMode = $user->following()->exists() ? 'following' : 'discover';

        return view('livewire.feed.index', [
            'feed' => $feed,
            'feedMode' => $feedMode,
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

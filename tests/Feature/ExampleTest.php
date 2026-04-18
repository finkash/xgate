<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /*
    * This test method checks if the application returns a successful response when accessing the root URL (`/`).
    * It sends a GET request to the root URL and asserts that the response is a redirect to the login page.
    * This test ensures that unauthenticated users are redirected to the login page when they try to access the root URL.
    * The `assertRedirect` method is used to verify that the response is a redirect to the specified route, which in this case is the login page.
    */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}

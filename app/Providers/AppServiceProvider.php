<?php
namespace App\Providers;

// Imports the `ServiceProvider` class from the `Illuminate\Support` namespace, which is a base class for all service providers in Laravel.
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services or bindings in the service container. 
     */
    public function register(): void
    {
        //
    }
    
    /*
    * This method is used to perform any actions that need to be done after all services have been registered.
    * It is typically used for tasks such as event listeners, route model bindings, or any other bootstrapping tasks that need to 
    * be performed when the application starts. 
    */
    public function boot(): void
    {
        //
    }
}

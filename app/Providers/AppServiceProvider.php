<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // En producción (Render) todas las URLs generadas deben ser https://.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Solo administradores (ADMIN_EMAILS) ven los contactos de pre-registro de toda la plataforma.
        Gate::define('view-pre-registrations', fn (User $user) => in_array(
            strtolower($user->email),
            config('app.admin_emails', []),
            true
        ));
    }
}

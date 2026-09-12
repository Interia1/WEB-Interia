<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        $codespaceName = env('CODESPACE_NAME');
        $forwardingDomain = env('GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN');

        if (! app()->environment('testing') && $codespaceName && $forwardingDomain) {
            URL::forceRootUrl("https://{$codespaceName}-8000.{$forwardingDomain}");
            URL::forceScheme('https');
        }

        Gate::define('access-internal', fn (User $user): bool => $user->isInternal());
        Gate::define('manage-products', fn (User $user): bool => $user->isInternal());
        Gate::define('manage-users', fn (User $user): bool => $user->role === UserRole::Administrator);
        Gate::define('export-consents', fn (User $user): bool => $user->role === UserRole::Administrator);
        Gate::define('view-project-structure', fn (User $user): bool => $user->role === UserRole::Administrator);

        RateLimiter::for('login', function (Request $request): Limit {
            $email = Str::lower((string) $request->input('email'));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('registration', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('password-reset', fn (Request $request): Limit => Limit::perMinute(3)->by($request->ip()));
    }
}

<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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
        Gate::define('manage-catalog', fn (User $user): bool => $user->role === UserRole::Administrator);
        Gate::define('manage-users', fn (User $user): bool => $user->role === UserRole::Administrator);
        Gate::define('export-consents', fn (User $user): bool => $user->role === UserRole::Administrator);
        Gate::define('view-project-structure', fn (User $user): bool => $user->role === UserRole::Administrator);

        View::composer('layouts.app', function ($view) {
            $carts = auth()->check() ? auth()->user()->shoppingCarts()->get() : collect();
            $defaultCart = $carts->firstWhere('is_default', true);
            if (auth()->check() && ! $defaultCart) {
                throw new \LogicException('Prihlásený používateľ nemá predvolený košík.');
            }
            $view->with([
                'headerCarts' => $carts,
                'headerDefaultCart' => $defaultCart,
                'headerCartProducts' => $defaultCart ? $defaultCart->products()->orderBy('name')->get() : collect(),
            ]);
        });

        View::composer('pages.catalog-results', function ($view) {
            $productIds = $view->getData()['products']->pluck('id');
            $favoriteIds = auth()->check()
                ? auth()->user()->favoriteProducts()->whereIn('products.id', $productIds)->pluck('products.id')->all()
                : [];
            $view->with([
                'favoriteProductIds' => $favoriteIds,
                'shoppingCarts' => auth()->check() ? auth()->user()->shoppingCarts()->get() : collect(),
            ]);
        });

        RateLimiter::for('login', function (Request $request): Limit {
            $email = Str::lower((string) $request->input('email'));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('registration', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('password-reset', fn (Request $request): Limit => Limit::perMinute(3)->by($request->ip()));
    }
}

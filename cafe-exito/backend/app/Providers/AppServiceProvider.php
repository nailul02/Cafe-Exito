<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuditService::class);
        $this->app->singleton(PermissionService::class);
    }

    public function boot(): void
    {
        // Wildcard CORS hanya boleh di lokal (keputusan F7).
        if (! $this->app->environment('local', 'testing') && in_array('*', (array) config('cors.allowed_origins'), true)) {
            throw new RuntimeException('CORS_ALLOWED_ORIGINS tidak boleh "*" di luar APP_ENV=local.');
        }

        Gate::policy(User::class, UserPolicy::class);

        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(5)->by(mb_strtolower((string) $r->input('login')).'|'.$r->ip()));
        RateLimiter::for('customer', fn (Request $r) => Limit::perMinute(60)->by($r->ip()));
        RateLimiter::for('staff', fn (Request $r) => Limit::perMinute(300)->by((string) ($r->user()?->id ?: $r->ip())));
    }
}

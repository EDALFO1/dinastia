<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use App\Services\ModuloService;
use App\View\Composers\SidebarComposer;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuloService::class);
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

        View::composer(['shared.aside', 'shared.header'], SidebarComposer::class);

        // Set up multi-tenant context for API requests from X-Empresa-ID header
        // This allows model binding to work correctly before middleware runs
        $this->app->make('router')->matched(function () {
            if (auth()->check()) {
                $empresaId = (int) request()->header('X-Empresa-ID');
                if ($empresaId) {
                    auth()->user()->current_empresa_id = $empresaId;
                }
            }
        });
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; // Ditambahkan
use Illuminate\Support\Facades\Auth; // Ditambahkan

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
        // Kode ini akan membagikan notifikasi ke SEMUA view
        // tapi hanya jika user sedang login
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $unreadNotifications = $user->unreadNotifications()->take(5)->get();
                $unreadNotificationsCount = $user->unreadNotifications()->count();
                
                $view->with('unreadNotifications', $unreadNotifications);
                $view->with('unreadNotificationsCount', $unreadNotificationsCount);
            } else {
                // Beri nilai default jika tidak login
                $view->with('unreadNotifications', collect());
                $view->with('unreadNotificationsCount', 0);
            }
        });
    }
}
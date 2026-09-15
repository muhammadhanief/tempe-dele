<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Carbon::setLocale('id');
        if (str_starts_with(config('app.url'), 'https')) {
            URL::forceRootUrl(config('app.url'));
            URL::forceScheme('https');
        }
    }
}
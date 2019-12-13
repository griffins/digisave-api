<?php

namespace App\Providers;

use App\Foundation\AfricasTalking\Client;
use App\Foundation\SmsChannel;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        require_once app_path('Foundation/Utils/helpers.php');

        $this->app->bind(Client::class, function ($app) {
            $config = array_values(config('services.africastalking'));
            return new Client(...$config);
        });

        $this->app->bind('africastalking', function ($app) {
            return app(Client::class);
        });

        $this->app->bind('sms', function ($app) {
            return app(SmsChannel::class);
        });

        $this->app->bind(SmsChannel::class, function ($app) {
            $from = config('services.africastalking.defaults.sender_id');
            return new SmsChannel(app('africastalking'), $from);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

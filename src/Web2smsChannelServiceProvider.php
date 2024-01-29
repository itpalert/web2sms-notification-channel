<?php

namespace ITPalert\Web2smsChannel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\ChannelManager;
use RuntimeException;

use ITPalert\Web2smsChannel\Channels\Web2smsChannel;

class Web2smsChannelServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/web2sms.php', 'web2sms');

        $this->app->singleton(Web2sms::class, function ($app) {
            $config = $app['config']['web2sms'];

            $httpClient = null;

            if ($httpClient = $config['http_client'] ?? null) {
                $httpClient = $app->make($httpClient);
            } elseif (! class_exists('GuzzleHttp\Client')) {
                throw new RuntimeException(
                    'The Web2sms client requires a "psr/http-client-implementation" class such as Guzzle.'
                );
            }

            return Web2sms::make($config, $httpClient)->client();
        });

        $this->app->bind(Web2smsChannel::class, function ($app) {
            return new Web2smsChannel(
                $app->make(Web2sms::class),
                $app['config']['web2sms.sms_from']
            );
        });

        Notification::resolved(function (ChannelManager $service) {
            $service->extend('web2sms', function ($app) {
                return $app->make(Web2smsChannel::class);
            });
        });
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/web2sms.php' => $this->app->configPath('web2sms.php'),
            ], 'web2sms');
        }
    }
}
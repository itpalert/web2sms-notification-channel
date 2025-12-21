<?php

namespace ITPalert\Web2smsChannel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\ChannelManager;
use ITPalert\Web2smsChannel\Channels\Web2smsChannel;
use ITPalert\Web2sms\Client;

class Web2smsChannelServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->bind(Web2smsChannel::class, function ($app): Web2smsChannel {
            $client = $app->make(Client::class);
            $from = $app['config']['services.web2sms.sms_from'] ?? '';

            return new Web2smsChannel($client, $from);
        });

        Notification::resolved(function (ChannelManager $service): void {
            $service->extend('web2sms', function ($app): Web2smsChannel {
                return $app->make(Web2smsChannel::class);
            });
        });
    }
}
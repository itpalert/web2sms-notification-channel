<?php

namespace ITPalert\Web2smsChannel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\ChannelManager;
use RuntimeException;

use ITPalert\Web2smsChannel\Channels\Web2smsChannel;
use ITPalert\Web2sms\Web2sms;

class Web2smsChannelServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Web2smsChannel::class, function ($app) {
            return new Web2smsChannel(
                $app->make(Web2sms::class)
            );
        });

        Notification::resolved(function (ChannelManager $service) {
            $service->extend('web2sms', function ($app) {
                return $app->make(Web2smsChannel::class);
            });
        });
    }
}

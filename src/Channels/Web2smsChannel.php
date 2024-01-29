<?php

namespace ITPalert\Web2smsChannel\Channels;

use Illuminate\Notifications\Notification;

use ITPalert\Web2sms\SMS;
use ITPalert\Web2sms\Client;

use ITPalert\Web2smsChannel\Messages\Web2smsMessage;

class Web2smsChannel
{
    /**
     * The Web2sms client instance.
     *
     * @var ITPalert\Web2sms\Client
     */
    protected $client;

    /**
     * The phone number notifications should be sent from.
     *
     * @var string
     */
    protected $from;

    /**
     * Create a new Web2sms channel instance.
     *
     * @param  ITPalert\Web2sms\Client  $client
     * @param  string  $from
     * @return void
     */
    public function __construct(Client $client, $from)
    {
        $this->client = $client;
        $this->from = $from;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return ITPalert\Web2sms\SMS\Collection|null
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $to = $notifiable->routeNotificationFor('Web2sms', $notification)) {
            return;
        }

        $message = $notification->toWeb2sms($notifiable);

        if (is_string($message)) {
            $message = new Web2smsMessage($message);
        }

        $web2smsSms = new SMS(
            $to,
            $message->from ?: $this->from,
            trim($message->content),
            $message->type
        );

        $web2smsSms->setClientRef($message->clientReference);

        if ($message->statusCallback) {
            $web2smsSms->setDeliveryReceiptCallback($message->statusCallback);
        }

        $web2smsSms->setVisible($message->visible);

        if ($message->schedule) {
            $web2smsSms->setSchedule($message->schedule);
        }

        return ($message->client ?? $this->client)->send($web2smsSms);
    }
}
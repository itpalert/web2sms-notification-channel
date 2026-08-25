<?php

namespace ITPalert\Web2smsChannel\Channels;

use Illuminate\Notifications\Notification;
use ITPalert\Web2sms\SMS;
use ITPalert\Web2sms\Contracts\Client;
use ITPalert\Web2smsChannel\Messages\Web2smsMessage;

class Web2smsChannel
{
    /**
     * The Web2sms client instance.
     */
    protected Client $client;

    /**
     * The phone number notifications should be sent from.
     */
    protected string $from;

    /**
     * Create a new Web2sms channel instance.
     */
    public function __construct(Client $client, string $from)
    {
        $this->client = $client;
        $this->from = $from;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @return \ITPalert\Web2sms\Responses\SendResponse|null
     */
    public function send(mixed $notifiable, Notification $notification): mixed
    {
        $to = $notifiable->routeNotificationFor('Web2sms', $notification);
        
        if (! $to) {
            return null;
        }

        $message = $notification->toWeb2sms($notifiable);

        if (is_string($message)) {
            $message = new Web2smsMessage($message);
        }

        if (! $message instanceof Web2smsMessage) {
            throw new \InvalidArgumentException(
                'Notification must return a string or Web2smsMessage instance from toWeb2sms method'
            );
        }

        $web2smsSms = new SMS(
            $to,
            $message->from ?: $this->from,
            trim($message->content),
            $message->type
        );

        if ($message->clientReference !== '') {
            $web2smsSms->setClientRef($message->clientReference);
        }

        if ($message->statusCallback !== '') {
            $web2smsSms->setDeliveryReceiptCallback($message->statusCallback);
        }

        if ($message->displayedMessage !== '') {
            $web2smsSms->setDisplayedMessage($message->displayedMessage);
        }

        if ($message->schedule !== null) {
            $web2smsSms->setSchedule($message->schedule);
        }

        return ($message->client ?? $this->client)->send($web2smsSms);
    }
}
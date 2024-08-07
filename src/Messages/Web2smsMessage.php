<?php

namespace ITPalert\Web2smsChannel\Messages;

class Web2smsMessage
{
    /**
     * The message content.
     *
     * @var string
     */
    public $content;

    /**
     * The phone number the message should be sent from.
     *
     * @var string
     */
    public $from;

    /**
     * The message type.
     *
     * @var string
     */
    public $type = 'text';

    /**
     * The custom Vonage client instance.
     *
     * @var \ITPalert\Web2sms\Client|null
     */
    public $client;

    /**
     * The client reference.
     *
     * @var string
     */
    public $clientReference = '';

    /**
     * The webhook to be called with status updates.
     *
     * @var string
     */
    public $statusCallback = '';

    /**
     * The (optional) date the message should sent at.
     *
     * @var string|null
     */
    public $schedule;

    /**
     * The text shown in place of the actual message.
     *
     * @var bool
     */
    public $displayedMessage = '';

    /**
     * Create a new message instance.
     *
     * @param  string  $content
     * @return void
     */
    public function __construct($content = '')
    {
        $this->content = $content;
    }

    /**
     * Set the message content.
     *
     * @param  string  $content
     * @return $this
     */
    public function content($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Set the phone number the message should be sent from.
     *
     * @param  string  $from
     * @return $this
     */
    public function from($from)
    {
        $this->from = $from;

        return $this;
    }

     /**
     * Set the message type.
     *
     * @return $this
     */
    public function unicode()
    {
        $this->type = 'unicode';

        return $this;
    }

    /**
     * Set the client reference (up to 40 characters).
     *
     * @param  string  $clientReference
     * @return $this
     */
    public function clientReference($clientReference)
    {
        $this->clientReference = $clientReference;

        return $this;
    }

    /**
     * Set the webhook callback URL to update the message status.
     *
     * @param  string  $callback
     * @return $this
     */
    public function statusCallback(string $callback)
    {
        $this->statusCallback = $callback;

        return $this;
    }

    /**
     * Set the date the message should sent at.
     *
     * @param  \DateTimeInterface|string  $sendAt
     * @return $this
     * @throws \Exception
     */
    public function schedule($schedule)
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * Set the text which will be shown in the dashboard in place of the actual message content.
     *
     * @param  string  $displayedMessage
     * @return $this
     */
    public function displayedMessage($displayedMessage)
    {
        $this->displayedMessage = $displayedMessage;

        return $this;
    }

    /**
     * Set the web2sms client instance.
     *
     * @param  ITPalert\Web2sms\Client  $client
     * @return $this
     */
    public function usingClient($client)
    {
        $this->client = $client;

        return $this;
    }
}
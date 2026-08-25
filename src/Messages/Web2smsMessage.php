<?php

namespace ITPalert\Web2smsChannel\Messages;

use ITPalert\Web2sms\Contracts\Client;

class Web2smsMessage
{
    /**
     * The message content.
     */
    public string $content;

    /**
     * The phone number the message should be sent from.
     */
    public string $from = '';

    /**
     * The message type.
     */
    public string $type = 'text';

    /**
     * The custom Web2sms client instance.
     */
    public ?Client $client = null;

    /**
     * The client reference.
     */
    public string $clientReference = '';

    /**
     * The webhook to be called with status updates.
     */
    public string $statusCallback = '';

    /**
     * The (optional) date the message should sent at.
     */
    public ?string $schedule = null;

    /**
     * The text shown in place of the actual message.
     */
    public string $displayedMessage = '';

    /**
     * Create a new message instance.
     */
    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    /**
     * Set the message content.
     */
    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Set the phone number the message should be sent from.
     */
    public function from(string $from): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Set the message type to unicode.
     */
    public function unicode(): self
    {
        $this->type = 'unicode';

        return $this;
    }

    /**
     * Set the client reference (up to 40 characters).
     */
    public function clientReference(string $clientReference): self
    {
        if (strlen($clientReference) > 40) {
            throw new \InvalidArgumentException('Client reference cannot exceed 40 characters');
        }

        $this->clientReference = $clientReference;

        return $this;
    }

    /**
     * Set the webhook callback URL to update the message status.
     */
    public function statusCallback(string $callback): self
    {
        $this->statusCallback = $callback;

        return $this;
    }

    /**
     * Set the date the message should sent at.
     */
    public function schedule(string $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * Set the text which will be shown in the dashboard in place of the actual message content.
     */
    public function displayedMessage(string $displayedMessage): self
    {
        $this->displayedMessage = $displayedMessage;

        return $this;
    }

    /**
     * Set the web2sms client instance.
     */
    public function usingClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
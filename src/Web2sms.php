<?php

namespace ITPalert\Web2smsChannel;

use Psr\Http\Client\ClientInterface;
use RuntimeException;

use ITPalert\Web2sms\Credentials\Basic;
use ITPalert\Web2sms\Client;

class Web2sms
{
    /**
     * The web2sms configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * The HttpClient instance, if provided.
     *
     * @var \Psr\Http\Client\ClientInterface
     */
    protected $client;

    /**
     * Create a new Web2sms instance.
     *
     * @param  array  $config
     * @param  \Psr\Http\Client\ClientInterface|null  $client
     * @return void
     */
    public function __construct(array $config = [], ?ClientInterface $client = null)
    {
        $this->config = $config;
        $this->client = $client;
    }

    /**
     * Create a new Web2sms instance.
     *
     * @param  array  $config
     * @param  \Psr\Http\Client\ClientInterface|null  $client
     * @return static
     */
    public static function make(array $config, ?ClientInterface $client = null)
    {
        return new static($config, $client);
    }

    /**
     * Create a new Web2sms Client.
     *
     * @return ITPalert\Web2sms\Client
     *
     * @throws \RuntimeException
     */
    public function client()
    {
        $basicCredentials = null;

        if ($apiSecret = $this->config['api_secret'] ?? null) {
            $credentials = new Basic($this->config['api_key'], $apiSecret);
        }

        if (!$credentials) {
            throw new RuntimeException(
                'Please provide your Web2sms API credentials.'
            );
        } 

        return new Client($credentials, $this->config, $this->client);
    }
}
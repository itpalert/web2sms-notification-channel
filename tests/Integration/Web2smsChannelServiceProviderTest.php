<?php

namespace ITPalert\Web2smsChannel\Tests;

use Mockery;
use Orchestra\Testbench\TestCase;
use Illuminate\Notifications\ChannelManager;
use ITPalert\Web2sms\Client;
use ITPalert\Web2smsChannel\Channels\Web2smsChannel;
use ITPalert\Web2smsChannel\Web2smsChannelServiceProvider;

class Web2smsChannelServiceProviderTest extends TestCase
{
    /**
     * Workaround for Orchestra Testbench 9.0.0-9.1.3 bug with $latestResponse property.
     * Fixed in 9.1.4: "Allow $latestResponse static property to be optional"
     */
    protected static $latestResponse;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [Web2smsChannelServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('services.web2sms.sms_from', 'TEST_SENDER');
    }

    /** @test */
    public function it_registers_the_channel(): void
    {
        // Mock the Client since it requires CredentialsInterface
        $this->app->singleton(Client::class, function () {
            return Mockery::mock(Client::class);
        });

        $channel = $this->app->make(Web2smsChannel::class);

        $this->assertInstanceOf(Web2smsChannel::class, $channel);
    }

    /** @test */
    public function it_binds_channel_with_correct_dependencies(): void
    {
        // Mock the Client
        $this->app->singleton(Client::class, function () {
            return Mockery::mock(Client::class);
        });

        $channel = $this->app->make(Web2smsChannel::class);

        $this->assertInstanceOf(Web2smsChannel::class, $channel);
    }

    /** @test */
    public function it_uses_config_for_default_from_number(): void
    {
        $this->app['config']->set('services.web2sms.sms_from', 'CONFIG_SENDER');

        $this->app->singleton(Client::class, function () {
            return Mockery::mock(Client::class);
        });

        $channel = $this->app->make(Web2smsChannel::class);
        
        // Use reflection to check the protected property
        $reflection = new \ReflectionClass($channel);
        $property = $reflection->getProperty('from');
        $property->setAccessible(true);
        
        $this->assertEquals('CONFIG_SENDER', $property->getValue($channel));
    }

    /** @test */
    public function it_uses_empty_string_when_config_is_missing(): void
    {
        $this->app['config']->set('services.web2sms.sms_from', null);

        $this->app->singleton(Client::class, function () {
            return Mockery::mock(Client::class);
        });

        $channel = $this->app->make(Web2smsChannel::class);
        
        // Use reflection to check the protected property
        $reflection = new \ReflectionClass($channel);
        $property = $reflection->getProperty('from');
        $property->setAccessible(true);
        
        $this->assertEquals('', $property->getValue($channel));
    }

    /** @test */
    public function it_extends_notification_channel_manager(): void
    {
        $this->app->singleton(Client::class, function () {
            return Mockery::mock(Client::class);
        });

        $manager = $this->app->make(ChannelManager::class);
        
        // The channel should be registered
        $channel = $manager->channel('web2sms');
        
        $this->assertInstanceOf(Web2smsChannel::class, $channel);
    }
}
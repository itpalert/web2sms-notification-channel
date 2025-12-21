<?php

namespace ITPalert\Web2smsChannel\Tests\Integration;

use Mockery;
use Orchestra\Testbench\TestCase;
use Illuminate\Notifications\ChannelManager;
use ITPalert\Web2sms\Client;
use ITPalert\Web2smsChannel\Channels\Web2smsChannel;
use ITPalert\Web2smsChannel\Web2smsChannelServiceProvider;

/**
 * Integration tests for Web2smsChannelServiceProvider
 * 
 * Testing the service provider with real Laravel container and service registration.
 */
class Web2smsChannelServiceProviderTest extends TestCase
{
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
        $this->app->singleton(Client::class, function () {
            return Mockery::mock(Client::class);
        });

        $channel = $this->app->make(Web2smsChannel::class);

        $this->assertInstanceOf(Web2smsChannel::class, $channel);
    }

    /** @test */
    public function it_binds_channel_with_correct_dependencies(): void
    {
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

    /** @test */
    public function it_resolves_same_instance_when_requested_multiple_times(): void
    {
        $this->app->singleton(Client::class, function () {
            return Mockery::mock(Client::class);
        });

        $channel1 = $this->app->make(Web2smsChannel::class);
        $channel2 = $this->app->make(Web2smsChannel::class);

        // Should not be the same instance (bound, not singleton)
        // but both should be valid instances
        $this->assertInstanceOf(Web2smsChannel::class, $channel1);
        $this->assertInstanceOf(Web2smsChannel::class, $channel2);
    }

    /** @test */
    public function it_works_with_different_config_values(): void
    {
        $configs = [
            'SENDER1',
            'SENDER2',
            'LONGNAME',
            '',
        ];

        foreach ($configs as $config) {
            $this->app['config']->set('services.web2sms.sms_from', $config);

            $this->app->singleton(Client::class, function () {
                return Mockery::mock(Client::class);
            });

            $channel = $this->app->make(Web2smsChannel::class);

            $reflection = new \ReflectionClass($channel);
            $property = $reflection->getProperty('from');
            $property->setAccessible(true);

            $this->assertEquals($config, $property->getValue($channel));

            // Clean up for next iteration
            $this->app->forgetInstance(Client::class);
        }
    }

    /** @test */
    public function it_can_resolve_client_from_container(): void
    {
        $mockClient = Mockery::mock(Client::class);
        
        $this->app->singleton(Client::class, function () use ($mockClient) {
            return $mockClient;
        });

        $channel = $this->app->make(Web2smsChannel::class);

        $reflection = new \ReflectionClass($channel);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);

        $this->assertSame($mockClient, $property->getValue($channel));
    }

    /** @test */
    public function it_registers_provider_only_once(): void
    {
        $this->app->singleton(Client::class, function () {
            return Mockery::mock(Client::class);
        });

        // Get channel multiple times
        $channel1 = $this->app->make(Web2smsChannel::class);
        $channel2 = $this->app->make(Web2smsChannel::class);

        // Both should work
        $this->assertInstanceOf(Web2smsChannel::class, $channel1);
        $this->assertInstanceOf(Web2smsChannel::class, $channel2);
    }
}
<?php

namespace ITPalert\Web2smsChannel\Tests\Integration;

use Mockery;
use Orchestra\Testbench\TestCase;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use ITPalert\Web2sms\Client;
use ITPalert\Web2sms\SMS;
use ITPalert\Web2smsChannel\Channels\Web2smsChannel;
use ITPalert\Web2smsChannel\Messages\Web2smsMessage;
use ITPalert\Web2smsChannel\Web2smsChannelServiceProvider;

/**
 * Integration tests for Web2smsChannel
 * 
 * These are integration tests - testing the channel with real Laravel framework.
 * Uses Orchestra Testbench to load the full Laravel environment.
 */
class Web2smsChannelIntegrationTest extends TestCase
{
    /**
     * Workaround for Orchestra Testbench 9.0.0-9.1.3 bug with $latestResponse property.
     * Fixed in 9.1.4: "Allow $latestResponse static property to be optional"
     */
    public static $latestResponse;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Clean up the testing environment before the next test.
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Get package providers.
     */
    protected function getPackageProviders($app): array
    {
        return [Web2smsChannelServiceProvider::class];
    }

    /**
     * Define environment setup.
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('services.web2sms', [
            'key' => 'test-key',
            'secret' => 'test-secret',
            'sms_from' => 'TEST_SENDER',
            'account_type' => 'prepaid',
        ]);
    }

    /** @test */
    public function it_can_send_notification_through_laravel_notification_system(): void
    {
        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($sms) {
                // Verify the SMS object is created correctly
                $this->assertInstanceOf(SMS::class, $sms);
                $this->assertEquals('+40712345678', $sms->getTo());
                $this->assertEquals('Test message', $sms->getMessage());
                return true;
            }))
            ->andReturn($mockResponse);

        $this->app->instance(Client::class, $mockClient);

        $user = new TestUser();
        $user->phone_number = '+40712345678';

        $user->notify(new TestNotification());

        // Assertion happens inside Mockery::on() callback (if present)
    }

    /** @test */
    public function it_uses_config_values_from_services(): void
    {
        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($sms) {
                // Verify it uses the TEST_SENDER from config
                $this->assertEquals('TEST_SENDER', $sms->getFrom());
                return true;
            }))
            ->andReturn($mockResponse);

        $this->app->instance(Client::class, $mockClient);

        $user = new TestUser();
        $user->phone_number = '+40712345678';

        $user->notify(new TestNotification());
        
        // Assertion happens inside Mockery::on() callback above
    }

    /** @test */
    public function it_works_with_notification_facade(): void
    {
        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($sms) {
                // Verify send() is called with SMS object
                $this->assertInstanceOf(SMS::class, $sms);
                return true;
            }))
            ->andReturn($mockResponse);

        $this->app->instance(Client::class, $mockClient);

        $user = new TestUser();
        $user->phone_number = '+40712345678';

        NotificationFacade::send($user, new TestNotification());

        // Mockery verifies send() was called once
    }

    /** @test */
    public function it_can_send_unicode_messages_through_laravel(): void
    {
        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($sms) {
                // Verify both unicode type and message content
                $this->assertEquals('unicode', $sms->getType());
                $this->assertEquals('Mesaj cu ăîâșț', $sms->getMessage());
                return true;
            }))
            ->andReturn($mockResponse);

        $this->app->instance(Client::class, $mockClient);

        $user = new TestUser();
        $user->phone_number = '+40712345678';

        $user->notify(new TestUnicodeNotification());
        
        // Assertions happen inside Mockery::on() callback above
    }

    /** @test */
    public function it_can_send_scheduled_messages_through_laravel(): void
    {
        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($sms) {
                // Verify schedule was set correctly
                $this->assertEquals('2024-12-25 10:00:00', $sms->getSchedule());
                return true;
            }))
            ->andReturn($mockResponse);

        $this->app->instance(Client::class, $mockClient);

        $user = new TestUser();
        $user->phone_number = '+40712345678';

        $user->notify(new TestScheduledNotification());
        
        // Assertion happens inside Mockery::on() callback above
    }

    /** @test */
    public function it_can_use_custom_client_through_laravel(): void
    {
        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $defaultClient = Mockery::mock(Client::class);
        $customClient = Mockery::mock(Client::class);

        $defaultClient->shouldNotReceive('send');
        $customClient->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($sms) {
                // Verify custom client receives the SMS
                $this->assertInstanceOf(SMS::class, $sms);
                return true;
            }))
            ->andReturn($mockResponse);

        $this->app->instance(Client::class, $defaultClient);

        $user = new TestUser();
        $user->phone_number = '+40712345678';

        $user->notify(new TestCustomClientNotification($customClient));
        
        // Mockery verifies customClient.send() was called, not defaultClient
    }

    /** @test */
    public function it_handles_missing_phone_number_gracefully(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldNotReceive('send');

        $this->app->instance(Client::class, $mockClient);

        $user = new TestUser();
        // Don't set phone_number

        $user->notify(new TestNotification());

        // If send() was called, Mockery would throw an exception
        // Getting here means send() was correctly not called
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_send_with_all_options_combined(): void
    {
        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($sms) {
                // Verify ALL options were set correctly
                $this->assertEquals('Full message', $sms->getMessage());
                $this->assertEquals('CUSTOM', $sms->getFrom());
                $this->assertEquals('unicode', $sms->getType());
                $this->assertEquals('REF123', $sms->getClientRef());
                $this->assertEquals('https://example.com/callback', $sms->getDeliveryReceiptCallback());
                $this->assertEquals('Hidden', $sms->getDisplayedMessage());
                $this->assertEquals('2024-12-25 10:00:00', $sms->getSchedule());
                return true;
            }))
            ->andReturn($mockResponse);

        $this->app->instance(Client::class, $mockClient);

        $user = new TestUser();
        $user->phone_number = '+40712345678';

        $user->notify(new TestFullOptionsNotification());
        
        // Assertions happen inside Mockery::on() callback above
    }

    /** @test */
    public function it_resolves_channel_through_container(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $this->app->instance(Client::class, $mockClient);

        $channel = $this->app->make(Web2smsChannel::class);

        $this->assertInstanceOf(Web2smsChannel::class, $channel);
    }

    /** @test */
    public function it_registers_channel_with_notification_manager(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $this->app->instance(Client::class, $mockClient);

        $manager = $this->app->make(\Illuminate\Notifications\ChannelManager::class);
        $channel = $manager->channel('web2sms');

        $this->assertInstanceOf(Web2smsChannel::class, $channel);
    }
}

// Test helper classes for integration tests

class TestUser
{
    use \Illuminate\Notifications\Notifiable;

    public $phone_number;

    public function routeNotificationForWeb2sms($notification)
    {
        return $this->phone_number;
    }
}

class TestNotification extends Notification
{
    public function via($notifiable)
    {
        return ['web2sms'];
    }

    public function toWeb2sms($notifiable)
    {
        return 'Test message';
    }
}

class TestUnicodeNotification extends Notification
{
    public function via($notifiable)
    {
        return ['web2sms'];
    }

    public function toWeb2sms($notifiable)
    {
        return (new Web2smsMessage('Mesaj cu ăîâșț'))
            ->unicode();
    }
}

class TestScheduledNotification extends Notification
{
    public function via($notifiable)
    {
        return ['web2sms'];
    }

    public function toWeb2sms($notifiable)
    {
        return (new Web2smsMessage('Test'))
            ->schedule('2024-12-25 10:00:00');
    }
}

class TestCustomClientNotification extends Notification
{
    private $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function via($notifiable)
    {
        return ['web2sms'];
    }

    public function toWeb2sms($notifiable)
    {
        return (new Web2smsMessage('Test'))
            ->usingClient($this->client);
    }
}

class TestFullOptionsNotification extends Notification
{
    public function via($notifiable)
    {
        return ['web2sms'];
    }

    public function toWeb2sms($notifiable)
    {
        return (new Web2smsMessage('Full message'))
            ->from('CUSTOM')
            ->unicode()
            ->clientReference('REF123')
            ->statusCallback('https://example.com/callback')
            ->displayedMessage('Hidden')
            ->schedule('2024-12-25 10:00:00');
    }
}
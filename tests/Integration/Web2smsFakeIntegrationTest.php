<?php

namespace ITPalert\Web2smsChannel\Tests\Integration;

use Illuminate\Notifications\Notification;
use ITPalert\Web2sms\Contracts\Client;
use ITPalert\Web2sms\SMS;
use ITPalert\Web2sms\Testing\Web2smsFake;
use ITPalert\Web2sms\Web2smsServiceProvider;
use ITPalert\Web2smsChannel\Channels\Web2smsChannel;
use ITPalert\Web2smsChannel\Messages\Web2smsMessage;
use ITPalert\Web2smsChannel\Web2smsChannelServiceProvider;
use Mockery;
use Orchestra\Testbench\TestCase;

/**
 * The two packages wired together, with nothing hand-bound.
 *
 * Every other test in this suite registers only the channel provider and binds
 * its own mock client, which means they pass whether or not this package can
 * actually talk to itpalert/web2sms. They would have passed while the channel
 * type-hinted the concrete client and refused the fake outright, which is the
 * defect this file exists to catch.
 *
 * So both providers are registered here and no client is bound by hand. What
 * the channel receives is whatever itpalert/web2sms decides to hand it, which
 * under the testing environment is the fake.
 */
class Web2smsFakeIntegrationTest extends TestCase
{
    /** Testbench 9.0.0-9.1.3 expects this property to exist. */
    public static $latestResponse;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            Web2smsServiceProvider::class,
            Web2smsChannelServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Deliberately no credentials and no driver: this is what an
        // application that has never configured SMS looks like.
        $app['config']->set('services.web2sms', ['sms_from' => 'TEST_SENDER']);
    }

    public function test_the_channel_accepts_the_fake(): void
    {
        // Constructing it at all is the assertion: the constructor type hint
        // has to admit something that is not the concrete HTTP client.
        $channel = $this->app->make(Web2smsChannel::class);

        $this->assertInstanceOf(Web2smsChannel::class, $channel);
        $this->assertInstanceOf(Web2smsFake::class, $this->app->make(Client::class));
    }

    public function test_a_notification_is_recorded_instead_of_sent(): void
    {
        $fake = $this->app->make(Client::class);

        $this->app->make(Web2smsChannel::class)->send(
            $this->notifiable('+40712345678'),
            $this->notification('Your ITP expires on 26-08-2026'),
        );

        $fake->assertSentCount(1)
            ->assertSentTo('+40712345678', fn (SMS $sms) => str_contains($sms->getMessage(), 'ITP'));
    }

    public function test_the_configured_sender_survives_the_round_trip(): void
    {
        $fake = $this->app->make(Client::class);

        $this->app->make(Web2smsChannel::class)->send(
            $this->notifiable('+40712345678'),
            $this->notification('Anything'),
        );

        $fake->assertSent(fn (SMS $sms) => $sms->getFrom() === 'TEST_SENDER');
    }

    public function test_a_message_object_reaches_the_fake_intact(): void
    {
        $fake = $this->app->make(Client::class);

        $message = (new Web2smsMessage('Detailed message'))
            ->clientReference('REF123');

        $this->app->make(Web2smsChannel::class)->send(
            $this->notifiable('+40712345678'),
            $this->notification($message),
        );

        $fake->assertSent(fn (SMS $sms) => $sms->getClientRef() === 'REF123'
            && $sms->getMessage() === 'Detailed message');
    }

    public function test_a_notifiable_without_a_number_sends_nothing(): void
    {
        $fake = $this->app->make(Client::class);

        $result = $this->app->make(Web2smsChannel::class)->send(
            $this->notifiable(null),
            $this->notification('Nobody to send this to'),
        );

        $this->assertNull($result);
        $fake->assertNothingSent();
    }

    public function test_the_channel_is_reachable_through_the_notification_manager(): void
    {
        // The driver name applications actually use in via().
        $channel = $this->app->make(\Illuminate\Notifications\ChannelManager::class)
            ->driver('web2sms');

        $this->assertInstanceOf(Web2smsChannel::class, $channel);
    }

    private function notifiable(?string $phoneNumber)
    {
        $notifiable = Mockery::mock();
        $notifiable->shouldReceive('routeNotificationFor')
            ->with('Web2sms', Mockery::type(Notification::class))
            ->andReturn($phoneNumber);

        return $notifiable;
    }

    private function notification(string|Web2smsMessage $message)
    {
        $notification = Mockery::mock(Notification::class);
        $notification->shouldReceive('toWeb2sms')->andReturn($message);

        return $notification;
    }
}

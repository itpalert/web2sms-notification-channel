<?php

namespace ITPalert\Web2smsChannel\Tests\Unit;

use Mockery;
use PHPUnit\Framework\TestCase;
use Illuminate\Notifications\Notification;
use ITPalert\Web2sms\Contracts\Client;
use ITPalert\Web2sms\SMS;
use ITPalert\Web2smsChannel\Channels\Web2smsChannel;
use ITPalert\Web2smsChannel\Messages\Web2smsMessage;

/**
 * Unit tests for Web2smsChannel
 * 
 * These are true unit tests - all dependencies are mocked.
 * Testing the channel logic in complete isolation.
 */
class Web2smsChannelTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_send_a_notification_with_string_message(): void
    {
        $client = Mockery::mock(Client::class);
        $channel = new Web2smsChannel($client, 'SENDER');

        $notifiable = $this->createNotifiable('+40712345678');
        $notification = $this->createNotification('Test message');

        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $client->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($sms) {
                // Verify SMS object properties
                $this->assertInstanceOf(SMS::class, $sms);
                $this->assertEquals('+40712345678', $sms->getTo());
                $this->assertEquals('SENDER', $sms->getFrom());
                $this->assertEquals('Test message', $sms->getMessage());
                $this->assertEquals('text', $sms->getType());
                return true;
            }))
            ->andReturn($mockResponse);

        $result = $channel->send($notifiable, $notification);

        // Verify the send method returns the response object
        $this->assertSame($mockResponse, $result);
    }

    /** @test */
    public function it_can_send_a_notification_with_message_object(): void
    {
        $client = Mockery::mock(Client::class);
        $channel = new Web2smsChannel($client, 'SENDER');

        $notifiable = $this->createNotifiable('+40712345678');
        $notification = $this->createNotification(new Web2smsMessage('Test message'));

        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $client->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($sms) {
                // Verify we receive an SMS object
                $this->assertInstanceOf(SMS::class, $sms);
                return true;
            }))
            ->andReturn($mockResponse);

        $result = $channel->send($notifiable, $notification);

        // Verify the send method returns the response object
        $this->assertSame($mockResponse, $result);
    }

    /** @test */
    public function it_returns_null_when_no_route_is_defined(): void
    {
        $client = Mockery::mock(Client::class);
        $channel = new Web2smsChannel($client, 'SENDER');

        $notifiable = $this->createNotifiable(null);
        $notification = $this->createNotification('Test');

        $client->shouldNotReceive('send');

        $result = $channel->send($notifiable, $notification);

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_null_when_route_is_empty_string(): void
    {
        $client = Mockery::mock(Client::class);
        $channel = new Web2smsChannel($client, 'SENDER');

        $notifiable = $this->createNotifiable('');
        $notification = $this->createNotification('Test');

        $client->shouldNotReceive('send');

        $result = $channel->send($notifiable, $notification);

        $this->assertNull($result);
    }

    /** @test */
    public function it_uses_custom_from_number_from_message(): void
    {
        $client = Mockery::mock(Client::class);
        $channel = new Web2smsChannel($client, 'DEFAULT');

        $message = (new Web2smsMessage('Test'))->from('CUSTOM');
        $notifiable = $this->createNotifiable('+40712345678');
        $notification = $this->createNotification($message);

        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $client->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($sms) {
                // Verify the SMS has the custom from number
                $this->assertEquals('CUSTOM', $sms->getFrom());
                return true;
            }))
            ->andReturn($mockResponse);

        $result = $channel->send($notifiable, $notification);
        
        // Verify the send method returns the response object
        $this->assertSame($mockResponse, $result);
    }

    /** @test */
    public function it_uses_default_from_when_message_from_is_empty(): void
    {
        $client = Mockery::mock(Client::class);
        $channel = new Web2smsChannel($client, 'DEFAULT');

        $message = new Web2smsMessage('Test');
        $notifiable = $this->createNotifiable('+40712345678');
        $notification = $this->createNotification($message);

        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $client->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($sms) {
                // Verify the SMS uses the default from number
                $this->assertEquals('DEFAULT', $sms->getFrom());
                return true;
            }))
            ->andReturn($mockResponse);

        $result = $channel->send($notifiable, $notification);
        
        // Verify the send method returns the response object
        $this->assertSame($mockResponse, $result);
    }

    /** @test */
    public function it_sets_client_reference_when_not_empty(): void
    {
        $client = Mockery::mock(Client::class);
        $channel = new Web2smsChannel($client, 'SENDER');

        $message = (new Web2smsMessage('Test'))->clientReference('REF123');
        $notifiable = $this->createNotifiable('+40712345678');
        $notification = $this->createNotification($message);

        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $client->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($sms) {
                // Verify the client reference was set correctly
                $this->assertEquals('REF123', $sms->getClientRef());
                return true;
            }))
            ->andReturn($mockResponse);

        $result = $channel->send($notifiable, $notification);
        
        // Verify the send method returns the response object
        $this->assertSame($mockResponse, $result);
    }

    /** @test */
    public function it_does_not_set_empty_client_reference(): void
    {
        $client = Mockery::mock(Client::class);
        $channel = new Web2smsChannel($client, 'SENDER');

        $message = new Web2smsMessage('Test');
        $notifiable = $this->createNotifiable('+40712345678');
        $notification = $this->createNotification($message);

        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $client->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($sms) {
                // Verify client reference is empty (not set)
                $this->assertEquals('', $sms->getClientRef());
                return true;
            }))
            ->andReturn($mockResponse);

        $result = $channel->send($notifiable, $notification);
        
        // Verify the send method returns the response object
        $this->assertSame($mockResponse, $result);
    }

    /** @test */
    public function it_sets_status_callback_when_not_empty(): void
    {
        $client = Mockery::mock(Client::class);
        $channel = new Web2smsChannel($client, 'SENDER');

        $message = (new Web2smsMessage('Test'))
            ->statusCallback('https://example.com/callback');
        $notifiable = $this->createNotifiable('+40712345678');
        $notification = $this->createNotification($message);

        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $client->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($sms) {
                // Verify status callback URL was set
                $this->assertEquals('https://example.com/callback', $sms->getDeliveryReceiptCallback());
                return true;
            }))
            ->andReturn($mockResponse);

        $result = $channel->send($notifiable, $notification);
        
        // Verify the send method returns the response object
        $this->assertSame($mockResponse, $result);
    }

    /** @test */
    public function it_sets_displayed_message_when_not_empty(): void
    {
        $client = Mockery::mock(Client::class);
        $channel = new Web2smsChannel($client, 'SENDER');

        $message = (new Web2smsMessage('Test'))
            ->displayedMessage('Hidden');
        $notifiable = $this->createNotifiable('+40712345678');
        $notification = $this->createNotification($message);

        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $client->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($sms) {
                // Verify displayed message was set
                $this->assertEquals('Hidden', $sms->getDisplayedMessage());
                return true;
            }))
            ->andReturn($mockResponse);

        $result = $channel->send($notifiable, $notification);
        
        // Verify the send method returns the response object
        $this->assertSame($mockResponse, $result);
    }

    /** @test */
    public function it_sets_schedule_when_not_null(): void
    {
        $client = Mockery::mock(Client::class);
        $channel = new Web2smsChannel($client, 'SENDER');

        $message = (new Web2smsMessage('Test'))
            ->schedule('2024-12-25 10:00:00');
        $notifiable = $this->createNotifiable('+40712345678');
        $notification = $this->createNotification($message);

        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $client->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($sms) {
                // Verify schedule was set correctly
                $this->assertEquals('2024-12-25 10:00:00', $sms->getSchedule());
                return true;
            }))
            ->andReturn($mockResponse);

        $result = $channel->send($notifiable, $notification);
        
        // Verify the send method returns the response object
        $this->assertSame($mockResponse, $result);
    }

    /** @test */
    public function it_uses_unicode_message_type(): void
    {
        $client = Mockery::mock(Client::class);
        $channel = new Web2smsChannel($client, 'SENDER');

        $message = (new Web2smsMessage('Test'))->unicode();
        $notifiable = $this->createNotifiable('+40712345678');
        $notification = $this->createNotification($message);

        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $client->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($sms) {
                // Verify message type is unicode
                $this->assertEquals('unicode', $sms->getType());
                return true;
            }))
            ->andReturn($mockResponse);

        $result = $channel->send($notifiable, $notification);
        
        // Verify the send method returns the response object
        $this->assertSame($mockResponse, $result);
    }

    /** @test */
    public function it_uses_custom_client_from_message(): void
    {
        $defaultClient = Mockery::mock(Client::class);
        $customClient = Mockery::mock(Client::class);
        $channel = new Web2smsChannel($defaultClient, 'SENDER');

        $message = (new Web2smsMessage('Test'))->usingClient($customClient);
        $notifiable = $this->createNotifiable('+40712345678');
        $notification = $this->createNotification($message);

        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $defaultClient->shouldNotReceive('send');
        $customClient->shouldReceive('send')
            ->once()
            ->andReturn($mockResponse);

        $result = $channel->send($notifiable, $notification);
        
        // Verify the send method returns the response object
        $this->assertSame($mockResponse, $result);
    }

    /** @test */
    public function it_trims_message_content(): void
    {
        $client = Mockery::mock(Client::class);
        $channel = new Web2smsChannel($client, 'SENDER');

        $notifiable = $this->createNotifiable('+40712345678');
        $notification = $this->createNotification('  Trimmed message  ');

        $mockResponse = Mockery::mock('ITPalert\Web2sms\Responses\SendResponse');
        
        $client->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($sms) {
                // Verify message was trimmed (no leading/trailing spaces)
                $this->assertEquals('Trimmed message', $sms->getMessage());
                return true;
            }))
            ->andReturn($mockResponse);

        $result = $channel->send($notifiable, $notification);
        
        // Verify the send method returns the response object
        $this->assertSame($mockResponse, $result);
    }

    /** @test */
    public function it_throws_exception_for_invalid_message_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Notification must return a string or Web2smsMessage instance from toWeb2sms method');

        $client = Mockery::mock(Client::class);
        $channel = new Web2smsChannel($client, 'SENDER');

        $notifiable = $this->createNotifiable('+40712345678');
        $notification = $this->createNotification(['invalid' => 'type']);

        $channel->send($notifiable, $notification);
    }

    /**
     * Helper to create a mock notifiable
     */
    private function createNotifiable($phoneNumber)
    {
        $notifiable = Mockery::mock();
        $notifiable->shouldReceive('routeNotificationFor')
            ->with('Web2sms', Mockery::type(Notification::class))
            ->andReturn($phoneNumber);

        return $notifiable;
    }

    /**
     * Helper to create a mock notification
     */
    private function createNotification($message)
    {
        $notification = Mockery::mock(Notification::class);
        $notification->shouldReceive('toWeb2sms')
            ->andReturn($message);

        return $notification;
    }
}
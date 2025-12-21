<?php

namespace ITPalert\Web2smsChannel\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ITPalert\Web2smsChannel\Messages\Web2smsMessage;

/**
 * Unit tests for Web2smsMessage
 * 
 * These are pure unit tests - no Laravel dependencies, no mocking needed.
 * Testing the message builder class in complete isolation.
 */
class Web2smsMessageTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated_with_content(): void
    {
        $message = new Web2smsMessage('Test content');

        $this->assertEquals('Test content', $message->content);
    }

    /** @test */
    public function it_can_be_instantiated_without_content(): void
    {
        $message = new Web2smsMessage();

        $this->assertEquals('', $message->content);
    }

    /** @test */
    public function it_has_correct_default_values(): void
    {
        $message = new Web2smsMessage();

        $this->assertEquals('', $message->content);
        $this->assertEquals('', $message->from);
        $this->assertEquals('text', $message->type);
        $this->assertNull($message->client);
        $this->assertEquals('', $message->clientReference);
        $this->assertEquals('', $message->statusCallback);
        $this->assertNull($message->schedule);
        $this->assertEquals('', $message->displayedMessage);
    }

    /** @test */
    public function it_can_set_content(): void
    {
        $message = new Web2smsMessage();
        $result = $message->content('New content');

        $this->assertSame($message, $result);
        $this->assertEquals('New content', $message->content);
    }

    /** @test */
    public function it_can_set_from(): void
    {
        $message = new Web2smsMessage();
        $result = $message->from('SENDER');

        $this->assertSame($message, $result);
        $this->assertEquals('SENDER', $message->from);
    }

    /** @test */
    public function it_can_set_unicode_type(): void
    {
        $message = new Web2smsMessage();
        $result = $message->unicode();

        $this->assertSame($message, $result);
        $this->assertEquals('unicode', $message->type);
    }

    /** @test */
    public function it_can_set_client_reference(): void
    {
        $message = new Web2smsMessage();
        $result = $message->clientReference('REF123');

        $this->assertSame($message, $result);
        $this->assertEquals('REF123', $message->clientReference);
    }

    /** @test */
    public function it_validates_client_reference_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Client reference cannot exceed 40 characters');

        $message = new Web2smsMessage();
        $message->clientReference(str_repeat('a', 41));
    }

    /** @test */
    public function it_accepts_exactly_40_character_client_reference(): void
    {
        $message = new Web2smsMessage();
        $reference = str_repeat('a', 40);
        $result = $message->clientReference($reference);

        $this->assertSame($message, $result);
        $this->assertEquals($reference, $message->clientReference);
    }

    /** @test */
    public function it_can_set_status_callback(): void
    {
        $message = new Web2smsMessage();
        $result = $message->statusCallback('https://example.com/callback');

        $this->assertSame($message, $result);
        $this->assertEquals('https://example.com/callback', $message->statusCallback);
    }

    /** @test */
    public function it_can_set_schedule(): void
    {
        $message = new Web2smsMessage();
        $result = $message->schedule('2024-12-25 10:00:00');

        $this->assertSame($message, $result);
        $this->assertEquals('2024-12-25 10:00:00', $message->schedule);
    }

    /** @test */
    public function it_can_set_displayed_message(): void
    {
        $message = new Web2smsMessage();
        $result = $message->displayedMessage('Hidden content');

        $this->assertSame($message, $result);
        $this->assertEquals('Hidden content', $message->displayedMessage);
    }

    /** @test */
    public function it_supports_method_chaining(): void
    {
        $message = (new Web2smsMessage())
            ->content('Test message')
            ->from('SENDER')
            ->unicode()
            ->clientReference('REF123')
            ->statusCallback('https://example.com/callback')
            ->schedule('2024-12-25 10:00:00')
            ->displayedMessage('Hidden');

        $this->assertEquals('Test message', $message->content);
        $this->assertEquals('SENDER', $message->from);
        $this->assertEquals('unicode', $message->type);
        $this->assertEquals('REF123', $message->clientReference);
        $this->assertEquals('https://example.com/callback', $message->statusCallback);
        $this->assertEquals('2024-12-25 10:00:00', $message->schedule);
        $this->assertEquals('Hidden', $message->displayedMessage);
    }

    /** @test */
    public function it_allows_empty_strings_for_optional_fields(): void
    {
        $message = new Web2smsMessage();
        
        $message->from('');
        $message->clientReference('');
        $message->statusCallback('');
        $message->displayedMessage('');

        $this->assertEquals('', $message->from);
        $this->assertEquals('', $message->clientReference);
        $this->assertEquals('', $message->statusCallback);
        $this->assertEquals('', $message->displayedMessage);
    }

    /** @test */
    public function it_preserves_content_with_special_characters(): void
    {
        $content = 'Message with ăîâșț and émojis 🎉';
        $message = new Web2smsMessage($content);

        $this->assertEquals($content, $message->content);
    }

    /** @test */
    public function it_can_chain_all_methods_in_any_order(): void
    {
        $message = (new Web2smsMessage())
            ->displayedMessage('Hidden')
            ->schedule('2024-12-25 10:00:00')
            ->statusCallback('https://example.com/callback')
            ->clientReference('REF123')
            ->unicode()
            ->from('SENDER')
            ->content('Test message');

        $this->assertEquals('Test message', $message->content);
        $this->assertEquals('Hidden', $message->displayedMessage);
    }

    /** @test */
    public function it_can_overwrite_previously_set_values(): void
    {
        $message = new Web2smsMessage();
        
        $message->content('First');
        $this->assertEquals('First', $message->content);
        
        $message->content('Second');
        $this->assertEquals('Second', $message->content);

        $message->from('SENDER1');
        $message->from('SENDER2');
        $this->assertEquals('SENDER2', $message->from);
    }
}
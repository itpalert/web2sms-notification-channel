# Web2sms notifications channel for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/itpalert/web2sms-notification-channel.svg?style=flat-square)](https://packagist.org/packages/itpalert/web2sms-notification-channel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/itpalert/web2sms-notification-channel/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/itpalert/web2sms-notification-channel/actions?query=workflow%3Atests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/itpalert/web2sms-notification-channel.svg?style=flat-square)](https://packagist.org/packages/itpalert/web2sms-notification-channel)

This package makes it easy to send notifications using [Web2sms](https://www.web2sms.ro/) with Laravel 8.0+.

## Features

✅ Full PHP 8.0+ type safety  
✅ Supports Laravel 8.x through 12.x  
✅ SMS scheduling  
✅ Unicode message support  
✅ Status callbacks  
✅ Custom sender IDs  
✅ Client reference tracking  
✅ 30+ comprehensive tests  

## Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Available Message Methods](#available-message-methods)
- [Testing](#testing)
- [Changelog](#changelog)
- [Credits](#credits)
- [License](#license)

## Installation

You can install the package via composer:

```bash
composer require itpalert/web2sms-notification-channel
```

## Configuration

### Setting up the Web2sms service

Add the following environment variables to your `.env`:

```env
WEB2SMS_KEY=8c78axxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
WEB2SMS_SECRET=e9a689cfxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
WEB2SMS_SMS_FROM=ALERT
WEB2SMS_ACCOUNT_TYPE=prepaid
```

Add the following to your `config/services.php`:

```php
'web2sms' => [
    'key' => env('WEB2SMS_KEY'),
    'secret' => env('WEB2SMS_SECRET'),
    'sms_from' => env('WEB2SMS_SMS_FROM', ''),
    'account_type' => env('WEB2SMS_ACCOUNT_TYPE', 'prepaid'),
],
```

## Usage

Now you can use the channel in your `via()` method inside the notification:

```php
use ITPalert\Web2smsChannel\Messages\Web2smsMessage;
use Illuminate\Notifications\Notification;

class ProjectCreated extends Notification
{
    public function via($notifiable)
    {
        return ['web2sms'];
    }

    public function toWeb2sms($notifiable)
    {
        return new Web2smsMessage('Your project has been created!');
    }
}
```

### Routing notifications

Add the `routeNotificationForWeb2sms` method to your Notifiable model to specify which phone number to use:

```php
public function routeNotificationForWeb2sms(Notification $notification): string
{
    return $this->phone_number;
}
```

### Simple string message

```php
public function toWeb2sms($notifiable): string
{
    return 'Simple SMS message';
}
```

## Available Message Methods

### Basic Methods

```php
public function toWeb2sms($notifiable): Web2smsMessage
{
    return (new Web2smsMessage())
        ->content('Your message content here')
        ->from('SENDER_ID');
}
```

### Unicode Support

For messages containing special characters (Romanian diacritics, emojis, etc.):

```php
public function toWeb2sms($notifiable): Web2smsMessage
{
    return (new Web2smsMessage('Mesaj cu diacritice: ăîâșț'))
        ->unicode();
}
```

### Client Reference

Track messages with a unique reference (max 40 characters):

```php
public function toWeb2sms($notifiable): Web2smsMessage
{
    return (new Web2smsMessage('Your message'))
        ->clientReference('order-' . $this->order->id);
}
```

### Status Callbacks

Get notified when message status changes:

```php
public function toWeb2sms($notifiable): Web2smsMessage
{
    return (new Web2smsMessage('Your message'))
        ->statusCallback(route('sms.status', $this->id));
}
```

### Scheduled Messages

Send messages at a specific time:

```php
public function toWeb2sms($notifiable): Web2smsMessage
{
    return (new Web2smsMessage('Reminder message'))
        ->schedule('2024-12-25 10:00:00');
}
```

### Displayed Message

Hide actual message content in dashboard (useful for sensitive data):

```php
public function toWeb2sms($notifiable): Web2smsMessage
{
    return (new Web2smsMessage('Your OTP is: 123456'))
        ->displayedMessage('OTP sent to customer');
}
```

### Custom Client

Use a different Web2sms client for specific notifications:

```php
public function toWeb2sms($notifiable): Web2smsMessage
{
    $customClient = new \ITPalert\Web2sms\Client(
        'different-key',
        'different-secret'
    );

    return (new Web2smsMessage('Your message'))
        ->usingClient($customClient);
}
```

### Complete Example

```php
public function toWeb2sms($notifiable): Web2smsMessage
{
    return (new Web2smsMessage())
        ->content('Comanda #' . $this->order->id . ' a fost confirmată!')
        ->from('MAGAZIN')
        ->unicode()
        ->clientReference('order-' . $this->order->id)
        ->statusCallback(route('sms.status', $this->order->id))
        ->displayedMessage('Order confirmation sent');
}
```

## Testing

Run the tests with:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

Run static analysis:

```bash
composer analyse
```

Check code style:

```bash
composer check-style
```

Fix code style:

```bash
composer format
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Gere Attila](https://github.com/itpalert)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
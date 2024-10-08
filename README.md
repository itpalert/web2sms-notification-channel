# Web2sms notifications channel for Laravel

This package makes it easy to send notifications using [web2sms](https://www.web2sms.ro/) with Laravel 10.0.

## Contents

- [Installation](#installation)
	- [Setting up the web2sms service](#setting-up-the-web2sms-service)
- [Usage](#usage)
	- [Available Message methods](#available-message-methods)
- [Credits](#credits)


## Installation

You can install the package via composer:

``` bash
composer require itpalert/web2sms-notification-channel
```

### Setting up the web2sms service

Add the following env variables to your `.env`:

```php
// .env
...
WEB2SMS_KEY=8c78axxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
WEB2SMS_SECRET=e9a689cfxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
WEB2SMS_SMS_FROM=ALERT
WEB2SMS_ACCOUNT_TYPE=prepaid
...
```

Add the folowing to the `config/services.php`

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

``` php
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
        return new Web2smsMessage('Content');
    }
}
```

In order to let your Notification know which phone number to use, add the `routeNotificationForWeb2sms` method to your Notifiable model.

This method needs to return a phone number.

```php
public function routeNotificationForWeb2sms(Notification $notification)
{
    return $this->phone_number;
}
```

### Available Message methods

- `content(string $content)`: Accepts a string value for the sms content.
- `from(string $from)`: Accepts a string value for the sender number.
- `unicode()`: Set the message type.
- `clientReference(string $clientReference)`: Set the client reference (up to 40 characters).
- `statusCallback(string $callback)`: Set the webhook callback URL to update the message status.
- `schedule(string $schedule)`: Set the date the message should sent at.
- `displayedMessage(string $displayedMessage)`:Set the text which will be shown in the dashboard in place of the actual message content.
- `usingClient(Client $client)`: Set the web2sms client instance.

## Credits

- [Gere Attila](https://github.com/itpalert)
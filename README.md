## Support us

At Opscale, we’re passionate about contributing to the open-source community by providing solutions that help businesses scale efficiently. If you’ve found our tools helpful, here are a few ways you can show your support:

⭐ **Star this repository** to help others discover our work and be part of our growing community. Every star makes a difference!

💬 **Share your experience** by leaving a review on [Trustpilot](https://www.trustpilot.com/review/opscale.co) or sharing your thoughts on social media. Your feedback helps us improve and grow!

📧 **Send us feedback** on what we can improve at [feedback@opscale.co](mailto:feedback@opscale.co). We value your input to make our tools even better for everyone.

🙏 **Get involved** by actively contributing to our open-source repositories. Your participation benefits the entire community and helps push the boundaries of what’s possible.

💼 **Hire us** if you need custom dashboards, admin panels, internal tools or MVPs tailored to your business. With our expertise, we can help you systematize operations or enhance your existing product. Contact us at hire@opscale.co to discuss your project needs.

Thanks for helping Opscale continue to scale! 🚀



## Description

Add webhooks support triggered by your resource CRUD operations.

Integrations are everywhere, even managing your operations in your Nova app you will need to communicate with external systems for triggering actions or keep records up to date. Webhooks are the best solution for that!

![Webhook creation](https://raw.githubusercontent.com/opscale-co/nova-webhooks/refs/heads/main/screenshots/webhook-creation.png)
![Wwebhook demo](https://raw.githubusercontent.com/opscale-co/nova-webhooks/refs/heads/main/screenshots/webhook-demo.png)

## Installation

[![Latest Version on Packagist](https://img.shields.io/packagist/v/opscale-co/nova-webhooks.svg?style=flat-square)](https://packagist.org/packages/opscale-co/nova-webhooks)

You can install the package in to a Laravel app that uses [Nova](https://nova.laravel.com) via composer:

```bash

composer require opscale-co/nova-webhooks

```

Next up, you must register the tool with Nova. This is typically done in the `tools` method of the `NovaServiceProvider`.

```php

// in app/Providers/NovaServiceProvider.php
// ...
public function tools()
{
    return [
        // ...
        new \Opscale\NovaWebhooks\Tool(),
    ];
}

```

This package uses [Laravel Webhook Server](https://github.com/spatie/laravel-webhook-server) internally to fire the webhook event, any further configuration can be done publishing the configuration file using:

`php artisan vendor:publish --provider="Spatie\WebhookServer\WebhookServerServiceProvider"`

## Usage

You will see a "Webhooks" item in your menu by default. You can your webhooks here and they will be triggered after the CRUD operation is completed. Remember to have your queues working.

## Testing

``` bash

npm run test

```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/opscale-co/.github/blob/main/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email development@opscale.co instead of using the issue tracker.

## Credits

- [Opscale](https://github.com/opscale-co)
- [Spatie](https://github.com/spatie/laravel-webhook-server)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
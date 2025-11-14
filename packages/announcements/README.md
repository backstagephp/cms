# Backstage Announcements

[![Latest Version on Packagist](https://img.shields.io/packagist/v/backstage/backstage-announcements.svg?style=flat-square)](https://packagist.org/packages/backstage/backstage-announcements)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/backstage/backstage-announcements/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/backstage/backstage-announcements/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/backstage/backstage-announcements/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/backstage/backstage-announcements/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/backstage/backstage-announcements.svg?style=flat-square)](https://packagist.org/packages/backstage/backstage-announcements)

A powerful Filament plugin for managing announcements in your Laravel application. Create, manage, and display announcements with customizable scopes, colors, and navigation controls.

## Installation

You can install the package via composer:

```bash
composer require backstage/announcements
```

> [!IMPORTANT]
> If you have not set up a custom theme and are using Filament Panels follow the instructions in the [Filament Docs](https://filamentphp.com/docs/4.x/styling/overview#creating-a-custom-theme) first.

After setting up a custom theme add the plugin's views to your theme css file or your app's css file if using the standalone packages.

```css
@source '../../../../vendor/backstage/announcements/resources/**/*.blade.php';
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="backstage-announcements-migrations"
php artisan migrate
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="backstage-announcements-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

Add the plugin to your Filament panel provider:

```php
use Backstage\Announcements\AnnouncementsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            AnnouncementsPlugin::make(),
        ]);
}
```

### Navigation Control

Control whether the announcements resource appears in the navigation menu:

```php
AnnouncementsPlugin::make()
    ->canRegisterNavigation(false) // Hide from navigation
```

```php
AnnouncementsPlugin::make()
    ->canRegisterNavigation(true) // Show in navigation (default)
```

### Forced Scopes

Limit which scopes (resources/pages) are available when creating announcements. You can specify the full class names of resources or pages:

```php
AnnouncementsPlugin::make()
    ->forceScopes([
        'App\\Filament\\Resources\\Users\\UserResource',
        'App\\Filament\\Resources\\Products\\ProductResource',
        'App\\Filament\\Pages\\Dashboard',
    ])
```

The plugin will automatically match these class names to the formatted scope names that appear in the dropdown, so users will see friendly names like "Users (list)" instead of the full class names. The form automatically converts between class names (stored in database) and formatted names (displayed in UI) using the `formatStateUsing()` method.

### Creating Announcements

Once configured, you can create announcements through the Filament interface:

1. **Title**: The announcement title
2. **Content**: The announcement content (supports HTML)
3. **Scopes**: Select which resources/pages the announcement should appear on
4. **Color**: Choose a color theme for the announcement

### Displaying Announcements

Announcements are automatically displayed on the selected scopes using the included Livewire component. The component handles:

- Dismissal tracking per user
- Color theming
- Responsive design
- Accessibility features

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Manoj Hortulanus](https://github.com/arduinomaster22)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

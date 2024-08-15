# SoftDeletesFlag

Designed for high-load applications and optimizes queries with soft deletes by utilizing a boolean field for indexing, than using unique timestamps.
## Installation

You can install the package via composer:

```bash
composer require vortech/softdeletes-flag
```

## Usage
```php
Add "$table->softDeletesFlag()" to your migration.
```

```php
Add "use SoftDeletesFlag" trait to your model.
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email mate@vortech.hu instead of using the issue tracker.

## Credits

-   [Mate Papp](https://github.com/vortech)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

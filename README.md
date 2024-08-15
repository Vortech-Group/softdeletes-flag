# SoftDeletesFlag

Designed for high-load applications and optimizes queries with soft deletes by utilizing a boolean field for indexing, than using unique timestamps.
## Installation

You can install the package by adding the following repository:
```php
"repositories": {
    "softdeletes-flag": {
        "type": "github",
        "url": "https://github.com/Vortech-Group/softdeletes-flag",
        "options": {
            "symlink": true
        }
    }
},
```

Next you can install it via composer:
```bash
composer require vortech/softdeletes-flag @dev
```

## Usage

Add the soft delete column to your migration up method:
```php
$table->softDeletesFlag()
```

Add the trait to your model:
```php
use SoftDeletesFlag
```

If you want drop the soft delete column add this line to the down method of your migration:
```php
$table->dropSoftDeletesFlag()
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

### Security

If you discover any security related issues, please email mate@vortech.hu instead of using the issue tracker.

## Credits

- Mate Papp, Developer @ Vortech

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

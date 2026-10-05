# Changelog

All notable changes to `softdeletes-flag` will be documented in this file

## 1.0.0 - 2026-10-05

### Added

- Initial release
- `SoftDeletesFlag` trait that soft deletes models using an indexed boolean column instead of a timestamp
- `softDeletesFlag()` and `dropSoftDeletesFlag()` migration helpers
- `softdeletes-flag:install` Artisan command that publishes the config file
- Configurable column name (`softdeletes-flag.column_name`)
- Route model binding support, including `withTrashed()`, scoped bindings and backed enums
- Laravel 13 support (PHP 8.3+)

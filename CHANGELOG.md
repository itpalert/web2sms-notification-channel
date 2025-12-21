# Changelog

All notable changes to `web2sms-notification-channel` will be documented in this file.

## [Unreleased]

## [2.0.0] - 2024-12-21

### Added
- Full PHP 8.0+ type hints on all classes, methods, and properties
- Validation for client reference length (max 40 characters)
- Exception handling for invalid message types
- Comprehensive test suite with 30+ tests
- PHPStan level 8 static analysis support
- PHP CS Fixer configuration for code style
- GitHub Actions workflow for automated testing

### Changed
- Improved empty value handling (don't set empty client references, callbacks, etc.)
- Better null handling throughout the codebase
- Service provider now handles missing configuration gracefully
- Return type declarations added to all methods

### Fixed
- Bug where empty strings would be passed to setter methods
- Missing validation for invalid notification return types
- Inconsistent default values in Web2smsMessage

### Security
- Added input validation for client reference
- Better type safety prevents common bugs

## [1.2.3] - Previous Release

### Added
- Initial release with basic functionality
- Support for Laravel 8.x through 12.x
- SMS sending via Web2sms API
- Message scheduling
- Status callbacks
- Unicode message support

[Unreleased]: https://github.com/itpalert/web2sms-notification-channel/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/itpalert/web2sms-notification-channel/compare/v1.2.3...v2.0.0
[1.2.3]: https://github.com/itpalert/web2sms-notification-channel/releases/tag/v1.2.3
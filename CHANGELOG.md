# CHANGELOG

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [6.0.0] - 2024-07-30
### Added
- Add URI translation, package:// URI scheme & bundle spec schemas ([#362](https://github.com/jsonrainbow/json-schema/pull/362))
- Add quiet option ([#382](https://github.com/jsonrainbow/json-schema/pull/382))
- Add option to disable validation of "format" constraint ([#383](https://github.com/jsonrainbow/json-schema/pull/383))
- Add more unit tests ([#366](https://github.com/jsonrainbow/json-schema/pull/366))
- Reset errors prior to validation ([#386](https://github.com/jsonrainbow/json-schema/pull/386))
- Allow the schema to be an associative array ([#389](https://github.com/jsonrainbow/json-schema/pull/389))
- Enable FILTER_FLAG_EMAIL_UNICODE for email format if present ([#398](https://github.com/jsonrainbow/json-schema/pull/398))
- Add enum wrapper ([#375](https://github.com/jsonrainbow/json-schema/pull/375))
- Add option to validate the schema ([#357](https://github.com/jsonrainbow/json-schema/pull/357))
- Add support for "const" ([#507](https://github.com/jsonrainbow/json-schema/pull/507))
- Added note about supported Draft versions ([#620](https://github.com/jsonrainbow/json-schema/pull/620))
- Add linting GH action
### Changed
- Centralize errors ([#364](https://github.com/jsonrainbow/json-schema/pull/364))
- Revert "An email is a string, not much else." ([#373](https://github.com/jsonrainbow/json-schema/pull/373))
- Improvements to type coercion ([#384](https://github.com/jsonrainbow/json-schema/pull/384))
- Don't add a file:// prefix to URI that already have a scheme ([#455](https://github.com/jsonrainbow/json-schema/pull/455))
- Enhancement: Normalize` composer.json` ([#505](https://github.com/jsonrainbow/json-schema/pull/505))
- Correct echo `sprintf` for `printf` ([#634](https://github.com/jsonrainbow/json-schema/pull/634))
- Streamline validation of Regex ([#650](https://github.com/jsonrainbow/json-schema/pull/650))
- Streamline validation of patternProperties Regex ([#653](https://github.com/jsonrainbow/json-schema/pull/653))
- Switch to GH Actions ([#670](https://github.com/jsonrainbow/json-schema/pull/670))
- Updated PHPStan
- Remove unwanted whitespace ([#700](https://github.com/jsonrainbow/json-schema/pull/700))
- Bump to v4 versions of GitHub actions ([#722](https://github.com/jsonrainbow/json-schema/pull/722))
- Update references to jsonrainbow ([#725](https://github.com/jsonrainbow/json-schema/pull/725))
### Deprecated
- Mark check() and coerce() as deprecated ([#476](https://github.com/jsonrainbow/json-schema/pull/476))
### Removed
- Remove stale files from #357 (obviated by #362) ([#400](https://github.com/jsonrainbow/json-schema/pull/400))
- Remove unnecessary fallbacks when args accept null
- Removed unused variable in UndefinedConstraint ([#698](https://github.com/jsonrainbow/json-schema/pull/698))
- Remove dead block of code ([#710](https://github.com/jsonrainbow/json-schema/pull/710))
### Fixed
- Add use line for InvalidArgumentException ([#370](https://github.com/jsonrainbow/json-schema/pull/370))
- Add use line for InvalidArgumentException & adjust scope ([#372](https://github.com/jsonrainbow/json-schema/pull/372))
- Add provided schema under a dummy / internal URI (fixes #376) ([#378](https://github.com/jsonrainbow/json-schema/pull/378))
- Don't throw exceptions until after checking anyOf / oneOf ([#394](https://github.com/jsonrainbow/json-schema/pull/394))
- Fix infinite recursion on some schemas when setting defaults (#359) ([#365](https://github.com/jsonrainbow/json-schema/pull/365))
- Fix autoload to work properly with composer dependencies ([#401](https://github.com/jsonrainbow/json-schema/pull/401))
- Ignore $ref siblings & abort on infinite-loop references ([#437](https://github.com/jsonrainbow/json-schema/pull/437))
- Don't cast multipleOf to be an integer for the error message ([#471](https://github.com/jsonrainbow/json-schema/pull/471))
- Strict Enum/Const Object Checking ([#518](https://github.com/jsonrainbow/json-schema/pull/518))
- Return original value when no cast ([#535](https://github.com/jsonrainbow/json-schema/pull/535))
- Allow `marc-mabe/php-enum` v2.x and v3.x. ([#464](https://github.com/jsonrainbow/json-schema/pull/464))
- Deprecated warning message on composer install command ([#614](https://github.com/jsonrainbow/json-schema/pull/614))
- Allow `marc-mabe/php-enum` v4.x ([#629](https://github.com/jsonrainbow/json-schema/pull/629))
- Fixed method convertJsonPointerIntoPropertyPath in wrong class ([#655](https://github.com/jsonrainbow/json-schema/pull/655))
- Fix type validation failing for "any" and false-y type wording ([#686](https://github.com/jsonrainbow/json-schema/pull/686))
- Correct code style
- Fix: Clean up `.gitattributes` ([#687](https://github.com/jsonrainbow/json-schema/pull/687))
- Fix: Order `friendsofphp/php-cs-fixer` rules ([#688](https://github.com/jsonrainbow/json-schema/pull/688))
- HTTP to HTTPS redirection breaks remote reference resolution ([#709](https://github.com/jsonrainbow/json-schema/pull/709))
- Corrected several typos and code style issues
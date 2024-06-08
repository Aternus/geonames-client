# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

### Changed

### Deprecated

### Removed

### Fixed

### Security

## [2.3.2] - 2024-06-08

### Changed

- Updated cacert.pem
- Allow access to env variables for forks

## [2.3.1] - 2024-01-03

### Added

- Dockerized the project
- Dockerized code styling and unit tests
- Added GitHub workflows

### Changed

- Aligned with PSR-12
- Updated .gitignore
- Removed composer.json version field in favour of VCS tags

### Fixed

- Fixed PHP 7.2 syntax
- Fixed documentation
- Fixed deployments

## [2.3.0] - 2023-12-30

### Added

- Added ability to specify connection timeout (PR #8)

### Changed

- Updated cacert.pem

## [2.2.2] - 2021-11-27

### Added

- Added Unit Tests for `getLastTotalResultsCount` method

### Changed

- Improved the call method

### Fixed

- Fixed typos

## [2.2.1] - 2021-11-06

### Fixed

- Fixed Unit Tests for `getLastUrlRequested` method

### Security

- Updated dependencies.

## [2.2.0] - 2021-03-20

### Fixed

- Semantic versioning (the last update was a new feature, which bumps the minor version)

## [2.1.1] - 2021-03-20

### Added

- Add `lastUrlRequested()` method to retrieve the URL used in the most recent request (PR #3)

### Fixed

- Code styling after PR #3
- Publish script `cd` statement

## [2.1.0] - 2020-09-05

### Added

- Added `getLastTotalResultsCount` method to the Client instance (PR #2)
- Added support for params where the values have the same key (PR #1)

### Fixed

- Code styling after PR #1 & PR #2

### Security

- Updated dependencies.

## [2.0.0] - 2020-08-14

### Added

- More documentation
- New `cacert.pem` file

### Changed

- Requires PHP 7.2+

### Fixed

- PHP7.2 and PHPUnit8 related issues

### Security

- Updated dependencies.

## [1.0.10] - 2020-04-29

### Changed

- Updated tests.
- Updated `.md` files formatting.

### Security

- Updated dependencies.

## [1.0.9] - 2018-09-09

### Changed

- Updated phpcs.xml.
- Updated .gitignore.

### Fixed

- Address endpoint not working.

## [1.0.8] - 2018-09-09

### Added

- Changelog.

### Changed

- The publish script now supports 0 as the first digit of the version.
- Updated dependencies.

### Fixed

- packagist.org: GitHub deprecated the use of Apps, switched to Web-hooks.

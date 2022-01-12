# Changelog

## 4.0.0

### Added

- Added support for Symfony 5.4

### Changed

- Dropped PHP 7.4 support

## 3.0.0

### Changed

- Added PHP 8.X support
- Dropped support for PHP 7.1, 7.2, and 7.3
- Dropped support for Symfony 3.4


## 2.0.1

### Fixed

- Fixed the routing configuration to reference the correct controller

## 2.0.0

### Changed

- Pheanstalk 4.X is required
- PHP 7.2+ is required
- Symfony 3.4 or 4.X are required

### Fixed
n/a

### Added

- `Pheanstalk\Contract\PheanstalkInterface` is aliased to the default Pheanstalk
  connection so autowiring should work.
- `PMG\PheanstalkBundle\ConnectionManager` to provide a way to access all
  connections outside of the container.

## 1.0

Brand new!

BC Breaks:

- None

New Features:

- Brand new bundle! Everything is new.

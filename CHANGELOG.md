# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

### Added

### Changed

### Deprecated

### Removed

### Fixed

### Security

## [0.5.0] - 2016-08-05

### Added

* Add informative customer error message

### Changed

* Deprecated `Api::FIELD_FIRSTNAME` in favour of `Api::FIELD_FIRST_NAME`
* Deprecated `Api::FIELD_LASTNAME` in favour of `Api::FIELD_LAST_NAME`

## [0.4.2] - 2016-08-01

### Fixed

* Fix issue with multi line `mandate_text`

## [0.4.1] - 2016-07-29

### Fixed

* Prevent internal fields from being sent to Payone

## [0.4.0] - 2016-07-14

### Added

* Add mandate download for SEPA
* Allow capturing authorized payments

### Fixed

* Fix various documentation issues
* Allow POST to mandate form without triggering errors
* Fix issues with long narrative text
* Fix status update handling

## [0.3.3] - 2016-04-19

## [0.3.2] - 2016-04-18

## [0.3.1] - 2016-04-14

## [0.3.0] - 2016-04-12

## [0.2.0] - 2016-04-04

### Added

* Add Smarty template for SEPA mandate
* Add more unit tests
* Add more field constants

## [0.1.0] - 2016-03-24

### Added

* Add Support for [Paydirekt](https://www.paydirekt.de/)
* Add Support for [Giropay](https://www.giropay.de/)
* Add Support for SEPA Direct Debit


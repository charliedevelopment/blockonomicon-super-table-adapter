# Changelog

## Unreleased

## 1.0.2 - 2019-11-18

### Fixed
- Excluded the `contentTable` setting from export/import, as it being provided explicitly can cause a database table to not be generated for the field.

## 1.0.1 - 2018-11-15

### Added
- Import configuration mechanism has been extended to provide the handle of the block being imported, to help ensure proper unique IDs for import controls.

### Changed
- Internal adapters for native fields changed from using the `blockHandle` misnomer to a more apt `fieldHandle`.

## 1.0.0 - 2018-03-08

The initial release of the Blockonomicon Super Table Adapter.

### Added
- Support for Super Table fields within Blockonomicon.

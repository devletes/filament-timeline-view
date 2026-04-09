# Changelog

All notable changes to `devletes/filament-timeline-view` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Render smoke tests for `Schemas\Components\Timeline` and `Infolists\Components\TimelineEntry`.
- `NormalizesTimelineData` trait extracting shared item/group normalization.
- `.gitattributes` with `export-ignore` rules so dist archives exclude tests, workbench, and dev tooling.

### Changed
- `TimelineWidget` now uses the shared normalization trait; duplicate logic removed.
- Widget blade view no longer constructs an anonymous shim class — it uses the widget directly as `$timelineComponent`.
- Collapsed day summary pill is now static text rather than a second toggle; the dedicated caret on the date row owns the expand interaction.

### Fixed
- Test suite pins `Carbon::setTestNow()` so date-based weekday assertions are stable across calendar dates.

## [0.1.0] — initial pre-release

Initial release of the package.

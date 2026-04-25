# Filament Timeline View

[![Latest Version on Packagist](https://img.shields.io/packagist/v/devletes/filament-timeline-view.svg?style=flat-square)](https://packagist.org/packages/devletes/filament-timeline-view)
[![Total Downloads](https://img.shields.io/packagist/dt/devletes/filament-timeline-view.svg?style=flat-square)](https://packagist.org/packages/devletes/filament-timeline-view)
[![License](https://img.shields.io/packagist/l/devletes/filament-timeline-view.svg?style=flat-square)](https://packagist.org/packages/devletes/filament-timeline-view)

Render Filament Tables as a chronological timeline.

> **Status: in development.** This package is being rebuilt on top of Filament Tables. The previous implementation is preserved on the `pre-pivot` branch for reference. Documentation will land here as the new API stabilises.

## Planned shape

```php
$table
    ->columns([
        TimelineCard::make()
            ->title('title')
            ->content('body')
            ->image('hero_image_url')
            ->author('author.name', 'author.avatar_url')
            ->time('published_at'),
    ])
    ->groups([Group::make('published_at')->date()])
    ->actions([
        ViewAction::make(),
        EditAction::make(),
        DeleteAction::make(),
    ])
    ->asTimeline(); // or ->asDoubleSidedTimeline()
```

## Installation

```bash
composer require devletes/filament-timeline-view
```

## License

MIT — see [LICENSE.md](LICENSE.md).

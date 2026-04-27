<?php

namespace Devletes\FilamentTimelineView\Tables\Columns;

use Carbon\CarbonInterface;
use Closure;
use Filament\Support\Components\Contracts\HasEmbeddedView;
use Filament\Tables\Columns\Layout\Component;
use Illuminate\Support\Carbon;

class TimelineEntry extends Component implements HasEmbeddedView
{
    protected string|Closure|null $titleField = null;

    protected string|Closure|null $contentField = null;

    protected string|Closure|null $imageField = null;

    protected string|Closure|null $authorNameField = null;

    protected string|Closure|null $authorAvatarField = null;

    protected string|Closure|null $timeField = null;

    protected string $timeFormat = 'g:i A';

    final public function __construct() {}

    public static function make(): static
    {
        $static = app(static::class);
        $static->configure();

        return $static;
    }

    public function title(string|Closure $field): static
    {
        $this->titleField = $field;

        return $this;
    }

    public function content(string|Closure $field): static
    {
        $this->contentField = $field;

        return $this;
    }

    public function image(string|Closure $field): static
    {
        $this->imageField = $field;

        return $this;
    }

    public function author(string|Closure $name, string|Closure|null $avatar = null): static
    {
        $this->authorNameField = $name;
        $this->authorAvatarField = $avatar;

        return $this;
    }

    public function time(string|Closure $field, string $format = 'g:i A'): static
    {
        $this->timeField = $field;
        $this->timeFormat = $format;

        return $this;
    }

    public function toEmbeddedHtml(): string
    {
        $record = $this->getRecord();

        $title = $this->resolveFieldValue($this->titleField, $record);
        $content = $this->resolveFieldValue($this->contentField, $record);
        $image = $this->resolveFieldValue($this->imageField, $record);
        $authorName = $this->resolveFieldValue($this->authorNameField, $record);
        $authorAvatar = $this->resolveFieldValue($this->authorAvatarField, $record);
        $time = $this->resolveTimeValue($record);

        $hasMedia = filled($image);
        $hasFooter = filled($authorName) || filled($time);

        ob_start(); ?>
        <div class="ftv-card-layout<?= $hasMedia ? ' ftv-card-layout-has-media' : '' ?>">
            <?php if ($hasMedia) { ?>
                <div class="ftv-card-media">
                    <img class="ftv-card-image" src="<?= e($image) ?>" alt="">
                </div>
            <?php } ?>
            <div class="ftv-card-copy">
                <?php if (filled($title)) { ?>
                    <h3 class="ftv-card-title"><?= e($title) ?></h3>
                <?php } ?>
                <?php if (filled($content)) { ?>
                    <p class="ftv-card-content"><?= nl2br(e($content)) ?></p>
                <?php } ?>
                <?php if ($hasFooter) { ?>
                    <div class="ftv-card-meta">
                        <div class="ftv-card-meta-left"></div>
                        <div class="ftv-card-meta-right">
                            <?php if (filled($authorName)) { ?>
                                <div class="ftv-card-meta-user">
                                    <?php if (filled($authorAvatar)) { ?>
                                        <img class="ftv-card-meta-user-avatar" src="<?= e($authorAvatar) ?>" alt="">
                                    <?php } ?>
                                    <span class="ftv-card-meta-user-name"><?= e($authorName) ?></span>
                                </div>
                            <?php } ?>
                            <?php if (filled($time)) { ?>
                                <span class="ftv-card-meta-time"><?= e($time) ?></span>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    protected function resolveFieldValue(string|Closure|null $field, mixed $record): mixed
    {
        if ($field === null) {
            return null;
        }

        if ($field instanceof Closure) {
            return $field($record);
        }

        return data_get($record, $field);
    }

    protected function resolveTimeValue(mixed $record): ?string
    {
        if ($this->timeField === null) {
            return null;
        }

        if ($this->timeField instanceof Closure) {
            $value = ($this->timeField)($record);

            if (blank($value)) {
                return null;
            }

            if ($value instanceof CarbonInterface) {
                return $value->translatedFormat($this->timeFormat);
            }

            return (string) $value;
        }

        return $this->formatTime(data_get($record, $this->timeField));
    }

    protected function formatTime(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $carbon = $value instanceof CarbonInterface ? $value : Carbon::parse($value);

        return $carbon->translatedFormat($this->timeFormat);
    }
}

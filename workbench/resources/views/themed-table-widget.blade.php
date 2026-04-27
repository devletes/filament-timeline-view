<x-filament-widgets::widget class="fi-wi-table demo-themed-pulse">
    <style>
        .demo-themed-pulse .ftv-shell {
            --ftv-line-color: oklch(60% 0.18 280);
            --ftv-card-surface: oklch(98.5% 0.012 280);
            --ftv-card-ring: oklch(85% 0.08 280);
            --ftv-hover-shadow-color: oklch(50% 0.18 280 / 0.30);
            --ftv-dot-halo: oklch(94% 0.05 280);
        }

        .demo-themed-pulse .ftv-card-title {
            color: oklch(35% 0.15 280);
        }

        .dark .demo-themed-pulse .ftv-shell {
            --ftv-line-color: oklch(70% 0.18 280);
            --ftv-card-surface: oklch(22% 0.04 280);
            --ftv-card-ring: oklch(45% 0.10 280);
            --ftv-hover-shadow-color: oklch(15% 0.04 280 / 0.50);
            --ftv-dot-halo: oklch(28% 0.04 280);
        }

        .dark .demo-themed-pulse .ftv-card-title {
            color: oklch(82% 0.10 280);
        }
    </style>

    {{ $this->table ?? null }}
</x-filament-widgets::widget>

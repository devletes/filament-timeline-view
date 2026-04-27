<?php

namespace Workbench\App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Workbench\App\Filament\Pages\CustomLayoutDemo;
use Workbench\App\Filament\Pages\DoubleSidedDemo;
use Workbench\App\Filament\Pages\GermanDemo;
use Workbench\App\Filament\Pages\SingleSidedDemo;
use Workbench\App\Filament\Pages\ThemedDemo;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        View::addNamespace('workbench', __DIR__.'/../../../resources/views');

        // README screenshot helper. With ?theme=dark the .dark class is applied
        // before initial render; with ?demo=kebab the first card's kebab dropdown
        // is opened on load; with ?demo=collapsed every group except the first is
        // collapsed. Has zero effect when no query param is present.
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn (): HtmlString => new HtmlString(<<<'HTML'
                <script>
                    (function () {
                        const params = new URLSearchParams(window.location.search);
                        const theme = params.get('theme');
                        if (theme === 'dark' || theme === 'light') {
                            try { localStorage.setItem('theme', theme); } catch (e) {}
                        }
                    })();
                </script>
HTML)
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): HtmlString => new HtmlString(<<<'HTML'
                <script>
                    (function () {
                        const demo = new URLSearchParams(window.location.search).get('demo');
                        if (!demo) return;
                        const openDropdown = (trigger) => {
                            if (!trigger) return;
                            trigger.dispatchEvent(new MouseEvent('mousedown', { bubbles: true, cancelable: true, button: 0 }));
                        };
                        const run = () => {
                            if (demo === 'kebab') {
                                openDropdown(document.querySelector('.ftv-card-actions .fi-dropdown-trigger'));
                            } else if (demo === 'collapsed') {
                                document.querySelectorAll('.ftv-date-toggle').forEach((b, i) => { if (i >= 1) b.click(); });
                            }
                        };
                        if (document.readyState === 'complete') setTimeout(run, 400);
                        else window.addEventListener('load', () => setTimeout(run, 400));
                    })();
                </script>
HTML)
        );
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->darkMode(true)
            ->colors([
                'primary' => Color::Blue,
            ])
            ->pages([
                Dashboard::class,
                SingleSidedDemo::class,
                DoubleSidedDemo::class,
                CustomLayoutDemo::class,
                ThemedDemo::class,
                GermanDemo::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Settings;
use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\Expenses\ExpenseResource;
use App\Filament\Resources\FollowUps\FollowUpResource;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Plans\PlanResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Resources\Users\UserResource;
use App\Http\Middleware\SetAppLocale;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Filament panel provider for the main admin panel.
 */
class AdminPanelProvider extends PanelProvider
{
    /**
     * Configure the panel.
     */
    public function panel(Panel $panel): Panel
    {
        return $this->basePanel($panel)
            ->navigation(fn (NavigationBuilder $builder) => $this->buildNavigation($builder));
    }

    /**
     * Configure the base panel options.
     */
    public function basePanel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('/')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->passwordReset()
            ->brandName('Gymie')
            ->brandLogo('/images/logo.svg')
            ->darkModeBrandLogo('/images/logo-dark-mode.svg')
            ->brandLogoHeight('2.5rem')
            ->favicon('/images/favicon.svg')
            ->unsavedChangesAlerts()
            ->colors($this->colors())
            ->defaultThemeMode(ThemeMode::Light)
            ->sidebarWidth('12rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
                Settings::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->plugins([FilamentShieldPlugin::make()
                ->navigationIcon(fn (): null => null)
                ->activeNavigationIcon(fn (): null => null)])
            ->middleware([
                SetAppLocale::class,
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
            ])
            ->databaseNotifications()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): HtmlString => new HtmlString(
                    Blade::render('@livewire(\\App\\Filament\\Livewire\\LocaleSwitcher::class, [], key(\'locale-switcher\'))')
                ),
            );
    }

    /**
     * Build grouped navigation for the admin panel.
     */
    protected function buildNavigation(NavigationBuilder $builder): NavigationBuilder
    {
        $administration = [
            ...Settings::getNavigationItems(),
            ...UserResource::getNavigationItems(),
            ...RoleResource::getNavigationItems(),
        ];

        $sales = [
            ...EnquiryResource::getNavigationItems(),
            ...FollowUpResource::getNavigationItems(),
        ];

        $billing = [
            ...InvoiceResource::getNavigationItems(),
            ...ExpenseResource::getNavigationItems(),
        ];

        $memberships = [
            ...MemberResource::getNavigationItems(),
            ...PlanResource::getNavigationItems(),
            ...ServiceResource::getNavigationItems(),
            ...SubscriptionResource::getNavigationItems(),
        ];

        return $builder
            ->groups([
                NavigationGroup::make(__('app.navigation.groups.sales'))
                    ->icon('heroicon-o-shopping-cart')
                    ->items($sales)
                    ->collapsed(false),

                NavigationGroup::make(__('app.navigation.groups.memberships'))
                    ->icon('heroicon-o-user-group')
                    ->items($memberships)
                    ->collapsed(false),

                NavigationGroup::make(__('app.navigation.groups.billing'))
                    ->icon('heroicon-o-document-text')
                    ->items($billing)
                    ->collapsed(false),

                NavigationGroup::make(__('app.navigation.groups.administration'))
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->items($administration)
                    ->collapsed(false),
            ])
            ->item(
                NavigationItem::make(__('app.navigation.dashboard'))
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn () => Dashboard::getUrl())
                    ->isActiveWhen(fn () => request()->routeIs('filament.admin.pages.dashboard'))
            );
    }

    /**
     * Panel color palette.
     *
     * @return array<string, mixed>
     */
    protected function colors(): array
    {
        return [
            'primary' => [
                50 => '#EAF3ED',
                100 => '#D2E9DC',
                200 => '#A9D3BB',
                300 => '#7CBB99',
                400 => '#57A57C',
                500 => '#2F8F5B',
                600 => '#227249',
                700 => '#1B5B3A',
                800 => '#1B402C',
                900 => '#16281F',
                950 => '#10201A',
            ],
            'danger' => Color::Rose,
            'gray' => Color::Gray,
            'info' => Color::Blue,
            'success' => Color::Emerald,
            'warning' => Color::Orange,
        ];
    }
}

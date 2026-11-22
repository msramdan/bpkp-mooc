<?php

namespace App\Providers;

use App\Services\AiAssistant\AiAssistantService;
use App\Services\AiAssistant\AiAssistantSettings;
use App\Services\AiAssistant\Provider\AiProvider;
use App\Services\AiAssistant\Provider\ProviderFactory;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Services\H5P\Contracts\H5PExtractorInterface::class,
            \App\Services\H5P\Extractors\ZipArchiveExtractor::class
        );

        $this->app->scoped(AiAssistantSettings::class);
        $this->app->scoped(\App\Support\Activity\RequestContext::class);

        // Provider aktif (Anthropic / SumoPod) dipilih dari pengaturan dashboard.
        $this->app->bind(AiProvider::class, fn ($app): AiProvider => $app->make(ProviderFactory::class)->make());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Log aktivitas autentikasi (login, logout, gagal, lockout, reset password).
        \Illuminate\Support\Facades\Event::subscribe(\App\Listeners\LogAuthenticationActivity::class);

        // Perlindungan abuse di luar kuota harian: maksimal 6 request/menit per user.
        RateLimiter::for('ai-assistant', function (Request $request): Limit {
            return Limit::perMinute(6)->by('ai:'.($request->user()?->id ?: $request->ip()))
                ->response(fn () => response()->json([
                    'ok' => false,
                    'type' => 'rate_limited',
                    'message' => __('Terlalu banyak permintaan. Tunggu sebentar sebelum bertanya lagi.'),
                ], 429));
        });

        View::composer('layouts.app', function ($view): void {
            $user = auth()->user();
            $available = false;

            if ($user) {
                try {
                    $available = app(AiAssistantService::class)->canUse($user);
                } catch (Throwable) {
                    $available = false;
                }
            }

            $view->with('aiAssistantAvailable', $available);
        });
    }
}

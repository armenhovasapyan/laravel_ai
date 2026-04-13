<?php

namespace App\Providers;

use App\Services\MurfFalconService;
use App\Services\OpenAIPromptGenerationService;
use App\Services\OpenAITranslationService;
use App\Services\OpenAIWhisperService;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OpenAIPromptGenerationService::class, function ($app) {
            return new OpenAIPromptGenerationService(
                apiKey: config('services.openai.api_key'),
                model: config('services.openai.model_image_gen', 'gpt-4o'),
            );
        });

        $this->app->singleton(OpenAIWhisperService::class, function ($app) {
            return new OpenAIWhisperService(
                apiKey: config('services.openai.api_key'),
                model: config('services.openai.model_whisper', 'whisper-1'),
            );
        });

        $this->app->singleton(OpenAITranslationService::class, function ($app) {
            return new OpenAITranslationService(
                apiKey: config('services.openai.api_key'),
                model: config('services.openai.model_whisper', 'gpt-4o-mini'),
            );
        });

        $this->app->singleton(MurfFalconService::class, function ($app) {
            return new MurfFalconService(
                apiKey: config('services.murf.api_key'),
                apiUrl: config('services.murf.api_url'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        RateLimiter::for('api', function(Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });

        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer', 'BearerAuth')
            );
        });
    }
}

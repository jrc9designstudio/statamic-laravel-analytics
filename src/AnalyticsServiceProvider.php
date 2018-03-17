<?php

namespace Spatie\Analytics;

use Illuminate\Support\ServiceProvider;
use Spatie\Analytics\Exceptions\InvalidConfiguration;

class AnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/laravel-analytics.php' => config_path('laravel-analytics.php'),
        ]);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $analyticsConfig = [
            /*
             * The view id of which you want to display data.
             */
            'view_id' => env('GOOGLE_ANALYTICS_VIEW_ID'),

            /*
             * Path to the json file with service account credentials. Take a look at the README of this package
             * to learn how to get this file.
             */
            'service_account_credentials_json' => storage_path('../../site/settings/addons/google-analytics-credentials.json'),

            /*
             * The amount of minutes the Google API responses will be cached.
             * If you set this to zero, the responses won't be cached at all.
             */
            'cache_lifetime_in_minutes' => 60 * 24,

            /*
             * The directory where the underlying Google_Client will store it's cache files.
             */
            'cache_location' => storage_path('../local/cache/laravel-google-analytics/google-cache/'),
        ];

        $this->app->bind(AnalyticsClient::class, function () use ($analyticsConfig) {
            return AnalyticsClientFactory::createForConfig($analyticsConfig);
        });

        $this->app->bind(Analytics::class, function () use ($analyticsConfig) {
            $this->guardAgainstInvalidConfiguration($analyticsConfig);

            $client = app(AnalyticsClient::class);

            return new Analytics($client, $analyticsConfig['view_id']);
        });

        $this->app->alias(Analytics::class, 'laravel-analytics');
    }

    /**
     * @param array|null $analyticsConfig
     *
     * @throws \Spatie\Analytics\Exceptions\InvalidConfiguration
     */
    protected function guardAgainstInvalidConfiguration($analyticsConfig)
    {
        if (empty($analyticsConfig['view_id'])) {
            throw InvalidConfiguration::viewIdNotSpecified();
        }

        if (! file_exists($analyticsConfig['service_account_credentials_json'])) {
            throw InvalidConfiguration::credentialsJsonDoesNotExist($analyticsConfig['service_account_credentials_json']);
        }
    }
}

<?php

namespace JRC9DS\Analytics;

use Illuminate\Support\ServiceProvider;
use JRC9DS\Analytics\Exceptions\InvalidConfiguration;

class AnalyticsServiceProvider extends ServiceProvider
{
    private $google_analytics;

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
        $this->google_analytics = new \Statamic\Addons\GoogleAnalytics\GoogleAnalyticsAPI;

        $analyticsConfig = $this->google_analytics->analyticsConfig();

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
     * @throws \JRC9DS\Analytics\Exceptions\InvalidConfiguration
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

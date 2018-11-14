<?php

namespace MrsJoker\Trade;

use Illuminate\Support\ServiceProvider;

class TradeProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Create a new service provider instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application $app
     * @return void
     */
    public function __construct($app)
    {
        parent::__construct($app);

    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('trade.php')
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $app = $this->app;

        // merge default config
        $this->mergeConfigFrom(
            __DIR__.'/config/config.php',
            'trade'
        );

        // create trade
        $app->singleton('trade', function ($app) {
            return new TradeManager($app['config']->get('trade'));
        });

        $app->alias('trade', 'MrsJoker\Trade\TradeManager');

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['trade'];
    }
}

<?php

namespace MrsJoker\Trade;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use MrsJoker\Trade\Facades\Trade;

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
            __DIR__ . '/config/config.php' => config_path('trade.php')
        ]);
        $this->bladeDirectives();
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
            __DIR__ . '/config/config.php',
            'trade'
        );

        // create trade
        $app->singleton('trade', function ($app) {
            return new TradeManager($app['config']->get('trade'));
        });

        $app->alias('trade', 'MrsJoker\Trade\TradeManager');
    }


    private function bladeDirectives()
    {
        //role
        Blade::directive('role', function ($expression) {
            $user = app("request")->user();
            if (empty($user)){
                return ;
            }
            return "<?php if(Trade::make('rbac')->executeCommand('user')->hasRole({$user->id}, {$expression})) : ?>";
        });
        Blade::directive('endrole', function ($expression) {
            $user = app("request")->user();
            if (empty($user)){
                return ;
            }
            return "<?php endif; ?>";
        });
        //permission
        Blade::directive('permission', function ($expression) {
            $user = app("request")->user();
            if (empty($user)){
                return ;
            }
            return "<?php if(Trade::make('rbac')->executeCommand('user')->can({$user->id}, {$expression})) : ?>";
        });
        Blade::directive('endpermission', function ($expression) {
            $user = app("request")->user();
            if (empty($user)){
                return ;
            }
            return "<?php endif; ?>";
        });
//        // Call to Entrust::can
//        \Blade::directive('permission', function($expression) {
        /*            return "<?php if (\\Entrust::can({$expression})) : ?>";*/
//        });
//        \Blade::directive('endpermission', function($expression) {
        /*            return "<?php endif; // Entrust::can ?>";*/
//        });
//        // Call to Entrust::ability
//        \Blade::directive('ability', function($expression) {
        /*            return "<?php if (\\Entrust::ability({$expression})) : ?>";*/
//        });
//        \Blade::directive('endability', function($expression) {
        /*            return "<?php endif; // Entrust::ability ?>";*/
//        });
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

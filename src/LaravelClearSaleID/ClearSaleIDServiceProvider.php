<?php

namespace RodrigoPedra\LaravelClearSaleID;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ClearSaleIDServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $this->bootConfig();
        $this->bootViews();

        $this->app->alias( 'clearsale-id', ClearSaleIDService::class );
    }

    public function register()
    {
        $this->app->singleton( 'clearsale-id', function () {
            return new ClearSaleIDService(
                $this->app[ 'request' ],
                $this->app[ 'log' ],
                $this->app[ 'config' ]->get( 'clearsale-id.environment' ),
                $this->app[ 'config' ]->get( 'clearsale-id.entity_code' ),
                $this->app[ 'config' ]->get( 'clearsale-id.appid' ),
                $this->app[ 'config' ]->get( 'clearsale-id.debug' )
            );
        } );

        $this->app->alias( 'clearsale-id', ClearSaleIDService::class );
    }

    private function bootConfig()
    {
        $this->publishes( [
            __DIR__ . '/../config/clearsale-id.php' => config_path( 'clearsale-id.php' ),
        ] );

        $this->mergeConfigFrom( __DIR__ . '/../config/clearsale-id.php', 'clearsale-id' );
    }

    private function bootViews()
    {
        $this->loadViewsFrom( __DIR__ . '/../resources/views', 'clearsale-id' );

        View::composer( 'clearsale-id::fingerprint', function ( $view ) {
            /** @var ClearSaleIDService $service */
            $service = $this->app->make( 'clearsale-id' );

            $view->with( 'sessionId', $service->getSessionId() );
            $view->with( 'appId', $service->getAppId() );
        } );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [ 'clearsale-id' ];
    }
}

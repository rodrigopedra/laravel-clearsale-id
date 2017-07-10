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
                $this->app->make( 'request' ),
                $this->app->make( 'log' ),
                config( 'clearsale-id.environment' ),
                config( 'clearsale-id.entity_code' ),
                config( 'clearsale-id.appid' ),
                config( 'clearsale-id.debug' )
            );
        } );
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

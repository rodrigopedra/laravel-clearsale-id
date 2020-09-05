<?php

namespace RodrigoPedra\LaravelClearSaleID;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class ClearSaleIDServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot(Factory $viewFactory): void
    {
        $this->bootConfig();
        $this->bootViews($viewFactory);
    }

    public function register(): void
    {
        $this->app->singleton(ClearSaleIDService::class, static function (Container $container): ClearSaleIDService {
            $config = $container->make(Repository::class);
            $request = $container->make(Request::class);
            $logger = $container->make(LoggerInterface::class);

            return new ClearSaleIDService(
                $request,
                $logger,
                $config->get('clearsale-id.environment'),
                $config->get('clearsale-id.entity_code'),
                $config->get('clearsale-id.appid'),
                $config->get('clearsale-id.debug')
            );
        });

        $this->app->alias(ClearSaleIDService::class, 'clearsale-id');
    }

    private function bootConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../config/clearsale-id.php' => $this->app->configPath('clearsale-id.php'),
        ]);

        $this->mergeConfigFrom(__DIR__ . '/../config/clearsale-id.php', 'clearsale-id');
    }

    private function bootViews(Factory $viewFactory): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'clearsale-id');

        $viewFactory->composer('clearsale-id::fingerprint', ClearSaleIDViewComposer::class);
    }

    public function provides(): array
    {
        return [
            ClearSaleIDService::class,
            'clearsale-id',
        ];
    }
}

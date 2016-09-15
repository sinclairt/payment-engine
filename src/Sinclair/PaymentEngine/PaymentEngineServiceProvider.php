<?php

namespace Sinclair\PaymentEngine;

use Illuminate\Support\ServiceProvider;
use Sinclair\PaymentEngine\Commands\GenerateTransaction;
use Sinclair\PaymentEngine\Commands\ProcessTransaction;

class PaymentEngineServiceProvider extends ServiceProvider
{

    protected $resources = [ 'charge', 'item', 'transaction', 'plan' ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        foreach ( $this->resources as $resource )
            $this->app[ 'router' ]->bind($resource, function ( $value ) use ( $resource )
            {
                return $this->app(studly_case($resource))
                            ->withTrashed()
                            ->find($value);
            });

        $this->app[ 'router' ]->bind('transaction_item', function ( $value )
        {
            return $this->app('Item')
                        ->withTrashed()
                        ->find($value);
        });

        $this->publishes([
            __DIR__ . '/../../migrations' => base_path('/database/migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/../../config' => config_path(),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../../routes/' => explode('.', app()->version())[ 1 ] < 3 ? app_path('Http') : base_path('routes'),
        ], 'routes');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        foreach ( $this->resources as $resource )
        {
            $this->app->bind('Sinclair\PaymentEngine\Contracts\\' . studly_case($resource), 'Sinclair\PaymentEngine\Models\\' . studly_case($resource));
            $this->app->bind(studly_case($resource), 'Sinclair\PaymentEngine\Contracts\\' . studly_case($resource));

            $this->app->bind('Sinclair\PaymentEngine\Contracts\\' . studly_case($resource) . 'Repository', 'Sinclair\PaymentEngine\Repositories\\' . studly_case($resource) . 'Repository');
            $this->app->bind(studly_case($resource) . 'Repository', 'Sinclair\PaymentEngine\Contracts\\' . studly_case($resource) . 'Repository');
        }

        $this->app->bind('Sinclair\PaymentEngine\Contracts\Engine', 'Sinclair\PaymentEngine\Engine');
        $this->app->bind('PaymentEngine', 'Sinclair\PaymentEngine\Contracts\Engine');

        $this->app->bind('Sinclair\PaymentEngine\Contracts\Factory', 'Sinclair\PaymentEngine\Gateways\Factory');
        $this->app->bind('PaymentEngineFactory', 'Sinclair\PaymentEngine\Contracts\Factory');

        $this->app[ 'command.transaction.generate' ] = $this->app->share(
            function ()
            {
                return new GenerateTransaction(app('PaymentEngine'), app('PlanRepository'));
            }
        );

        $this->app[ 'command.transaction.process' ] = $this->app->share(
            function ()
            {
                return new ProcessTransaction(app('PaymentEngine'), app('TransactionRepository'));
            }
        );

        $this->commands('command.transaction.generate', 'command.transaction.process');
    }
}
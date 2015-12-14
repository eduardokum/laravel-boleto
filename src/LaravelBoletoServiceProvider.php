<?php
namespace Eduardokum\LaravelBoleto;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class LaravelBoletoServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {

        require __DIR__ . '/../vendor/autoload.php';

//        $this->app->group(['namespace' => 'League\Skeleton\Http\Controllers'], function($router)
//        {
//            require __DIR__.'/Http/routes.php';
//        });

        // use this if your package needs a config file
        // $this->publishes([
        //         __DIR__.'/config/config.php' => config_path('skeleton.php'),
        // ]);

        // use the vendor configuration file as fallback
        // $this->mergeConfigFrom(
        //     __DIR__.'/config/config.php', 'skeleton'
        // );
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->bind('Eduardokum\LaravelBoleto\Contracts\Banco\Bb','Eduardokum\LaravelBoleto\Banco\Bb');
        $this->app->bind('Eduardokum\LaravelBoleto\Contracts\Banco\Bradesco','Eduardokum\LaravelBoleto\Banco\Bradesco');
        $this->app->bind('Eduardokum\LaravelBoleto\Contracts\Banco\Caixa','Eduardokum\LaravelBoleto\Banco\Caixa');
        $this->app->bind('Eduardokum\LaravelBoleto\Contracts\Banco\Hsbc','Eduardokum\LaravelBoleto\Banco\Hsbc');
        $this->app->bind('Eduardokum\LaravelBoleto\Contracts\Banco\Itau','Eduardokum\LaravelBoleto\Banco\Itau');
        $this->app->bind('Eduardokum\LaravelBoleto\Contracts\Banco\Santander','Eduardokum\LaravelBoleto\Banco\Santander');
        $this->app->bind('Eduardokum\LaravelBoleto\Contracts\Render\Pdf','Eduardokum\LaravelBoleto\Render\Pdf');

//        $this->app->bind('skeleton',function($app){
//            return new Skeleton($app);
//        });

        // use this if your package has a config file
        // config([
        //         'config/skeleton.php',
        // ]);
    }
}
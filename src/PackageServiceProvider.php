<?php

namespace Shafiulnaeem\CrudOperation;

use Illuminate\Support\ServiceProvider;
use Shafiulnaeem\CrudOperation\Console\CreateCrud;
use Illuminate\Routing\Router;

class PackageServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // load command
        $this->command();
        //load route
        $this->routeRegister();
    }
    private function routeRegister()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/route.php');
    }
    private function command(){
        // Register the command if we are using the application via the CLI
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateCrud::class,
            ]);
        }
    }
}

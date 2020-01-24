<?php

namespace Laravel\DataTables;

use Illuminate\Support\ServiceProvider;

class LaravelDataTablesServiceProvider extends ServiceProvider
{
    //TODO: ask the user for the preset (react/vue/angular)
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->registerConsoleCommands();
        }
        $this->registerPublishables();
    }

    public function registerPublishables()
    {
        $publishablePath = __DIR__ . '/publishable';
        $this->publishes([
            $publishablePath . '/js' => resource_path('js/laravel-datatables/components'),
        ], 'datatable');
    }

    protected function registerConsoleCommands()
    {
        $this->commands(\Laravel\DataTables\Commands\RegisterServiceDataTableCommand::class);
    }
}

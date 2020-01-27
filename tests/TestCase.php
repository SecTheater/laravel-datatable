<?php
namespace Laravel\DataTables\Tests;

use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\DataTables\LaravelDataTablesServiceProvider;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    public function setUp(): void
    {

        parent::setUp();

        $this->setUpDatabase();
        $this->withFactories(__DIR__ . '/factories');

    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * @param $app
     */
    protected function getPackageProviders($app)
    {
        return [
            LaravelDataTablesServiceProvider::class,
        ];
    }

    protected function setUpDatabase()
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

        });
    }
}

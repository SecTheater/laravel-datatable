<?php
namespace Laravel\DataTables\Tests;

use Faker\Factory;
use Illuminate\Database\Schema\Blueprint;
use Laravel\DataTables\Tests\Models\User;
use Orchestra\Testbench\TestCase as Orchestra;
use Laravel\DataTables\LaravelDataTablesServiceProvider;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        $pathToFactories = realpath(dirname(__DIR__) . '/tests');

        parent::setUp();

        // This overrides the $this->factory that is established in TestBench's setUp method above
        $this->factory = EloquentFactory::construct(Factory::create(), $pathToFactories);
        $this->setUpDatabase();
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
        collect(range(80, 100))->each(function (int $i) {
            User::create([
                'title' => $i,
                'order' => rand(),
            ]);
        });
        dd('here');
    }
}

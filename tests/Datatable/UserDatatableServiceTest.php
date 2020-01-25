<?php
namespace Laravel\DataTables\Tests\Datatable;

use Faker\Factory;
use App\Console\Kernel;
use Laravel\DataTables\Tests\Models\User;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;

class UserDatatableServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = base_path('bootstrap/app.php');

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /** @test */
    public function it_should_assert_true_is_true()
    {
        $user = factory(\Laravel\DataTables\Tests\Models\User::class)->create();
        dd($user);
        $this->assertTrue(true);
    }

    public function setUp(): void
    {
        parent::setUp();
        $pathToFactories = realpath(dirname(__DIR__) . '../Factories');

        $this->factory = EloquentFactory::construct(Factory::create(), $pathToFactories);

    }
}

<?php
namespace Laravel\DataTables\Tests\DataTable;

use Faker\Factory;
use Illuminate\Contracts\Console\Kernel;
use Laravel\DataTables\Tests\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserDatatableServiceTest extends \Tests\TestCase
{
    use RefreshDatabase;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require_once __DIR__ . '/../../../../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /** @test */
    public function it_should_assert_true_is_true()
    {
        $user = factory(User::class)->create();
        dd($user);
        $this->assertTrue(true);
    }
}

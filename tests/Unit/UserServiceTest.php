<?php
namespace Tests\Unit;

use Illuminate\Support\Facades\Schema;
use Laravel\DataTables\Tests\TestCase;
use Laravel\DataTables\Tests\Models\User;
use Laravel\DataTables\Services\BaseDataTableService;
use Laravel\DataTables\Tests\Services\UserDataTableService;
use Laravel\DataTables\Exceptions\InvalidColumnSearchException;
use Laravel\DataTables\Exceptions\EloquentBuilderWasSetToNullException;

class UserServiceTest extends TestCase
{
    /** @test */
    public function it_should_allow_to_add_a_macroable_method_to_the_base_class()
    {
        BaseDataTableService::macro('sum', function (...$values) {
            return array_sum($values);
        });
        $this->assertEquals(6, $this->userDataTableService->sum(1, 2, 3));
    }

    /** @test */
    public function it_should_build_the_search_query_and_return_an_instance_of_builder_with_the_sql_appended()
    {
        request()->merge([
            'column' => 'id',
            'operator' => 'equals',
            'value' => 'some-dummy-data',
        ]);

        $reflectionMethod = new \ReflectionMethod($this->userDataTableService, 'buildSearchQuery');

        $reflectionMethod->setAccessible(true);

        $this->assertEquals(
            "select * from \"users\" where \"id\" = ?",
            $reflectionMethod->invoke(
                $this->userDataTableService,
                $this->userDataTableService->builder(), request()
            )
                ->toSql()
        );

    }

    /** @test */
    public function it_should_format_query_to_a_format_that_builder_understands()
    {
        $reflectionMethod = new \ReflectionMethod($this->userDataTableService, 'resolveQueryParts');
        $reflectionMethod->setAccessible(true);

        $this->assertEquals(['operator' => '=', 'value' => $value = 'something'], $reflectionMethod->invoke($this->userDataTableService, 'equals', $value));

    }

    /** @test */
    public function it_should_get_3_records_oredered_by_id()
    {
        factory(User::class, 6)->create();
        request()->merge([
            'limit' => 3,
        ]);
        $this->assertEquals(6, $this->userDataTableService->getRecords(request(), function ($builder) {
            $builder->orderBy('id', 'desc');

            return $builder;
        })->first()->id);

    }

    /** @test */
    public function it_should_get_columns_without_primary_key()
    {
        $this->assertArrayNotHasKey('id', $this->userDataTableService->getColumnsWithoutPrimaryKey(Schema::getColumnListing('users')));
    }

    /** @test */
    public function it_should_get_only_3_records()
    {
        factory(User::class, 6)->create();
        request()->merge([
            'limit' => 3,
        ]);
        $this->assertCount(3, $this->userDataTableService->getRecords(request()));
    }

    /** @test */
    public function it_should_get_only_selectable_columns_from_sql()
    {
        // since selectable columns don't contain password, and since laravel will interpret any value that doesn't exist at the attributes to null, we can assert against that.
        factory(User::class)->create();
        $this->assertNull($this->userDataTableService->getRecords()->first()->password);
    }

    /** @test */
    public function it_should_get_records_with_searched_value_only()
    {
        factory(User::class, 3)->create();
        request()->merge([
            'limit' => 1,
            'column' => 'id',
            'operator' => 'equals',
            'value' => 2,
        ]);
        $this->assertEquals(2, $this->userDataTableService->getRecords(request())->first()->id);
    }

    /** @test */
    public function it_should_get_the_current_model_instance_that_is_set_to()
    {
        $this->assertInstanceOf(User::class, $this->userDataTableService->getModel());
    }

    /** @test */
    public function it_should_has_a_query_method()
    {
        $this->assertTrue(method_exists($this->userDataTableService, 'query'));
    }

    /** @test */
    public function it_should_retrieve_custom_column_names_as_an_empty_array_when_not_loaded_from_the_model()
    {
        $this->assertEquals([], $this->userDataTableService->getCustomColumnNames());
    }

    /** @test */
    public function it_should_return_all_columns_except_for_hidden_fields_as_displayable()
    {
        $user = new User;
        $displayables = $this->userDataTableService->getDisplayableColumns();
        $result = Schema::getColumnListing('users');
        $this->assertTrue(
            empty(array_diff_key($displayables, $result)) && array_values($user->getHidden()) === array_values(array_diff_key($result, $displayables))
        );
    }

    /** @test */
    public function it_should_return_response_in_array_format()
    {
        $user = new User;
        $response = $this->userDataTableService->response();
        $this->assertEquals('users', $response['table']);
        $this->assertEquals([], $response['custom_columns']);
        $this->assertEquals(['email', 'name'], $response['updatable']);
        $this->assertCount(0, $response['records']);
        $this->assertEquals([
            'creatable' => false,
            'deletable' => false,
            'updatable' => false,
        ], $response['allow']);

    }

    /** @test */
    public function it_should_return_true_when_request_parameters_contain_the_column_operator_and_value()
    {
        request()->merge([
            'column' => 'somewhere',
            'operator' => 'equals',
            'value' => 'some-dummy-data',
        ]);
        $reflectionMethod = new \ReflectionMethod($this->userDataTableService, 'hasSearchQuery');
        $reflectionMethod->setAccessible(true);

        $this->assertTrue($reflectionMethod->invoke($this->userDataTableService, request()));
    }

    /** @test */
    public function it_should_return_updatable_columns_that_is_specified_at_user_model()
    {
        $updatables = $this->userDataTableService->getUpdatableColumns();

        $this->assertEquals(['email', 'name'], $updatables);
    }

    /** @test */
    public function it_should_return_users_as_a_table()
    {
        $this->assertEquals('users', $this->userDataTableService->getTable());
    }

    /** @test */
    public function it_should_throw_an_exception_when_not_returning_the_builder_when_using_callback()
    {
        $this->expectException(EloquentBuilderWasSetToNullException::class);
        $this->userDataTableService->getRecords(request(), function ($builder) {
            $builder->orderBy('id', 'desc');

        });

    }

    /** @test */
    public function it_should_throw_an_exception_when_searching_with_a_column_that_doesnt_exist_at_displayable_array()
    {
        request()->merge([
            'column' => 'password',
            'operator' => 'equals',
            'value' => 'some-dummy-data',
        ]);

        $reflectionMethod = new \ReflectionMethod($this->userDataTableService, 'buildSearchQuery');

        $reflectionMethod->setAccessible(true);
        $this->expectException(InvalidColumnSearchException::class);
        $reflectionMethod->invoke(
            $this->userDataTableService,
            $this->userDataTableService->builder(), request()
        );

    }

    public function setUp(): void
    {
        $this->userDataTableService = app(UserDataTableService::class);

        parent::setUp();
    }
}

<?php

namespace Fouladgar\EloquentBuilder\Tests;

use BadMethodCallException;
use Fouladgar\EloquentBuilder\Exceptions\FilterException;
use Fouladgar\EloquentBuilder\Tests\Models\User;

class DateTimeFilterTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        User::factory()->create(['birth_date' => '2016-01-01', 'authenticated_at' => '2016-01-01 12:34:56']);
        User::factory()->create(['birth_date' => '2016-12-30', 'authenticated_at' => '2017-01-01 12:34:56']);
        User::factory()->create(['birth_date' => '2017-01-01', 'authenticated_at' => '2018-01-01 12:34:56']);
        User::factory()->create(['birth_date' => '2017-12-30', 'authenticated_at' => '2018-02-01 12:34:56']);

        User::factory()->create(['birth_date' => '2018-01-01 10:49:20', 'authenticated_at' => '2018-03-01 12:34:56']);
        User::factory()->create(['birth_date' => '2018-01-02', 'authenticated_at' => '2018-04-01 12:34:56']);
    }

    /**
     * @test
     */
    public function it_can_filter_between_date_time_by_using_array(): void
    {
        $users = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['birth_date' => ['2016-01-01', '2017-01-01']])
            ->thenApply()
            ->get();

        $this->assertEquals(3, $users->count());
    }

    /**
     * @test
     */
    public function it_can_filter_between_date_time_by_using_convention(): void
    {
        $users = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['birth_date' => 'between:2016-01-01,2017-01-01'])
            ->thenApply()
            ->get();

        $this->assertEquals(3, $users->count());
    }

    /**
     * @test
     */
    public function it_can_filter_between_date_time_by_using_simple_value_separated_by_comma(): void
    {
        $users = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['birth_date' => '2016-01-01,2017-01-01'])
            ->thenApply()
            ->get();

        $this->assertEquals(3, $users->count());
    }

    /**
     * @test
     */
    public function it_can_filter_before_a_date_time(): void
    {
        $beforeDateTimes = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['birth_date' => 'before:2018-01-01 10:49:20'])
            ->thenApply()
            ->get(['id', 'birth_date']);

        $this->assertEquals(4, $beforeDateTimes->count());
    }

    /**
     * @test
     */
    public function it_can_filter_before_or_equals_a_date_time(): void
    {
        $beforeOrEqualDateTimes = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['birth_date' => 'before_or_equal:2018-01-01 10:49:20'])
            ->thenApply()
            ->get(['id', 'birth_date']);

        $this->assertEquals(5, $beforeOrEqualDateTimes->count());
    }

    /**
     * @test
     */
    public function it_can_filter_after_a_date_time(): void
    {
        $afterDateTimes = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['birth_date' => 'after:2018-01-01 10:49:20'])
            ->thenApply()
            ->get(['id', 'birth_date']);

        $this->assertEquals(1, $afterDateTimes->count());
    }

    /**
     * @test
     */
    public function it_can_filter_after_or_equal_a_date_time(): void
    {
        $afterDateTimes = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['birth_date' => 'after_or_equal:2017-12-30'])
            ->thenApply()
            ->get(['id', 'birth_date']);

        $this->assertEquals(3, $afterDateTimes->count());
    }

    /**
     * @test
     */
    public function it_can_filter_same_or_equal_a_date_time(): void
    {
        $defaultDateTime = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['birth_date' => '2017-12-30'])
            ->thenApply()
            ->get(['id', 'birth_date']);

        $equalDateTime = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['birth_date' => 'equals:2017-12-30'])
            ->thenApply()
            ->get(['id', 'birth_date']);

        $sameDateTime = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['birth_date' => 'same:2017-12-30'])
            ->thenApply()
            ->get(['id', 'birth_date']);

        $this->assertEquals(1, $defaultDateTime->count());
        $this->assertEquals(1, $sameDateTime->count());
        $this->assertEquals(1, $equalDateTime->count());
    }

    /**
     * @test
     */
    public function it_can_filter_same_or_equal_a_date(): void
    {
        $defaultDateTime = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['authenticated_at' => '2018-02-01'])
            ->thenApply()
            ->get(['id', 'birth_date']);

        $equalDateTime = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['authenticated_at' => 'equals:2018-02-01'])
            ->thenApply()
            ->get(['id', 'birth_date']);

        $sameDateTime = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['authenticated_at' => 'same:2018-02-01'])
            ->thenApply()
            ->get(['id', 'birth_date']);

        $this->assertEquals(1, $defaultDateTime->count());
        $this->assertEquals(1, $sameDateTime->count());
        $this->assertEquals(1, $equalDateTime->count());
    }

    /**
     * @test
     */
    public function it_can_throw_validate_convention_exception_if_date_is_not_a_valid_date(): void
    {
        $this->expectException(FilterException::class);
        $this->eloquentBuilder
            ->model(User::class)
            ->filters(['birth_date' => 'after_or_equal:2017-30-30'])
            ->thenApply()
            ->get(['id', 'birth_date']);
    }

    /**
     * @test
     */
    public function it_can_throw_validate_convention_exception_if_dates_are_not_a_valid_date(): void
    {
        $this->expectException(FilterException::class);
        $this->eloquentBuilder
            ->model(User::class)
            ->filters(['birth_date' => 'between:2016-01-01, 2017-AA1-01'])
            ->thenApply()
            ->get(['id', 'birth_date']);

        $this->expectException(FilterException::class);
        $this->eloquentBuilder
            ->model(User::class)
            ->filters(['birth_date' => ['2016-01-01', '2017-BB-01']])
            ->thenApply()
            ->get(['id', 'birth_date']);

        $this->expectException(FilterException::class);
        $this->eloquentBuilder
            ->model(User::class)
            ->filters(['birth_date' => '2016-01-CC,2017-01-01'])
            ->thenApply()
            ->get(['id', 'birth_date']);
    }

    /**
     * @test
     */
    public function it_can_throw_bad_method_call_exception_if_a_filter_date_does_not_exist(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->eloquentBuilder
            ->model(User::class)
            ->filters(['birth_date' => 'does_not_exists_method:2017-12-30'])
            ->thenApply()
            ->get(['id', 'birth_date']);
    }
}

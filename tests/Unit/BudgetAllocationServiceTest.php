<?php

use App\Models\BudgetAllocation;
use App\Models\Category;
use App\Repositories\Contracts\BudgetAllocationRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Services\Budgeting\BudgetAllocationService;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

pest()->extend(TestCase::class)->in(__FILE__);

it('replaces a monthly allocation when it remains at or above spent', function () {
    $category = new Category(['organization_id' => 7]);
    $category->id = 12;
    $allocation = new BudgetAllocation([
        'category_id' => 12,
        'month' => '2026-09-01',
        'allocated_amount' => 5000,
    ]);

    $categories = Mockery::mock(CategoryRepositoryInterface::class);
    $allocations = Mockery::mock(BudgetAllocationRepositoryInterface::class);
    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);

    $categories->shouldReceive('findOrFail')->with(12)->andReturn($category);
    $expenses->shouldReceive('totalForCategoryInMonth')->with(12, Mockery::type(Carbon::class))->andReturn(5000.0);
    $allocations->shouldReceive('setAllocation')->with(7, 12, Mockery::type(Carbon::class), 5000.0)->andReturn($allocation);

    $service = new BudgetAllocationService($allocations, $categories, $expenses);

    $result = $service->replace(7, 12, '2026-09-15', 5000.0);

    expect($result)->toBe($allocation);
});

it('rejects a replacement below the category spending', function () {
    $category = new Category(['organization_id' => 7]);
    $category->id = 12;

    $categories = Mockery::mock(CategoryRepositoryInterface::class);
    $allocations = Mockery::mock(BudgetAllocationRepositoryInterface::class);
    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);

    $categories->shouldReceive('findOrFail')->with(12)->andReturn($category);
    $expenses->shouldReceive('totalForCategoryInMonth')->with(12, Mockery::type(Carbon::class))->andReturn(5000.0);
    $allocations->shouldNotReceive('setAllocation');

    $service = new BudgetAllocationService($allocations, $categories, $expenses);

    expect(fn () => $service->replace(7, 12, '2026-09-15', 4999.99))
        ->toThrow(ValidationException::class, 'The allocation cannot be lower than the amount already spent');
});

it('keeps top-ups additive', function () {
    $category = new Category(['organization_id' => 7]);
    $category->id = 12;
    $existing = new BudgetAllocation([
        'category_id' => 12,
        'month' => '2026-09-01',
        'allocated_amount' => 5000,
    ]);
    $updated = new BudgetAllocation([
        'category_id' => 12,
        'month' => '2026-09-01',
        'allocated_amount' => 7200,
    ]);

    $categories = Mockery::mock(CategoryRepositoryInterface::class);
    $allocations = Mockery::mock(BudgetAllocationRepositoryInterface::class);
    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);

    $categories->shouldReceive('findOrFail')->with(12)->andReturn($category);
    $allocations->shouldReceive('findForCategoryAndMonth')->with(12, Mockery::type(Carbon::class))->andReturn($existing);
    $allocations->shouldReceive('setAllocation')->with(7, 12, Mockery::type(Carbon::class), 7200.0)->andReturn($updated);

    $service = new BudgetAllocationService($allocations, $categories, $expenses);

    $result = $service->allocate(7, 12, '2026-09-15', 2200.0);

    expect($result)->toBe($updated);
});

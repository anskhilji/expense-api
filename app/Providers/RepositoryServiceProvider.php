<?php

namespace App\Providers;

use App\Repositories\Contracts\BudgetAllocationRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Repositories\Contracts\IncomeRepositoryInterface;
use App\Repositories\Contracts\InvitationRepositoryInterface;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\BudgetAllocationRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\ExpenseRepository;
use App\Repositories\Eloquent\IncomeRepository;
use App\Repositories\Eloquent\InvitationRepository;
use App\Repositories\Eloquent\OrganizationRepository;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds every repository interface to its Eloquent implementation. This is
 * the one place that knows which concrete class backs each contract —
 * controllers and services only ever depend on the interfaces, so swapping
 * an implementation (or mocking one in a test) never touches them.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(OrganizationRepositoryInterface::class, OrganizationRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(IncomeRepositoryInterface::class, IncomeRepository::class);
        $this->app->bind(BudgetAllocationRepositoryInterface::class, BudgetAllocationRepository::class);
        $this->app->bind(ExpenseRepositoryInterface::class, ExpenseRepository::class);
        $this->app->bind(InvitationRepositoryInterface::class, InvitationRepository::class);
    }
}

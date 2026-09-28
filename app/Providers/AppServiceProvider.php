<?php

namespace App\Providers;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::before(fn (User $user, $ability) => $user->hasRole('Super Admin') ? true : null);
        Gate::define('manage-job', fn (User $u, JobListing $job) => $u->can('manage platform') || ($u->can('create jobs') && $u->id === $job->employer_id));
    }
}

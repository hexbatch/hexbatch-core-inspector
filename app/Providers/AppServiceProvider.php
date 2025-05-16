<?php

namespace App\Providers;

use App\Helpers\TestOwners\OwnerFromUser;
use App\Models\TestActionDatum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::automaticallyEagerLoadRelationships();
        OwnerFromUser::registerOwner();
        TestActionDatum::registerAction();
    }
}

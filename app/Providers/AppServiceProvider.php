<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Permission::firstOrCreate(['name' => 'c_grad']);
        Permission::firstOrCreate(['name' => 'c_posgrad']);
        Permission::firstOrCreate(['name' => 'c_pesquisa']);
        Permission::firstOrCreate(['name' => 'c_cultext']);
        Permission::firstOrCreate(['name' => 'c_inclusao']);
        Permission::firstOrCreate(['name' => 'c_internacional']);
        Permission::firstOrCreate(['name' => 'gmg']);
        Permission::firstOrCreate(['name' => 'gaa']);
        
        $role = Role::firstOrCreate(['name' => 'departamentos']);
        $role->givePermissionTo(['gmg', 'gaa']);
    }
}

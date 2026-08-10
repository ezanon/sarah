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
        $guard = 'web'; // Força o guard correto

        Permission::firstOrCreate(['name' => 'c_grad', 'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'c_posgrad', 'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'c_pesquisa', 'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'c_cultext', 'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'c_inclusao', 'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'c_internacional', 'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'gmg', 'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'gaa', 'guard_name' => $guard]);

        $roleDeptos = Role::firstOrCreate(['name' => 'departamentos', 'guard_name' => $guard]);
        $roleDeptos->givePermissionTo(['gmg', 'gaa']);

        $roleTotal = Role::firstOrCreate(['name' => 'controle_total', 'guard_name' => $guard]);
        $roleTotal->syncPermissions(Permission::where('guard_name', $guard)->get());
    }
}

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authorization Model
    |--------------------------------------------------------------------------
    */

    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role' => Spatie\Permission\Models\Role::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Model
    |--------------------------------------------------------------------------
    */

    'register_permission_check_method' => true,

    /*
    |--------------------------------------------------------------------------
    | Authorization Table
    |--------------------------------------------------------------------------
    */

    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Column Names
    |--------------------------------------------------------------------------
    */

    'column_names' => [
        'team_pivot_key' => 'team_id',
        'permission_pivot_key' => 'permission_id',
        'role_pivot_key' => 'role_id',
        'role_model_key' => 'role_id',
        'permission_model_key' => 'permission_id',
        'model_key' => 'model_id',
        'model_name' => 'model_type',
        'table_name' => 'model',
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Prefix
    |--------------------------------------------------------------------------
    */

    'prefix' => env('SPATIE_PERMISSION_PREFIX', 'spatie.permission.cache'),

    /*
    |--------------------------------------------------------------------------
    | Cache Store
    |--------------------------------------------------------------------------
    */

    'cache_store' => env('SPATIE_PERMISSION_CACHE_STORE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Cache Key
    |--------------------------------------------------------------------------
    */

    'cache_key' => 'spatie.permission.cache',

    /*
    |--------------------------------------------------------------------------
    | Cache Duration
    |--------------------------------------------------------------------------
    */

    'cache_ttl' => null,

    /*
    |--------------------------------------------------------------------------
    | Display Permission in Alias
    |--------------------------------------------------------------------------
    */

    'display_permission_in_alias' => false,

    /*
    |--------------------------------------------------------------------------
    | Display Role in Alias
    |--------------------------------------------------------------------------
    */

    'display_role_in_alias' => false,

];

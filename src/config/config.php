<?php

return [
    'goods' => [
        'model' => \MrsJoker\Trade\Goods\Models\Good::class,
        'user' => \App\User::class,
        'category' => 'trade.category.goods_category',
        'table_goods_category' => 'goods_category',
        'goods_pivot_key' => 'goods_id',
        'category_pivot_key' => 'category_id',
        'cache_prefix' => 'trade_goods_cache_',
    ],
    'rbac' => [
        'cache_prefix' => 'trade_rbac_cache_',
        'user' => \App\User::class,
        'role' => \MrsJoker\Trade\Rbac\Models\RbacRole::class,
        'permission' => \MrsJoker\Trade\Rbac\Models\RbacPermission::class,
        'table_role_user' => 'rbac_role_user',
        'table_permission_role' => 'rbac_permission_role',
        'user_pivot_key' => 'user_id',
        'role_pivot_key' => 'role_id',
        'permission_pivot_key' => 'permission_id',
    ],
    'category' => [
        'menus' => [
            'cache_prefix' => 'trade_category_menus_cache_',
            'model' => \App\Models\Menu::class,
            'user' => \App\User::class,
        ],
        'goods_category' => [
            'cache_prefix' => 'trade_category_goods_category_cache_',
            'model' => \App\Models\GoodsCategory::class,
            'user' => \App\User::class,
        ],
    ],

];
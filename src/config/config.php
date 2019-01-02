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
    //权限
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
    //树状分类
    'category' => [
        'menus' => [
            'cache_prefix' => 'trade_category_menus_cache_',
            'model' => \App\Models\Menu::class,
            'user' => \App\User::class,
            'admin_menu_parent' => 5,
        ],
        'tags' => [
            'cache_prefix' => 'trade_category_tags_cache_',
            'model' => \App\Models\TagRelation::class,
            'user' => \App\User::class,
        ],
        'goods_category' => [
            'cache_prefix' => 'trade_category_goods_category_cache_',
            'model' => \App\Models\GoodsCategory::class,
            'user' => \App\User::class,
        ],
    ],
    //标签
    'tag' => [
        'cache_prefix' => 'trade_tag_cache_',
        'model' => \App\Models\TagQuantification::class,
        'node' => \App\Models\TagRelation::class,
        'user' => \App\User::class,
        'scene' => [
            'sogal' => '索菲亚',
//            'tencent' => '腾讯',
//            'ali' => '阿里',
        ],
        'directional' => [
            'region' => '地域',
            'population_attribute_sex' => '人口属性-性别',
            'population_attribute_age' => '人口属性-年龄',
            'population_attribute_education' => '人口属性-学历',
            'population_attribute_marriage' => '人口属性-婚恋情况',
            'interest_behavior_interest' => '兴趣行为-兴趣',
            'interest_behavior_behavior' => '兴趣行为-行为',
            'equipment' => '手机设备',
            're-marketing' => '再营销/排除营销',
        ],
        'rules' => [
            'character_equal' => '全等字符',
            'character_like' => '模糊匹配字符',
            'character_collect' => '字符结果集',
            'number_range' => '数字范围',
        ],
    ],

];
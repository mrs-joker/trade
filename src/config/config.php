<?php

return [
    'goods' => [
        'model' => App\Tree::class,
    ],
    'rbac' => [
        'user' => '\App\User',
        'role' => '\MrsJoker\Trade\Rbac\Models\RbacRole',
        'permission' => '\MrsJoker\Trade\Rbac\Models\RbacPermission',
        'table_role_user' => 'rbac_role_user',
        'user_foreign_pivot_key' => 'user_id',
        'role_related_pivot_key' => 'role_id',

        'table_permission_role' => 'rbac_permission_role',
    ],

];

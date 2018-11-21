<?php

namespace MrsJoker\Trade\Rbac\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use MrsJoker\Trade\Rbac\Contracts\RoleInterface;
use MrsJoker\Trade\Rbac\Traits\RoleTrait;

class RbacRole  extends Model
{
    use SoftDeletes;

    /**
     * Many-to-Many relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(Config::get('trade.rbac.user'), Config::get('trade.rbac.table_role_user'), Config::get('trade.rbac.role_pivot_key'), Config::get('trade.rbac.user_pivot_key'));
    }
    /**
     * Many-to-Many relations with the permission model.
     * Named "perms" for backwards compatibility. Also because "perms" is short and sweet.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function perms()
    {
//        return $this->belongsToMany('MrsJoker\Trade\Rbac\Models\RbacPermission', 'rbac_permission_role', 'role_id', 'permission_id');
        return $this->belongsToMany(Config::get('trade.rbac.permission'), Config::get('trade.rbac.table_permission_role'), Config::get('trade.rbac.role_pivot_key'), Config::get('trade.rbac.permission_pivot_key'));
    }
}

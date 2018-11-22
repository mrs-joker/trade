<?php
namespace MrsJoker\Trade\Rbac\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\SoftDeletes;


class RbacPermission extends Model
{
    use SoftDeletes;
    /**
     * Many-to-Many relations with role model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Config::get('trade.rbac.role'), Config::get('trade.rbac.table_permission_role'), Config::get('trade.rbac.permission_pivot_key'), Config::get('trade.rbac.role_pivot_key'));
    }


}

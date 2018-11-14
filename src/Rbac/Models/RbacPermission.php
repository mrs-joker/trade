<?php
namespace MrsJoker\Trade\Rbac\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MrsJoker\Trade\Rbac\Contracts\PermissionInterface;

class RbacPermission extends Model implements PermissionInterface
{
    use SoftDeletes;
    /**
     * Many-to-Many relations with role model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Config::get('entrust.role'), Config::get('entrust.permission_role_table'), Config::get('entrust.permission_foreign_key'), Config::get('entrust.role_foreign_key'));
    }
}

<?php

namespace MrsJoker\Trade\Rbac\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MrsJoker\Trade\Rbac\Contracts\RoleInterface;
use MrsJoker\Trade\Rbac\Traits\RoleTrait;

class RbacRole  extends Model implements RoleInterface
{
    use SoftDeletes;
    use RoleTrait;

    /**
     * Many-to-Many relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(Config::get('auth.providers.users.model'), Config::get('entrust.role_user_table'), Config::get('entrust.role_foreign_key'), Config::get('entrust.user_foreign_key'));
    }
    /**
     * Many-to-Many relations with the permission model.
     * Named "perms" for backwards compatibility. Also because "perms" is short and sweet.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function perms()
    {
        return $this->belongsToMany(Config::get('entrust.permission'), Config::get('entrust.permission_role_table'), Config::get('entrust.role_foreign_key'), Config::get('entrust.permission_foreign_key'));
    }
}

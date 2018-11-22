<?php

namespace MrsJoker\Trade\Goods\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

class Good  extends Model
{
    use SoftDeletes;
    /**
     * Many-to-Many relations with the category model.
     * Named "perms" for backwards compatibility. Also because "perms" is short and sweet.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categorys()
    {
        return $this->belongsToMany(Config::get('trade.category.goods_category.model'), Config::get('trade.goods.table_goods_category'), Config::get('trade.goods.goods_pivot_key'), Config::get('trade.goods.category_pivot_key'));
    }
}

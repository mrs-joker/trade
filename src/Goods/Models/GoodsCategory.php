<?php

namespace MrsJoker\Trade\Goods\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

class GoodsCategory extends Model
{
    use SoftDeletes;
    protected $casts = [
        'additional_data' => 'array',
    ];

    /**
     * Many-to-Many relations with the good model.
     * Named "perms" for backwards compatibility. Also because "perms" is short and sweet.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function goods()
    {
        return $this->belongsToMany(Config::get('trade.goods.model'), Config::get('trade.goods.table_goods_category'), Config::get('trade.goods.category_pivot_key'), Config::get('trade.goods.goods_pivot_key'));
    }
}

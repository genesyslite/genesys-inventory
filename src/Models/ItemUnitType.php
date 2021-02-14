<?php

namespace GenesysLite\GenesysInventory\Models;

use Illuminate\Database\Eloquent\Model;

class ItemUnitType extends Model
{
     protected $with = ['unit_type'];
    public $timestamps = false;

    protected $fillable = [
        'description',
        'item_id',
        'unit_type_id',
        'quantity_unit',
        'price1',
        'price2',
        'price3',
        'price_default',
    ];

    public function unit_type() {
        return $this->belongsTo(UnitType::class, 'unit_type_id');
    }

    public function item() {
        return $this->belongsTo(Item::class);
    }

}

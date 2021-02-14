<?php

namespace GenesysLite\GenesysInventory\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
        'establishment_id',
        'description',
    ];


    public function inventory_kardex()
    {
        return $this->hasMany(InventoryKardex::class);
    }
    
}
<?php

namespace GenesysLite\GenesysInventory\Models;
use Illuminate\Database\Eloquent\Model;


class Item extends Model
{
    protected $fillable = [
        'name',
        'second_name',
        'description',
        'item_type_id',
        'internal_id',
        'item_code',
        'item_code_gs1',
        'unit_type_id',
        'currency_type_id',
        'sale_unit_price',
        'has_isc',
        'system_isc_type_id',
        'percentage_isc',
        'suggested_price',

        'sale_affectation_igv_type_id',
        'calculate_quantity',
        'has_igv',

        'stock',
        'stock_min',
        'percentage_of_profit',

        'attributes',
        'has_perception',
        'percentage_perception',
        'image',
        'image_medium',
        'image_small',

        'account_id',
        'amount_plastic_bag_taxes',
        'date_of_due',
        'is_set',
        'sale_unit_price_set',
        'brand_id',
        'category_id',
        'active',
        'web_platform_id',
        'has_plastic_bag_taxes',
        'barcode',
        // 'warehouse_id'
    ];


    public function getAttributesAttribute($value)
    {
        return (is_null($value))?null:json_decode($value);
    }

    public function setAttributesAttribute($value)
    {
        $this->attributes['attributes'] = (is_null($value))?null:json_encode($value);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function item_type()
    {
        return $this->belongsTo(ItemType::class);
    }

    public function unit_type()
    {
        return $this->belongsTo(UnitType::class, 'unit_type_id');
    }

    public function currency_type()
    {
        return $this->belongsTo(CurrencyType::class, 'currency_type_id');
    }

    public function system_isc_type()
    {
        return $this->belongsTo(SystemIscType::class, 'system_isc_type_id');
    }




    public function sale_affectation_igv_type()
    {
        return $this->belongsTo(AffectationIgvType::class, 'sale_affectation_igv_type_id');
    }


    public function scopeWhereTypeUser($query)
    {
        $user = auth()->user();
        return ($user->type == 'seller') ? $this->scopeWhereWarehouse($query) : null;
    }

    public function scopeWhereNotIsSet($query)
    {
        return $query->where('is_set', false);
    }

    public function scopeWhereIsActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeWhereIsSet($query)
    {
        return $query->where('is_set', true);
    }



    public function item_unit_types()
    {
        return $this->hasMany(ItemUnitType::class);
    }



    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeWhereNotService($query)
    {
        return $query->where('unit_type_id','!=', 'ZZ');
    }

    public function scopeWhereFilterValuedKardex($query, $params)
    {

        if($params->establishment_id){

            return $query->with(['document_items'=> function($q) use($params){
                        $q->whereHas('document', function($q) use($params){
                            $q->whereStateTypeAccepted()
                                ->whereTypeUser()
                                ->whereBetween('date_of_issue', [$params->date_start, $params->date_end])
                                ->where('establishment_id', $params->establishment_id);
                        });
                    },
                    'sale_note_items' => function($q) use($params){
                        $q->whereHas('sale_note', function($q) use($params){
                            $q->whereStateTypeAccepted()
                                ->whereNotChanged()
                                ->whereTypeUser()
                                ->whereBetween('date_of_issue', [$params->date_start, $params->date_end])
                                ->where('establishment_id', $params->establishment_id);
                        });
                    }]);

        }

        return $query->with(['document_items'=> function($q) use($params){
                    $q->whereHas('document', function($q) use($params){
                        $q->whereStateTypeAccepted()
                            ->whereTypeUser()
                            ->whereBetween('date_of_issue', [$params->date_start, $params->date_end]);
                    });
                },
                'sale_note_items' => function($q) use($params){
                    $q->whereHas('sale_note', function($q) use($params){
                        $q->whereStateTypeAccepted()
                            ->whereNotChanged()
                            ->whereTypeUser()
                            ->whereBetween('date_of_issue', [$params->date_start, $params->date_end]);
                    });
                }]);
    }

    public function scopeWhereIsNotActive($query)
    {
        return $query->where('active', false);
    }

    public function scopeWhereHasInternalId($query)
    {
        return $query->where('internal_id','!=', null);
    }


}

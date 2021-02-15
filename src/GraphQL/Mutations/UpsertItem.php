<?php

namespace GenesysLite\GenesysInventory\GraphQL\Mutations;

use GenesysLite\GenesysInventory\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class UpsertItem
{
    /**
     * @param null $_
     * @param array<string, mixed> $args
     */
    public $all_attribute_values = [];

    public function __invoke($_, array $args)
    {
        // TODO implement the resolver
        $Item = self::updateOrCreateItem($args);
/*
        $maxProductVariant = 0;
        $product_variant_values = $args['product_variant_values'];
        $extractValues = self::refreshAttributevalues($product_variant_values, $Item);
        //TODO ejecución de matriz
        $data = self::combinations($extractValues);
        Log::info('data');
        Log::info($data);
        Log::info('data');
        $productVariant = null;

        if (count($extractValues) === 1) {
            $valuesAux = [];
            foreach ($data as $values) {
                $valuesAux[] = [$values];
            }
            Log::info('ejecutrando 1 fila');
            foreach ($valuesAux as $values) {
                self::main($values, $Item, $extractValues);
            }
        } else {
            foreach ($data as $values) {
                self::main($values, $Item, $extractValues);
            }
        }
        self::verificarYEliminar($Item, $product_variant_values);

        //eliminar filas

        if (count($extractValues) === 1) {
            $valuesAux = [];
            foreach ($data as $values) {
                $valuesAux[] = [$values];
            }
            Log::info('ejecutrando 1 fila');
            foreach ($valuesAux as $values) {
                self::verificarEliminarFilas($Item, $values);
            }
        } else {
            foreach ($data as $values) {
                self::verificarEliminarFilas($Item, $values);
            }
        }*/
        return $Item;
    }

    public function main($values, $Item, $extractValues)
    {
        Log::info('fila evaluar');
        Log::info($values);
        Log::info('fila evaluar fin');
        //verificar el estado de los atributos valor para determinar si tienen un PV en general
        $attributeProductVariantsAll = [];
        $c_range = 0;
        $c_range = count($values);
        foreach ($values as $value) {
            //obtener id de atributo valor
            $attribute_value_id = self::getAttributeValueId($Item, $value);
            //verificar si tiene products variants (PV)
            $attributeProductVariants = AttValProdVariant::where('product_variant_value_id', $attribute_value_id)->get();
            $attributeProductVariantsAll[] = $attributeProductVariants;
        }
        //evaluar pv repetido
        $pv_select_id = self::obtenerProductVariantId($attributeProductVariantsAll, $c_range);
        self::updateOrCreateValuesAttributeWithProductValue($Item, $values, $pv_select_id);
    }

    public function verificarEliminarFilas2($Item, $value)
    {

        $attribute_value_id = self::getAttributeValueId($Item, $value);
        Log::info($value);
        Log::info('verificando para elminar');
        //verificar si tiene products variants (PV)
        $attributeProductVariants = AttValProdVariant::where('product_variant_value_id', $attribute_value_id)->get();
        foreach ($attributeProductVariants as $attributeProductVariant) {
            $pv_eval = ProductVariant::find($attributeProductVariant->product_variant_id);
            if ($pv_eval->att_val_prod_variants) {
                Log::info('cantidad de values =>' . count($value));
                Log::info('cantidad de cantidad de columnas de fila' . count($pv_eval->att_val_prod_variants));
                if (count($pv_eval->att_val_prod_variants) > count($value)) {
                    Log::info('eliminar');
                    foreach ($pv_eval->att_val_prod_variants as $fileDelete) {
                        AttValProdVariant::destroy($fileDelete->id);
                        Log::info('eliminar AttValProdVariant por motivo de eliminar fila' . $fileDelete->id);
                    }
                    ProductVariant::destroy($attributeProductVariant->product_variant_id);
                    Log::info('eliminar ProductVariant fila' . $attributeProductVariant->product_variant_id);
                }
            }
        }
    }

    public function verificarEliminarFilas($Item, $value)
    {

        Log::info($value);
        Log::info('verificando para elminar');
        //verificar si tiene products variants (PV)
        $pv_all_items = ProductVariant::where('item_id', $Item->id)->get();
        foreach ($pv_all_items as $pv_eval) {
            if ($pv_eval->att_val_prod_variants) {
                Log::info('cantidad de values =>' . count($value));
                Log::info('cantidad de cantidad de columnas de fila' . count($pv_eval->att_val_prod_variants));
                if (count($pv_eval->att_val_prod_variants) !== count($value)) {
                    Log::info('eliminar');
                    foreach ($pv_eval->att_val_prod_variants as $fileDelete) {
                        AttValProdVariant::destroy($fileDelete->id);
                        Log::info('eliminar AttValProdVariant por motivo de eliminar fila' . $fileDelete->id);
                    }
                    ProductVariant::destroy($pv_eval->id);
                    Log::info('eliminar ProductVariant fila' . $pv_eval->id);
                }
            }
        }
    }

    public function verificarEliminarFilasExcedentes($Item, $value)
    {

        Log::info($value);
        Log::info('verificando para elminar');
        //verificar si tiene products variants (PV)
        $pv_all_items = ProductVariant::where('item_id', $Item->id)->get();
        foreach ($pv_all_items as $pv_eval) {

        }
    }

    public function verificarYEliminar($Item, $product_variant_values)
    {

        //Todo verificar si esta siendo usado
        $all_attribute_val_product_variants = AttValProdVariant::whereHas('product_variant', function (Builder $query) use ($Item) {
            $query->where('item_id', $Item->id);
        })->get();
        $c = 0;
        foreach ($all_attribute_val_product_variants as $all_attribute_val_product_variant) {
            foreach ($this->all_attribute_values as $all_attribute_value) {
                if ($all_attribute_val_product_variant->product_variant_value->attribute_value->value === $all_attribute_value) {
                    $c++;
                }
            }
            if ($c === 0) {
                AttValProdVariant::destroy($all_attribute_val_product_variant->id);
                Log::info('eliminar AttValProdVariant por operación backup' . $all_attribute_val_product_variant->id);
            }
            $c = 0;
        }
        //TODO verificando si product Variant tiene attvalpro
        $product_variant = ProductVariant::get();
        foreach ($product_variant as $d_product_variant) {
            if (count($d_product_variant->att_val_prod_variants) === 0) {
                ProductVariant::destroy($d_product_variant->id);
                Log::info('eliminar ProductVariant por operación backup' . $all_attribute_val_product_variant->id);
            }
        }
        $c = 0;
        //todo no se para que es
        $ProductVariants = ProductVariantValue::where('item_id', $Item->id)->get();
        foreach ($ProductVariants as $productVariant) {
            foreach ($product_variant_values as $product_variant_value) {
                $values = $product_variant_value['values'];
                foreach ($values as $value) {
                    $attributeValue = AttributeValue::where('attribute_id', $product_variant_value['attribute_id'])->where('value', $value)->first();
                    $productV = ProductVariantValue::where('item_id', $Item->id)->where('attribute_value_id', $attributeValue->id)->first();
                    if ($productVariant->id === $productV->id) {
                        $c++;
                    }
                }
            }
            if ($c === 0) {
                ProductVariantValue::destroy($productVariant->id);
                Log::info('eliminar ProductVariantValue por operación backup' . $productVariant->id);
            }
            $c = 0;
        }
    }

    public function updateOrCreateValuesAttributeWithProductValue($Item, $values, $pv_select_id)
    {
        Log::info('pv id ' . $pv_select_id);
        $att_val_prod_variants = [];
        $productVariant = null;
        if ($pv_select_id) {
            $productVariant = ProductVariant::find($pv_select_id);
            $att_val_prod_variants = $productVariant->att_val_prod_variants;
        }
        //verificar si el que el pv es el que tiene los dos campos
        $isCreate = false;
        Log::info('total de atributos de pv ' . count($att_val_prod_variants));
        Log::info('total de valores ' . count($values));
        if (count($att_val_prod_variants) >= count($values)) {
            if (count($att_val_prod_variants) === count($values)) {
                //evaluar si el pv es el que usa esos valores
                $isCreate = true;
                //no hagas nada
                Log::info('pv completara su tope, se creara');
                /*$productVariant = ProductVariant::create([
                    'item_id' => $Item->id,
                ]);*/
            } else {
                //eliminar excedente column
                self::deleteColumnasExcendentes($att_val_prod_variants, $values);
            }
        }
        if  (!$isCreate) {
            if (!$productVariant) {
                //crear pv
                $productVariant = ProductVariant::create([
                    'item_id' => $Item->id,
                ]);
            }
            foreach ($values as $value) {
                //obtener id de atributo valor
                $attribute_value_id = self::getAttributeValueId($Item, $value);
                AttValProdVariant::updateOrCreate(
                    [
                        'product_variant_id' => $productVariant->id,
                        'product_variant_value_id' => $attribute_value_id,
                    ],
                    [
                        'product_variant_id' => $productVariant->id,
                        'product_variant_value_id' => $attribute_value_id,
                    ]);
            }
        }
    }
    public function eliminarExcedentes($values, $pv_select_id)
    {
        $att_val_prod_variants = [];
        $productVariant = null;
        if ($pv_select_id) {
            $productVariant = ProductVariant::find($pv_select_id);
            $att_val_prod_variants = $productVariant->att_val_prod_variants;
        }
        if (count($att_val_prod_variants) > count($values)) {
            self::deleteColumnasExcendentes($att_val_prod_variants, $values);
        }
    }

    public function deleteColumnasExcendentes($att_val_prod_variants, $values)
    {
        foreach ($att_val_prod_variants as $att_val_prod_variant) {
            $c = 0;
            foreach ($values as $value) {
                $value_ = $att_val_prod_variant->product_variant_value->value;
                if ($value === $value_) {
                    $c++;
                }
            }
            if ($c === 0) {
                AttValProdVariant::destroy($att_val_prod_variant->id);
                Log::info('eliminar AttValProdVariant por motivo que no existe la columan en el array' . $att_val_prod_variant->id);
            }
        }
    }

    public function obtenerProductVariantId($attributeProductVariantsAll, $c_range)
    {
        $pv_select = null;
        $c_pv = 0;
        $c_at_v = 0;
        $c = $c_range-1;
        $c_at = 0;
        Log::info('eval');
        foreach ($attributeProductVariantsAll as $attributeProductVariant) {
            Log::info($attributeProductVariant);
            if (count($attributeProductVariant)>0) {
                foreach ($attributeProductVariant as $file) {
                    $product_variant_id = $file->product_variant_id;
                    //evaluar cuantas veces se repite
                    $c_pv = 0;
                    foreach ($attributeProductVariantsAll as $attributeProductVariant2) {
                        foreach ($attributeProductVariant2 as $fileEval) {
                            if ($file->product_variant_id === $fileEval->product_variant_id) {
                                $c_pv++;
                                $c_at_v = count(ProductVariant::find($product_variant_id)->att_val_prod_variants);
                            }
                        }
                    }
                    //conclusión de evaluación
                    if ($c_pv > $c && $c_at_v > $c_at) {
                        $c = $c_pv;
                        $c_at = $c_at_v;
                        $pv_select = $product_variant_id;
                    }
                }
            } else {
                return null;
            }
        }
        Log::info('eval fin');
        return $pv_select;
    }

    public function getAttributeValueId($Item, $value)
    {
        return ProductVariantValue::where('item_id', $Item->id)->whereHas('attribute_value', function (Builder $query) use ($value) {
            $query->where('value', $value);
        })->first()->id;
    }

    public function updateOrCreateItem(array $args)
    {

        // TODO implement the resolver
        $Item = null;
        //$args['url_img'] = $args['url_img'] ? (trim($args['url_img']) != '' ? $args['url_img'] : 'products/product_default.jpg') : 'products/product_default.jpg';
        //$warehouse = Warehouse::where('establishment_id', $args['establishment_id'])->first();
        if (array_key_exists('id', $args)) {
            $Item = Item::find($args['id']);
            if ($Item) {
                $Item->fill($args);
                if ($Item->isDirty()) {
                    $Item->save();
                }
            } else {
                $Item = Item::create([
                    'description' => $args['description'],
                    'item_type_id' => $args['item_type_id'],
                    'internal_id' => $args['internal_id'],
                    'item_code' => $args['item_code'],
                    'item_code_gs1' => $args['item_code_gs1'],
                    'unit_type_id' => $args['unit_type_id'],
                    'currency_type_id' => $args['currency_type_id'],
                    'sale_unit_price' => $args['sale_unit_price'],
                    'has_isc' => $args['has_isc'],
                    'system_isc_type_id' => $args['system_isc_type_id'],
                    'percentage_isc' => $args['percentage_isc'],
                    'suggested_price' => $args['suggested_price'],
                    'sale_affectation_igv_type_id' => $args['sale_affectation_igv_type_id'],
                    'calculate_quantity' => $args['calculate_quantity'],
                    'has_igv' => $args['has_igv'],
                    'stock' => $args['stock'],
                    'stock_min' => $args['stock_min'],
                    'attributes' => $args['attributes'],
                    'brand_id' => $args['brand_id'],
                    'category_id' => $args['category_id'],
                    //'url_img' => $args['url_img'] ? (trim($args['url_img']) != '' ? $args['url_img'] : 'products/product_default.jpg') : 'products/product_default.jpg',
                    //'warehouse_id' => $warehouse ? $warehouse->id : null
                ]);
            }
        } else {
            $Item = Item::create([
                'description' => $args['description'],
                'item_type_id' => $args['item_type_id'],
                'internal_id' => $args['internal_id'],
                'item_code' => $args['item_code'],
                'item_code_gs1' => $args['item_code_gs1'],
                'unit_type_id' => $args['unit_type_id'],
                'currency_type_id' => $args['currency_type_id'],
                'sale_unit_price' => $args['sale_unit_price'],
                'has_isc' => $args['has_isc'],
                'system_isc_type_id' => $args['system_isc_type_id'],
                'percentage_isc' => $args['percentage_isc'],
                'suggested_price' => $args['suggested_price'],
                'sale_affectation_igv_type_id' => $args['sale_affectation_igv_type_id'],
                'calculate_quantity' => $args['calculate_quantity'],
                'has_igv' => $args['has_igv'],
                'stock' => $args['stock'],
                'stock_min' => $args['stock_min'],
                'attributes' => $args['attributes'],
                'brand_id' => $args['brand_id'],
                'category_id' => $args['category_id'],
                //'url_img' => $args['url_img'] ? (trim($args['url_img']) != '' ? $args['url_img'] : 'products/product_default.jpg') : 'products/product_default.jpg',
                //'warehouse_id' => $warehouse ? $warehouse->id : null
            ]);
        }
        return $Item;
    }

    public function refreshAttributevalues($product_variant_values, $Item)
    {
        //Todos los atributos de valor
        $extractValues = [];
        foreach ($product_variant_values as $key_pvv => $product_variant_value) {
            $values = $product_variant_value['values'];
            $extractValues[] = $values;
            foreach ($values as $key_value => $value) {
                $this->all_attribute_values[] = $value;
                $attributeValue = AttributeValue::where('attribute_id', $product_variant_value['attribute_id'])->where('value', $value)->first();
                if (!$attributeValue) {
                    $attributeValue = AttributeValue::create(
                        [
                            'value' => $value,
                            'custom' => false,
                            'color' => null,
                            'attribute_id' => $product_variant_value['attribute_id'],
                        ]
                    );
                }
                ProductVariantValue::updateOrCreate([
                    'item_id' => $Item->id,
                    'attribute_value_id' => $attributeValue->id,
                ], [
                    'item_id' => $Item->id,
                    'attribute_value_id' => $attributeValue->id,
                ]);
                //TODO agregar logica para variante de item
            }
        }
        return $extractValues;
    }

    function combinations($arrays, $i = 0)
    {
        if (!isset($arrays[$i])) {
            return array();
        }
        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }

        // get combinations from subsequent arrays
        $tmp = self::combinations($arrays, $i + 1);

        $result = array();

        // concat each array from tmp with each element from $arrays[$i]
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ?
                    array_merge(array($v), $t) :
                    array($v, $t);
            }
        }

        return $result;
    }

}

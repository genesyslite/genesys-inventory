<?php

namespace GenesysLite\GenesysInventory\Models\Extension;

use GenesysLite\GenesysCatalog\Models\AffectationIgvType;
use GenesysLite\GenesysCatalog\Models\PriceType;
use GenesysLite\GenesysCatalog\Models\SystemIscType;
use GenesysLite\GenesysInventory\Models\Item;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use GenesysLite\GenesysFact\Models\DocumentItem as GenesysFactDocumentType;

class DocumentItem extends GenesysFactDocumentType
{

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

}

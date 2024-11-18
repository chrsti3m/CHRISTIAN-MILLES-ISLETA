<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierBanana extends Model
{
    protected $table = 'supplier_banana';

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function bananaType()
    {
        return $this->belongsTo(BananaType::class, 'banana_type_id');
    }
}

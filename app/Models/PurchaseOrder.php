<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_order';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bananaType()
    {
        return $this->belongsTo(BananaType::class, 'banana_type_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class, 'purchase_order_id');
    }
}

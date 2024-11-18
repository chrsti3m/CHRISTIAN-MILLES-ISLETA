<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'supplier';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bananas()
    {
        return $this->hasMany(SupplierBanana::class, 'supplier_id');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class, 'supplier_id');
    }
}

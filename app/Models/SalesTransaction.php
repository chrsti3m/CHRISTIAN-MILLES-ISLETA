<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesTransaction extends Model
{
    protected $table = 'sales_transaction';

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    public function tricycleInventory()
    {
        return $this->belongsTo(TricycleInventory::class, 'tric_inventory_id');
    }

    public function waste()
    {
        return $this->hasMany(Waste::class, 'sales_transaction_id');
    }
}

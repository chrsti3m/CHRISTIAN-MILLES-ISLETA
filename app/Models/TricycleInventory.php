<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TricycleInventory extends Model
{
    protected $table = 'tricycle_inventory';

    public function tricycle()
    {
        return $this->belongsTo(Tricycle::class, 'tricycle_id'); // Ensure the correct table is referenced
    }


    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    public function bananaType()
    {
        return $this->belongsTo(BananaType::class, 'banana_type_id');
    }

    public function salesTransactions()
    {
        return $this->hasMany(SalesTransaction::class, 'tric_inventory_id');
    }

    public function waste()
    {
        return $this->hasMany(Waste::class, 'tricycle_inventory_id');
    }
}

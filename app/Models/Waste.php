<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Waste extends Model
{
    protected $table = 'waste';

    public function salesTransaction()
    {
        return $this->belongsTo(SalesTransaction::class, 'sales_transaction_id');
    }

    public function tricycleInventory()
    {
        return $this->belongsTo(TricycleInventory::class, 'tricycle_inventory_id');
    }
}

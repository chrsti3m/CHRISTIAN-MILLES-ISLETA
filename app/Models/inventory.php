<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{

    protected $primaryKey = 'inventory_id';
    // Disable automatic timestamp management
    public $timestamps = false;  // Disable timestamp management

    protected $table = 'inventory';

    protected $fillable = [
        'banana_type_id', 'quantity_in_stock', 'receive_date', 
        'supplier_id', 'user_id', 'purchase_order_id', // Add any other fields if necessary
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function bananaType()
    {
        return $this->belongsTo(BananaType::class, 'banana_type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tricycleInventories()
    {
        return $this->hasMany(TricycleInventory::class, 'inventory_id');
    }
}

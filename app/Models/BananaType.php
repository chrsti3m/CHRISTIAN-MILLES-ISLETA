<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BananaType extends Model
{
    protected $table = 'banana_type';
    protected $primaryKey = 'banana_type_id';
    
    protected $fillable = [
        'type_name',
        'description'
    ];

    // Relationships
    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'banana_type_id');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'banana_type_id');
    }

    public function supplierBananas()
    {
        return $this->hasMany(SupplierBanana::class, 'banana_type_id');
    }

    public function tricycleInventories()
    {
        return $this->hasMany(TricycleInventory::class, 'banana_type_id');
    }
}

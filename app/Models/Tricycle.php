<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tricycle extends Model
{
    protected $table = 'tricycle';
    protected $primaryKey = 'tricycle_id';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tricycleInventories()
    {
        return $this->hasMany(TricycleInventory::class, 'tricycle_id');
    }
}

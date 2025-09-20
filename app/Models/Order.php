<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'assigned_to',
        'total_price',
        'total_old_price',
        'discount',
        'quantity',
        'status',
        'order_code',
        'name',
        'email'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function handler(){
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function items(){
        return $this->hasMany(OrderItem::class);
    }

}

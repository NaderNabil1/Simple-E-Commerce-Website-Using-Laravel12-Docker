<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    use HasFactory;
    protected $table = 'order_logs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'order_id',
        'description',
        'status',
        'created_by',
        'name',
        'email',
    ];

    public function order(){
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function createdBy(){
        return $this->belongsTo(User::class, 'created_by');
    }

}

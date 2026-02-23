<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'orderable_type',
        'orderable_id',
        'price',
        'quantity',
        'preferred_start_date',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'preferred_start_date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderable()
    {
        return $this->morphTo();
    }

    public function getSubtotalAttribute()
    {
        return $this->price * $this->quantity;
    }
}

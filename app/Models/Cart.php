<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cartable_type',
        'cartable_id',
        'quantity',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cartable()
    {
        return $this->morphTo();
    }

    public function getSubtotalAttribute()
    {
        if ($this->cartable) {
            return $this->cartable->price * $this->quantity;
        }
        return 0;
    }
}

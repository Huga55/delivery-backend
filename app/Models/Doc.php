<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doc extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'dostavista_id',
        'user_id',
        'path',
        'name',
        'doc_type',
        'type',
    ];

    public function order()
    {
        return $this->belongsTo('App\Models\Order');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name_cargo',
        'address_from',
        'address_to',
        'type',
        'track_number',
        'weight',
        'length',
        'width',
        'height',
        'size',
        'date_take',
        'date_delivery',
        'value_client',
        'pay_type',
        'price',
        'errors',
    ];

    public function user()
    {
    	return $this->belongsTo('App\Models\User');
    }

    public function contacts()
    {
    	return $this->hasMany('App\Models\Contact');
    }

    public function docs()
    {
        return $this->hasMany('App\Models\Doc');
    }
}

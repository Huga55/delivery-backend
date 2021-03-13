<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'name_organization',
        'name',
        'phone_work',
        'phone_mobile',
        'phone_more',
        'position',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function address()
    {
        return $this->hasMany('App\Models\Address');
    }
}

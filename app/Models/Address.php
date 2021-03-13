<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'city',
        'street',
        'home',
        'corps',
        'structure',
        'type_rooms',
        'apartment',
    ];

    public function organization()
    {
        return $this->belongsTo('App\Models\Organization');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title_top',
        'title_main',
        'title_doc',
        'table_data',
        'posibility',
        'services',
        'addition',
    ];
}

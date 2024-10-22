<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class ModelConfig extends Model
{
    protected $fillable = [
        'column_config',
    ];

    protected $casts = [
        'column_config' => 'array',
    ];
}

<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class ModelConfig extends Model
{
    protected $table = 'model_config';
    protected $connection = 'pgsql_core';

    protected $fillable = [
        'column_config',
        'form_name',
        'app_name',
        'form_type',
        'filter_config'
    ];

    protected $casts = [
        'column_config' => 'array',
        'filter_config' => 'array'
    ];
}

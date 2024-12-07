<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $table = 'submissions';
    protected $connection = 'pgsql_core';

    protected $fillable = [
        'generated_documents',
        'id'
    ];

    protected $casts = [
        'generated_documents' => 'array',
        'id' => 'string'
    ];
}

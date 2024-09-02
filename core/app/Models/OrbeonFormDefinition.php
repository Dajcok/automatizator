<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrbeonFormDefinition extends Model
{
    use HasFactory;

    protected $table = 'orbeon_form_definition';
    public $timestamps = false;

    protected $fillable = [
        'created',
        'last_modified_time',
        'last_modified_by',
        'app',
        'form',
        'form_version',
        'form_metadata',
        'deleted',
        'xml'
    ];
}

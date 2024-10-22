<?php

namespace App\Models\Of;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrbeonFormDefinitionAttach extends Model
{
    use HasFactory;

    protected $table = 'orbeon_form_definition_attach';
    public $timestamps = false;

    protected $fillable = [
        'created',
        'last_modified_time',
        'last_modified_by',
        'app',
        'form',
        'form_version',
        'deleted',
        'file_name',
        'file_content'
    ];
}

<?php

namespace App\Models\Of;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrbeonFormDataAttach extends Model
{
    use HasFactory;

    protected $table = 'orbeon_form_data_attach';
    public $timestamps = false;

    protected $fillable = [
        'created',
        'last_modified_time',
        'last_modified_by',
        'username',
        'groupname',
        'organization_id',
        'app',
        'form',
        'form_version',
        'document_id',
        'deleted',
        'draft',
        'file_name',
        'file_content'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrbeonFormData extends Model
{
    use HasFactory;

    protected $table = 'orbeon_form_data';

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
        'stage',
        'document_id',
        'deleted',
        'draft',
        'xml'
    ];

    public function orbeonICurrent()
    {
        return $this->hasOne(OrbeonICurrent::class, 'data_id');
    }

    public function orbeonIControlText()
    {
        return $this->hasMany(OrbeonIControlText::class, 'data_id');
    }
}

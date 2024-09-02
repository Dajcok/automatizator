<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrbeonICurrent extends Model
{
    use HasFactory;

    protected $table = 'orbeon_i_current';
    public $timestamps = false;

    protected $fillable = [
        'data_id',
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
        'draft'
    ];

    public function orbeonFormData()
    {
        return $this->belongsTo(OrbeonFormData::class, 'data_id');
    }
}

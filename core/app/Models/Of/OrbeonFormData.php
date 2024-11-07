<?php

namespace App\Models\Of;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Of\OrbeonFormData
 * @property int $id
 * @property string $created
 * @property string $last_modified_time
 * @property string $last_modified_by
 * @property string $username
 * @property string $groupname
 * @property int $organization_id
 * @property string $app
 * @property string $form
 * @property string $form_version
 * @property string $stage
 * @property string $document_id
 * @property string $deleted
 * @property string $draft
 * @property string $xml
 */
class OrbeonFormData extends Model
{
    use HasFactory;

    public $timestamps = false;

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

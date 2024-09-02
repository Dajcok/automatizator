<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrbeonIControlText extends Model
{
    use HasFactory;

    protected $table = 'orbeon_i_control_text';
    public $timestamps = false;

    protected $fillable = [
        'data_id',
        'pos',
        'control',
        'val'
    ];

    public function orbeonFormData()
    {
        return $this->belongsTo(OrbeonFormData::class, 'data_id');
    }
}

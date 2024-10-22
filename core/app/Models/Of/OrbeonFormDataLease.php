<?php

namespace App\Models\Of;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrbeonFormDataLease extends Model
{
    use HasFactory;

    protected $table = 'orbeon_form_data_lease';
    protected $primaryKey = 'document_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'username',
        'groupname',
        'expiration'
    ];
}

<?php

namespace App\Models\Of;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrbeonOrganization extends Model
{
    use HasFactory;

    protected $table = 'orbeon_organization';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'depth',
        'pos',
        'name'
    ];
}

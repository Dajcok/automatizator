<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrbeonSeq extends Model
{
    use HasFactory;

    protected $table = 'orbeon_seq';
    protected $primaryKey = 'val';
    public $timestamps = false;
}

<?php

namespace App\Models\GomezApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConcentradoViewAtc extends Model
{
    protected $connection = 'mysql_gomezapp';
    protected $table = 'vw_concentrado_atc';
    // protected $fillable = ['department', 'asunto', 'fecha_reporte'];
}

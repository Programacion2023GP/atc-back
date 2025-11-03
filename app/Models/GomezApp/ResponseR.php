<?php

namespace App\Models\GomezApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponseR extends Model
{
    protected $connection = 'mysql_gomezapp';
    protected $table = 'reportes_respuestas';
    protected $primaryKey = 'id_reporte';
}

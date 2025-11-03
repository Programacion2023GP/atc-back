<?php

namespace App\Models\GomezApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsuntosDepTable extends Model
{
    protected $connection = 'mysql_gomezapp';
    protected $table = 'departamentos_asuntos';
    public $timestamps = false;
}

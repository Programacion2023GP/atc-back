<?php

namespace App\Models\GomezApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudesXEstatusView extends Model
{
    protected $connection = 'mysql_gomezapp';
    protected $table = 'solicitudesxestatus';
}

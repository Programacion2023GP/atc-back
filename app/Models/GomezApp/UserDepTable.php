<?php

namespace App\Models\GomezApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDepTable extends Model
{
    protected $connection = 'mysql_gomezapp';
    protected $table = 'usuarios_departamentos';
}

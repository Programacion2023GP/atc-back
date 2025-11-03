<?php

namespace App\Models\GomezApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuariosDep extends Model
{
    protected $connection = 'mysql_gomezapp';
    protected $table = 'userdep';

    // protected $hidden = [
    //     'created_at',
    //     'updated_at',
    //     'id',
    //     'user_id'
    // ];
}

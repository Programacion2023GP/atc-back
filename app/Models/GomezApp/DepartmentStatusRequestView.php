<?php

namespace App\Models\GomezApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentStatusRequestView extends Model
{
    protected $connection = 'mysql_gomezapp';
    protected $table = 'request_departaments_status';
}

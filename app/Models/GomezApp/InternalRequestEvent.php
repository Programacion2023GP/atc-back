<?php
namespace App\Models\GomezApp;
use Illuminate\Database\Eloquent\Model;
class InternalRequestEvent extends Model { protected $connection = 'mysql_gomezapp'; protected $guarded = []; protected $casts = ['metadata' => 'array']; }

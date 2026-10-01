<?php
namespace App\Models\GomezApp;
use Illuminate\Database\Eloquent\Model;
class InternalRequestSlaCycle extends Model { protected $connection = 'mysql_gomezapp'; protected $guarded = []; protected $casts = ['started_at' => 'datetime', 'due_at' => 'datetime', 'stopped_at' => 'datetime']; }

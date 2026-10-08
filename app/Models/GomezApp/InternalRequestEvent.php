<?php
namespace App\Models\GomezApp;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
class InternalRequestEvent extends Model { protected $connection = 'mysql_gomezapp'; protected $guarded = []; protected $casts = ['metadata' => 'array']; public function user() { return $this->belongsTo(User::class); } }

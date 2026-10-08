<?php
namespace App\Models\GomezApp;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
class InternalRequestResponse extends Model { protected $connection = 'mysql_gomezapp'; protected $guarded = []; protected $casts = ['response_json' => 'array']; public function user() { return $this->belongsTo(User::class); } }

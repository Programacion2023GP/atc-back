<?php
namespace App\Models\GomezApp;
use Illuminate\Database\Eloquent\Model;
class InternalRequestFile extends Model { protected $connection = 'mysql_gomezapp'; protected $guarded = []; protected $appends = ['url']; public function getUrlAttribute() { return asset('storage/'.$this->path); } }

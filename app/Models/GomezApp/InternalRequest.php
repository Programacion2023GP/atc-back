<?php

namespace App\Models\GomezApp;

use Illuminate\Database\Eloquent\Model;

class InternalRequest extends Model
{
    protected $connection = 'mysql_gomezapp';
    protected $guarded = [];
    protected $casts = ['body_json' => 'array', 'sent_at' => 'datetime', 'received_at' => 'datetime', 'started_at' => 'datetime', 'first_responded_at' => 'datetime', 'closed_at' => 'datetime'];

    public function origin() { return $this->belongsTo(Department::class, 'origin_department_id'); }
    public function destination() { return $this->belongsTo(Department::class, 'destination_department_id'); }
    public function events() { return $this->hasMany(InternalRequestEvent::class)->orderBy('created_at'); }
    public function cycles() { return $this->hasMany(InternalRequestSlaCycle::class)->orderBy('cycle_number'); }
    public function responses() { return $this->hasMany(InternalRequestResponse::class)->orderBy('created_at'); }
    public function files() { return $this->hasMany(InternalRequestFile::class)->orderBy('created_at'); }
}

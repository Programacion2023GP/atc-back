<?php

namespace App\Models\GomezApp;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class InternalRequest extends Model
{
    protected $connection = 'mysql_gomezapp';
    protected $guarded = [];
    protected $casts = ['body_json' => 'array', 'incoming_office_at' => 'datetime', 'sent_at' => 'datetime', 'received_at' => 'datetime', 'started_at' => 'datetime', 'first_responded_at' => 'datetime', 'closed_at' => 'datetime'];

    public function origin() { return $this->belongsTo(Department::class, 'origin_department_id'); }
    public function destination() { return $this->belongsTo(Department::class, 'destination_department_id'); }
    public function incomingOrigin() { return $this->belongsTo(Department::class, 'incoming_origin_department_id'); }
    public function incomingDestination() { return $this->belongsTo(Department::class, 'incoming_destination_department_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function incomingReceiver() { return $this->belongsTo(User::class, 'incoming_received_by_user_id'); }
    public function receiver() { return $this->belongsTo(User::class, 'received_by'); }
    public function starter() { return $this->belongsTo(User::class, 'started_by'); }
    public function decisionMaker() { return $this->belongsTo(User::class, 'decision_by'); }
    public function redirector() { return $this->belongsTo(User::class, 'redirected_by'); }
    public function events() { return $this->hasMany(InternalRequestEvent::class)->orderBy('created_at'); }
    public function cycles() { return $this->hasMany(InternalRequestSlaCycle::class)->orderBy('cycle_number'); }
    public function responses() { return $this->hasMany(InternalRequestResponse::class)->orderBy('created_at'); }
    public function files() { return $this->hasMany(InternalRequestFile::class)->orderBy('created_at'); }
}

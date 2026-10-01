<?php

namespace App\Http\Controllers\GomezApp;

use App\Http\Controllers\Controller;
use App\Models\GomezApp\Department;
use App\Models\GomezApp\InternalRequest;
use App\Models\GomezApp\InternalRequestEvent;
use App\Models\GomezApp\InternalRequestFile;
use App\Models\GomezApp\InternalRequestResponse;
use App\Models\GomezApp\InternalRequestSlaCycle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InternalRequestController extends Controller
{
    private const STATUSES = ['BORRADOR', 'ENVIADA', 'RECIBIDA', 'EN_PROCESO', 'RESPONDIDA', 'CERRADA'];
    private const ALLOWED_NODES = ['doc', 'paragraph', 'text', 'bulletList', 'orderedList', 'listItem', 'hardBreak'];
    private const ALLOWED_MARKS = ['bold', 'italic', 'underline'];

    public function bootstrap(Request $request)
    {
        $issuer = $this->issuerDepartment($request);
        $destinations = Department::query()->where('active', 1)->when($issuer, fn ($q) => $q->where('id', '!=', $issuer->id))->orderBy('department')->get(['id', 'department']);
        return $this->ok(['issuer' => $issuer, 'destinations' => $destinations, 'can_issue' => $this->isSuperAdmin($request) || (bool) $issuer]);
    }

    public function folioPreview(Request $request)
    {
        $this->requireIssuer($request);
        $prefix = $this->cleanPrefix($request->query('prefix', 'OM'));
        $year = (int) $request->query('year', now()->year);
        $last = DB::connection('mysql_gomezapp')->table('internal_request_folio_sequences')->where(compact('prefix', 'year'))->value('last_number') ?? 0;
        return $this->ok(['prefix' => $prefix, 'year' => $year, 'number' => $last + 1, 'folio' => $this->formatFolio($prefix, $year, $last + 1)]);
    }

    public function index(Request $request)
    {
        $query = InternalRequest::query()->with(['origin:id,department', 'destination:id,department'])->latest('updated_at');
        $this->scopeVisible($request, $query);
        foreach (['status', 'destination_department_id'] as $field) if ($request->filled($field)) $query->where($field, $request->input($field));
        if ($request->filled('search')) {
            $search = '%'.$request->input('search').'%';
            $query->where(fn ($q) => $q->where('folio', 'like', $search)->orWhere('subject', 'like', $search)->orWhere('body_text', 'like', $search));
        }
        $items = $query->paginate(min((int) $request->input('per_page', 25), 100));
        $items->getCollection()->transform(fn ($item) => $this->withTiming($item));
        return $this->ok($items);
    }

    public function show(Request $request, InternalRequest $internalRequest)
    {
        $this->requireVisible($request, $internalRequest);
        return $this->ok($this->withTiming($internalRequest->load(['origin:id,department', 'destination:id,department', 'events', 'cycles', 'responses', 'files'])));
    }

    public function saveDraft(Request $request, ?InternalRequest $internalRequest = null)
    {
        $issuer = $this->requireIssuer($request);
        if ($internalRequest && ($internalRequest->status !== 'BORRADOR' || $internalRequest->created_by !== $request->user()->id) && !$this->isSuperAdmin($request)) abort(403, 'El borrador no puede editarse.');
        $data = $this->validateDocument($request, true);
        $bodyText = $this->bodyText($data['body_json']);
        if (mb_strlen($bodyText) > 20000) abort(422, 'La redacción excede 20,000 caracteres.');

        return DB::connection('mysql_gomezapp')->transaction(function () use ($request, $issuer, $internalRequest, $data, $bodyText) {
            $before = $internalRequest ? $internalRequest->only(['subject', 'body_json', 'destination_department_id', 'folio_prefix', 'folio_number']) : null;
            $internalRequest ??= new InternalRequest(['created_by' => $request->user()->id, 'origin_department_id' => $issuer->id, 'status' => 'BORRADOR', 'folio_year' => now()->year]);
            $internalRequest->fill([
                'destination_department_id' => $data['destination_department_id'], 'subject' => $data['subject'], 'body_json' => $data['body_json'],
                'body_text' => $bodyText, 'tracking_mode' => $data['tracking_mode'], 'business_days' => $data['tracking_mode'] === 'PLAZO_CONFIGURADO' ? $data['business_days'] : null,
                'folio_prefix' => $this->cleanPrefix($data['folio_prefix'] ?? 'OM'), 'folio_number' => $data['folio_number'] ?? null,
            ])->save();
            $this->event($request, $internalRequest, $before ? 'BORRADOR_ACTUALIZADO' : 'BORRADOR_CREADO', null, 'BORRADOR', $before ? ['previous' => $before] : null);
            return $this->ok($internalRequest->fresh(['origin:id,department', 'destination:id,department']), $before ? 200 : 201);
        });
    }

    public function send(Request $request, InternalRequest $internalRequest)
    {
        $this->requireIssuer($request);
        if ($internalRequest->status !== 'BORRADOR') abort(409, 'Solo se puede enviar un borrador.');
        return DB::connection('mysql_gomezapp')->transaction(function () use ($request, $internalRequest) {
            $locked = InternalRequest::query()->lockForUpdate()->findOrFail($internalRequest->id);
            $prefix = $this->cleanPrefix($locked->folio_prefix ?: 'OM');
            $year = $locked->folio_year ?: now()->year;
            $sequence = DB::connection('mysql_gomezapp')->table('internal_request_folio_sequences')->where(compact('prefix', 'year'))->lockForUpdate()->first();
            $requested = $locked->folio_number;
            if (!$sequence) {
                DB::connection('mysql_gomezapp')->table('internal_request_folio_sequences')->insert(['prefix' => $prefix, 'year' => $year, 'last_number' => 0, 'created_at' => now(), 'updated_at' => now()]);
                $last = 0;
            } else $last = (int) $sequence->last_number;
            $number = $requested ?: $last + 1;
            $folio = $this->formatFolio($prefix, $year, $number);
            if (InternalRequest::query()->where('folio', $folio)->whereKeyNot($locked->id)->exists()) abort(422, 'El folio ya está en uso.');
            DB::connection('mysql_gomezapp')->table('internal_request_folio_sequences')->where(compact('prefix', 'year'))->update(['last_number' => max($last, $number), 'updated_at' => now()]);
            $locked->update(['folio' => $folio, 'folio_number' => $number, 'status' => 'ENVIADA', 'sent_at' => now()]);
            $this->createCycle($locked);
            $this->event($request, $locked, $requested ? 'FOLIO_MANUAL_ASIGNADO' : 'SOLICITUD_ENVIADA', 'BORRADOR', 'ENVIADA', ['folio' => $folio]);
            return $this->ok($locked->fresh());
        });
    }

    public function receive(Request $request, InternalRequest $internalRequest) { return $this->transition($request, $internalRequest, 'ENVIADA', 'RECIBIDA', 'SOLICITUD_RECIBIDA', 'received_at'); }
    public function start(Request $request, InternalRequest $internalRequest) { return $this->transition($request, $internalRequest, 'RECIBIDA', 'EN_PROCESO', 'ATENCION_INICIADA', 'started_at'); }

    public function respond(Request $request, InternalRequest $internalRequest)
    {
        $this->requireDestination($request, $internalRequest);
        $data = $request->validate(['response' => ['required', 'string', 'max:20000']]);
        if (!in_array($internalRequest->status, ['ENVIADA', 'RECIBIDA', 'EN_PROCESO'])) abort(409, 'La solicitud no admite respuesta en su estado actual.');
        return DB::connection('mysql_gomezapp')->transaction(function () use ($request, $internalRequest, $data) {
            InternalRequestResponse::create(['internal_request_id' => $internalRequest->id, 'user_id' => $request->user()->id, 'response' => $data['response']]);
            $from = $internalRequest->status;
            $now = now();
            $internalRequest->update(['status' => 'CERRADA', 'first_responded_at' => $internalRequest->first_responded_at ?: $now, 'closed_at' => $now]);
            InternalRequestSlaCycle::where('internal_request_id', $internalRequest->id)->whereNull('stopped_at')->update(['stopped_at' => $now, 'updated_at' => $now]);
            $this->event($request, $internalRequest, 'RESPUESTA_Y_CIERRE_AUTOMATICO', $from, 'CERRADA');
            return $this->ok($internalRequest->fresh(['responses', 'cycles']));
        });
    }

    public function reopen(Request $request, InternalRequest $internalRequest)
    {
        $this->requireIssuer($request);
        if ($internalRequest->status !== 'CERRADA') abort(409, 'Solo se puede reabrir una solicitud cerrada.');
        return DB::connection('mysql_gomezapp')->transaction(function () use ($request, $internalRequest) {
            $internalRequest->update(['status' => 'ENVIADA', 'received_at' => null, 'started_at' => null, 'closed_at' => null]);
            $this->createCycle($internalRequest);
            $this->event($request, $internalRequest, 'SOLICITUD_REABIERTA', 'CERRADA', 'ENVIADA');
            return $this->ok($internalRequest->fresh(['cycles']));
        });
    }

    public function destroyDraft(Request $request, InternalRequest $internalRequest)
    {
        $this->requireIssuer($request);
        if ($internalRequest->status !== 'BORRADOR') abort(409, 'Solo se pueden eliminar solicitudes en borrador.');

        $paths = $internalRequest->files()->pluck('path');
        DB::connection('mysql_gomezapp')->transaction(function () use ($internalRequest) {
            $internalRequest->events()->delete();
            $internalRequest->cycles()->delete();
            $internalRequest->responses()->delete();
            $internalRequest->files()->delete();
            $internalRequest->delete();
        });
        Storage::disk('public')->delete($paths->all());

        return $this->ok(['deleted' => true]);
    }

    public function uploadEvidence(Request $request, InternalRequest $internalRequest)
    {
        $this->requireVisible($request, $internalRequest);
        $request->validate(['files' => ['required', 'array', 'max:10'], 'files.*' => ['file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx', 'max:10240']]);
        $saved = [];
        foreach ($request->file('files') as $file) {
            $path = $file->store("internal-requests/{$internalRequest->id}", 'public');
            $saved[] = InternalRequestFile::create(['internal_request_id' => $internalRequest->id, 'user_id' => $request->user()->id, 'original_name' => $file->getClientOriginalName(), 'path' => $path, 'mime_type' => $file->getMimeType(), 'size' => $file->getSize()]);
        }
        $this->event($request, $internalRequest, 'EVIDENCIA_ADJUNTADA', $internalRequest->status, $internalRequest->status, ['files' => collect($saved)->pluck('original_name')]);
        return $this->ok($saved, 201);
    }

    public function metrics(Request $request)
    {
        $query = InternalRequest::query(); $this->scopeVisible($request, $query); $rows = $query->get();
        $closed = $rows->whereNotNull('first_responded_at');
        $departmentNames = Department::whereIn('id', $rows->pluck('destination_department_id')->unique())->pluck('department', 'id');
        $byDestination = $rows->groupBy('destination_department_id')->map(fn ($items, $id) => ['department_id' => $id, 'department' => $departmentNames[$id] ?? 'Sin departamento', 'total' => $items->count()])->values();
        $timed = $rows->map(fn ($r) => $this->withTiming($r));
        return $this->ok([
            'total' => $rows->count(), 'by_status' => $rows->countBy('status'), 'by_destination' => $byDestination,
            'overdue' => $timed->where('timing.is_overdue', true)->count(), 'due_soon' => $timed->where('timing.is_due_soon', true)->count(),
            'compliance_percent' => $closed->count() ? round(100 * $closed->filter(function ($r) { $cycle = $r->cycles()->latest('cycle_number')->first(); return !$cycle || !$cycle->due_at || $r->first_responded_at->lte($cycle->due_at); })->count() / $closed->count(), 1) : null,
            'average_first_response_hours' => $closed->count() ? round($closed->avg(fn ($r) => $r->sent_at->diffInMinutes($r->first_responded_at)) / 60, 1) : null,
            'average_stage_hours' => [
                'envio_a_recepcion' => $rows->whereNotNull('received_at')->avg(fn ($r) => $r->sent_at->diffInMinutes($r->received_at)) ? round($rows->whereNotNull('received_at')->avg(fn ($r) => $r->sent_at->diffInMinutes($r->received_at)) / 60, 1) : null,
                'recepcion_a_inicio' => $rows->whereNotNull('started_at')->avg(fn ($r) => $r->received_at?->diffInMinutes($r->started_at)) ? round($rows->whereNotNull('started_at')->avg(fn ($r) => $r->received_at?->diffInMinutes($r->started_at)) / 60, 1) : null,
                'inicio_a_respuesta' => $rows->whereNotNull('first_responded_at')->avg(fn ($r) => ($r->started_at ?: $r->sent_at)->diffInMinutes($r->first_responded_at)) ? round($rows->whereNotNull('first_responded_at')->avg(fn ($r) => ($r->started_at ?: $r->sent_at)->diffInMinutes($r->first_responded_at)) / 60, 1) : null,
            ],
        ]);
    }

    private function validateDocument(Request $request, bool $draft): array
    {
        $data = $request->validate([
            'destination_department_id' => ['required', 'integer', Rule::exists('mysql_gomezapp.departments', 'id')->where('active', 1)],
            'subject' => ['required', 'string', 'max:255'], 'body_json' => ['required', 'array'],
            'tracking_mode' => ['required', Rule::in(['SOLO_SEGUIMIENTO', 'PLAZO_CONFIGURADO'])],
            'business_days' => ['nullable', 'required_if:tracking_mode,PLAZO_CONFIGURADO', 'integer', 'min:1', 'max:365'],
            'folio_prefix' => ['nullable', 'string', 'max:12', 'regex:/^[A-Za-z0-9-]+$/'], 'folio_number' => ['nullable', 'integer', 'min:1', 'max:999999999'],
        ]);
        if (!$this->validNode($data['body_json']) || trim($this->bodyText($data['body_json'])) === '') abort(422, 'La redacción contiene elementos no permitidos o está vacía.');
        return $data;
    }

    private function validNode(array $node): bool
    {
        if (!isset($node['type']) || !in_array($node['type'], self::ALLOWED_NODES, true)) return false;
        foreach (($node['marks'] ?? []) as $mark) if (!isset($mark['type']) || !in_array($mark['type'], self::ALLOWED_MARKS, true)) return false;
        $attrs = array_keys($node['attrs'] ?? []); foreach ($attrs as $attr) if (!in_array($attr, ['textAlign', 'indent'], true)) return false;
        foreach (($node['content'] ?? []) as $child) if (!is_array($child) || !$this->validNode($child)) return false;
        return true;
    }

    private function bodyText(array $node): string { return trim(collect($node['content'] ?? [])->map(fn ($child) => $child['type'] === 'text' ? ($child['text'] ?? '') : $this->bodyText($child).(in_array($child['type'], ['paragraph', 'listItem']) ? "\n" : ''))->implode('')); }
    private function cleanPrefix(string $prefix): string { $prefix = strtoupper(trim($prefix)); if (!preg_match('/^[A-Z0-9-]{2,12}$/', $prefix)) abort(422, 'El prefijo debe tener de 2 a 12 caracteres alfanuméricos.'); return $prefix; }
    private function formatFolio(string $prefix, int $year, int $number): string { return sprintf('%s-%d-%05d', $prefix, $year, $number); }

    private function transition(Request $request, InternalRequest $item, string $from, string $to, string $type, string $timestamp)
    {
        $this->requireDestination($request, $item); if ($item->status !== $from) abort(409, "La transición {$item->status} → {$to} no es válida.");
        $item->update(['status' => $to, $timestamp => now()]); $this->event($request, $item, $type, $from, $to); return $this->ok($item->fresh());
    }

    private function createCycle(InternalRequest $item): void
    {
        $number = (int) InternalRequestSlaCycle::where('internal_request_id', $item->id)->max('cycle_number') + 1;
        InternalRequestSlaCycle::create(['internal_request_id' => $item->id, 'cycle_number' => $number, 'tracking_mode' => $item->tracking_mode, 'business_days' => $item->business_days, 'started_at' => now(), 'due_at' => $item->tracking_mode === 'PLAZO_CONFIGURADO' ? $this->addBusinessDays(now(), $item->business_days) : null]);
    }

    private function addBusinessDays(Carbon $date, int $days): Carbon { $cursor = $date->copy(); while ($days > 0) { $cursor->addDay(); if (!$cursor->isWeekend()) $days--; } return $cursor; }
    private function businessDaysBetween(Carbon $from, Carbon $to): int { $days = 0; $cursor = $from->copy()->startOfDay(); while ($cursor->lt($to->copy()->startOfDay())) { $cursor->addDay(); if (!$cursor->isWeekend()) $days++; } return $days; }

    private function withTiming(InternalRequest $item): InternalRequest
    {
        $cycle = $item->relationLoaded('cycles') ? $item->cycles->last() : $item->cycles()->latest('cycle_number')->first(); $now = now();
        $remaining = $cycle && $cycle->due_at && !$cycle->stopped_at ? ($cycle->due_at->isPast() ? -$this->businessDaysBetween($cycle->due_at, $now) : $this->businessDaysBetween($now, $cycle->due_at)) : null;
        $item->setAttribute('timing', ['due_at' => $cycle?->due_at, 'business_days_remaining' => $remaining, 'is_overdue' => $remaining !== null && $remaining < 0, 'is_due_soon' => $remaining !== null && $remaining >= 0 && $remaining <= 2]); return $item;
    }

    private function issuerDepartment(Request $request): ?Department
    {
        return Department::query()->join('usuarios_departamentos as ud', 'ud.departamento_id', '=', 'departments.id')->where('ud.user_id', $request->user()->id)->where('departments.can_issue_internal_requests', true)->where('departments.active', 1)->select('departments.*')->first();
    }
    private function departmentIds(Request $request) { return DB::connection('mysql_gomezapp')->table('usuarios_departamentos')->where('user_id', $request->user()->id)->pluck('departamento_id'); }
    private function isSuperAdmin(Request $request): bool { return (int) $request->user()->role_id === 1; }
    private function requireIssuer(Request $request): Department { $issuer = $this->issuerDepartment($request); if (!$issuer && !$this->isSuperAdmin($request)) abort(403, 'Solo Oficialía Mayor puede realizar esta acción.'); return $issuer ?: Department::where('can_issue_internal_requests', true)->firstOrFail(); }
    private function requireDestination(Request $request, InternalRequest $item): void { if (!$this->isSuperAdmin($request) && !$this->departmentIds($request)->contains($item->destination_department_id)) abort(403, 'La solicitud no corresponde a sus departamentos.'); }
    private function requireVisible(Request $request, InternalRequest $item): void { if (!$this->isSuperAdmin($request) && !$this->issuerDepartment($request) && !$this->departmentIds($request)->contains($item->destination_department_id)) abort(403); }
    private function scopeVisible(Request $request, $query): void { if (!$this->isSuperAdmin($request) && !$this->issuerDepartment($request)) $query->whereIn('destination_department_id', $this->departmentIds($request)); }
    private function event(Request $request, InternalRequest $item, string $type, ?string $from, ?string $to, $metadata = null): void { InternalRequestEvent::create(['internal_request_id' => $item->id, 'user_id' => $request->user()->id, 'type' => $type, 'from_status' => $from, 'to_status' => $to, 'metadata' => $metadata]); }
    private function ok($result, int $status = 200) { return response()->json(['data' => ['status_code' => $status, 'status' => true, 'result' => $result]], $status); }
}

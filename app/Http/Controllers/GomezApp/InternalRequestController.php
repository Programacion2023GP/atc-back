<?php

namespace App\Http\Controllers\GomezApp;

use App\Http\Controllers\Controller;
use App\Models\User;
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
    private const STATUSES = ['BORRADOR', 'ENVIADA', 'RECIBIDA', 'EN_PROCESO', 'CERRADA', 'NO_AUTORIZADA'];
    private const DECISIONS = ['Para su atención y acuerdo', 'Solicitud Autorizada', 'Para que realice los trámites correspondientes', 'Solicitud no autorizada'];
    private const ALLOWED_NODES = ['doc', 'paragraph', 'text', 'bulletList', 'orderedList', 'listItem', 'hardBreak'];
    private const ALLOWED_MARKS = ['bold', 'italic', 'underline'];
    private const PERMISSION_REDIRECT = 'Redirigir Departamento';
    private const PERMISSION_RESOLVE = 'Resolver Solicitud Interna';
    private const PERMISSION_REOPEN = 'Reabrir Solicitud Interna';
    private const PERMISSION_RECEIVE = 'Confirmar Recepción';
    private const PERMISSION_START = 'Iniciar Atención';
    private const PERMISSION_RESPOND = 'Responder';

    public function bootstrap(Request $request)
    {
        $issuer = $this->issuerDepartment($request);
        $departments = Department::query()->where('active', 1)->orderBy('department')->get(['id', 'department']);
        $destinations = $departments->when($issuer, fn ($items) => $items->where('id', '!=', $issuer->id))->values();
        $issuerId = $issuer?->id ?: Department::query()->where('can_issue_internal_requests', true)->value('id');
        $officeUsers = $issuerId ? User::query()
            ->join('usuarios_departamentos as ud', 'ud.user_id', '=', 'users.id')
            ->where('ud.departamento_id', $issuerId)->where('users.active', 1)
            ->orderBy('users.name')->orderBy('users.paternal_last_name')
            ->get(['users.id', 'users.name', 'users.paternal_last_name', 'users.maternal_last_name']) : collect();
        return $this->ok([
            'issuer' => $issuer ?: Department::query()->find($issuerId),
            'destinations' => $destinations,
            'departments' => $departments,
            'office_users' => $officeUsers,
            'current_user_id' => $request->user()->id,
            'can_issue' => $this->isSuperAdmin($request) || (bool) $issuer,
            'permissions' => [
                'create' => $this->hasCrudPermission($request, 'create'),
                'update' => $this->hasCrudPermission($request, 'update'),
                'delete' => $this->hasCrudPermission($request, 'delete'),
                'redirect' => $this->hasPermission($request, self::PERMISSION_REDIRECT),
                'resolve' => $this->hasPermission($request, self::PERMISSION_RESOLVE),
                'reopen' => $this->hasPermission($request, self::PERMISSION_REOPEN),
                'receive' => $this->hasPermission($request, self::PERMISSION_RECEIVE),
                'start' => $this->hasPermission($request, self::PERMISSION_START),
                'respond' => $this->hasPermission($request, self::PERMISSION_RESPOND),
            ],
        ]);
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
        $query = InternalRequest::query()->with([
            'origin:id,department', 'destination:id,department',
            'creator:id,name,paternal_last_name,maternal_last_name',
            'receiver:id,name,paternal_last_name,maternal_last_name',
        ])->latest('updated_at');
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
        if ($internalRequest->status === 'ENVIADA' && ! $this->isSuperAdmin($request) && ! $this->issuerDepartment($request) && $this->departmentIds($request)->contains($internalRequest->destination_department_id) && $this->hasPermission($request, self::PERMISSION_RECEIVE)) {
            $internalRequest->update(['status' => 'RECIBIDA', 'received_at' => now(), 'received_by' => $request->user()->id]);
            $this->event($request, $internalRequest, 'SOLICITUD_RECIBIDA_AL_ABRIR', 'ENVIADA', 'RECIBIDA');
        }
        return $this->ok($this->withTiming($internalRequest->load([
            'origin:id,department', 'destination:id,department',
            'incomingOrigin:id,department', 'incomingDestination:id,department',
            'creator:id,name,paternal_last_name,maternal_last_name',
            'incomingReceiver:id,name,paternal_last_name,maternal_last_name',
            'receiver:id,name,paternal_last_name,maternal_last_name',
            'starter:id,name,paternal_last_name,maternal_last_name',
            'decisionMaker:id,name,paternal_last_name,maternal_last_name',
            'redirector:id,name,paternal_last_name,maternal_last_name',
            'events.user:id,name,paternal_last_name,maternal_last_name',
            'cycles', 'responses.user:id,name,paternal_last_name,maternal_last_name', 'files',
        ])));
    }

    public function saveDraft(Request $request, ?InternalRequest $internalRequest = null)
    {
        $issuer = $this->requireIssuer($request);
        if (! $internalRequest) $this->requireCrudPermission($request, 'create');
        if ($internalRequest && $internalRequest->status !== 'BORRADOR') abort(409, 'Solo se puede editar una solicitud en borrador.');
        if ($internalRequest && $internalRequest->created_by !== $request->user()->id && ! $this->hasCrudPermission($request, 'update')) abort(403, 'No tiene permiso para corregir este borrador.');
        $data = $this->validateDocument($request, true);
        $bodyText = $this->bodyText($data['body_json']);
        if (mb_strlen($bodyText) > 20000) abort(422, 'La redacción excede 20,000 caracteres.');
        $redirected = $internalRequest && (int) $internalRequest->destination_department_id !== (int) $data['destination_department_id'];
        if ($redirected) $this->requirePermission($request, self::PERMISSION_REDIRECT);

        return DB::connection('mysql_gomezapp')->transaction(function () use ($request, $issuer, $internalRequest, $data, $bodyText, $redirected) {
            $before = $internalRequest ? $internalRequest->only(['subject', 'body_json', 'destination_department_id', 'folio_prefix', 'folio_number']) : null;
            $internalRequest ??= new InternalRequest(['created_by' => $request->user()->id, 'origin_department_id' => $issuer->id, 'incoming_received_by_user_id' => $request->user()->id, 'status' => 'BORRADOR', 'folio_year' => now()->year]);
            $internalRequest->fill([
                'destination_department_id' => $data['destination_department_id'], 'subject' => $data['subject'], 'body_json' => $data['body_json'],
                'body_text' => $bodyText, 'tracking_mode' => $data['tracking_mode'], 'business_days' => $data['tracking_mode'] === 'PLAZO_CONFIGURADO' ? $data['business_days'] : null,
                'folio_prefix' => $this->cleanPrefix($data['folio_prefix'] ?? 'OM'), 'folio_number' => $data['folio_number'] ?? null,
                'incoming_office_number' => $data['incoming_office_number'] ?? null,
                'incoming_office_at' => $data['incoming_office_at'] ?? null,
                'incoming_origin_department_id' => $data['incoming_origin_department_id'] ?? null,
                'incoming_destination_department_id' => $data['incoming_destination_department_id'] ?? null,
                'incoming_received_by_user_id' => $internalRequest->incoming_received_by_user_id ?: $request->user()->id,
                'redirected_by' => $redirected ? $request->user()->id : $internalRequest->redirected_by,
            ])->save();
            $this->event($request, $internalRequest, $before ? 'BORRADOR_ACTUALIZADO' : 'BORRADOR_CREADO', null, 'BORRADOR', $before ? ['previous' => $before] : null);
            if ($redirected) $this->event($request, $internalRequest, 'SOLICITUD_REDIRIGIDA', 'BORRADOR', 'BORRADOR', ['from_department_id' => $before['destination_department_id'], 'to_department_id' => $data['destination_department_id']]);
            return $this->ok($internalRequest->fresh(['origin:id,department', 'destination:id,department']), $before ? 200 : 201);
        });
    }

    public function send(Request $request, InternalRequest $internalRequest)
    {
        $this->requirePermission($request, self::PERMISSION_RESOLVE);
        $this->requireIssuer($request);
        if ($internalRequest->status !== 'BORRADOR') abort(409, 'Solo se puede resolver un borrador.');
        $data = $request->validate(['decision' => ['required', Rule::in(self::DECISIONS)]]);
        if (trim((string) $internalRequest->body_text) === '') abort(422, 'Agregue las observaciones de Oficialía Mayor antes de resolver.');
        if (! $internalRequest->files()->where('kind', 'OFICIO_REFERENCIA')->exists()) abort(422, 'Adjunte el oficio recibido antes de resolver la solicitud.');
        return DB::connection('mysql_gomezapp')->transaction(function () use ($request, $internalRequest, $data) {
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
            $rejected = $data['decision'] === 'Solicitud no autorizada';
            $status = $rejected ? 'NO_AUTORIZADA' : 'ENVIADA';
            $locked->update(['folio' => $folio, 'folio_number' => $number, 'decision' => $data['decision'], 'decision_by' => $request->user()->id, 'decided_at' => now(), 'status' => $status, 'sent_at' => $rejected ? null : now(), 'closed_at' => $rejected ? now() : null]);
            if (! $rejected) $this->createCycle($locked);
            $this->event($request, $locked, $rejected ? 'SOLICITUD_NO_AUTORIZADA' : 'SOLICITUD_RESUELTA_Y_ENVIADA', 'BORRADOR', $status, ['folio' => $folio, 'decision' => $data['decision']]);
            return $this->ok($locked->fresh());
        });
    }

    public function redirect(Request $request, InternalRequest $internalRequest)
    {
        $this->requirePermission($request, self::PERMISSION_REDIRECT);
        $this->requireIssuer($request);
        if ($internalRequest->status !== 'BORRADOR') abort(409, 'Solo se puede redirigir un borrador.');
        $data = $request->validate(['destination_department_id' => ['required', 'integer', Rule::exists('mysql_gomezapp.departments', 'id')->where('active', 1)]]);
        $previous = $internalRequest->destination_department_id;
        $internalRequest->update(['destination_department_id' => $data['destination_department_id'], 'redirected_by' => $request->user()->id]);
        $this->event($request, $internalRequest, 'SOLICITUD_REDIRIGIDA', 'BORRADOR', 'BORRADOR', ['from_department_id' => $previous, 'to_department_id' => $data['destination_department_id']]);
        return $this->ok($internalRequest->fresh(['destination:id,department']));
    }

    public function receive(Request $request, InternalRequest $internalRequest)
    {
        $this->requirePermission($request, self::PERMISSION_RECEIVE);
        $this->requireDestination($request, $internalRequest);
        if ($internalRequest->status !== 'ENVIADA') abort(409, 'La solicitud no puede recibirse en su estado actual.');
        $internalRequest->update(['status' => 'RECIBIDA', 'received_at' => now(), 'received_by' => $request->user()->id]);
        $this->event($request, $internalRequest, 'SOLICITUD_RECIBIDA', 'ENVIADA', 'RECIBIDA');
        return $this->ok($internalRequest->fresh());
    }

    public function start(Request $request, InternalRequest $internalRequest)
    {
        $this->requirePermission($request, self::PERMISSION_START);
        $this->requireDestination($request, $internalRequest);
        if ($internalRequest->status !== 'RECIBIDA') abort(409, 'La atención no puede iniciarse en su estado actual.');
        $data = $request->validate(['attention_plan' => ['required', 'string', 'max:20000']]);
        $internalRequest->update(['status' => 'EN_PROCESO', 'started_at' => now(), 'started_by' => $request->user()->id, 'attention_plan' => $data['attention_plan']]);
        $this->event($request, $internalRequest, 'ATENCION_INICIADA', 'RECIBIDA', 'EN_PROCESO', ['attention_plan' => $data['attention_plan']]);
        return $this->ok($internalRequest->fresh());
    }

    public function respond(Request $request, InternalRequest $internalRequest)
    {
        $this->requirePermission($request, self::PERMISSION_RESPOND);
        $this->requireDestination($request, $internalRequest);
        $data = $request->validate(['response_json' => ['required', 'array']]);
        if (! $this->validNode($data['response_json'])) abort(422, 'La respuesta contiene elementos no permitidos.');
        $responseText = $this->bodyText($data['response_json']);
        if ($responseText === '' || mb_strlen($responseText) > 20000) abort(422, 'La respuesta debe contener entre 1 y 20,000 caracteres.');
        if ($internalRequest->status !== 'EN_PROCESO') abort(409, 'Primero debe iniciar la atención antes de responder.');
        return DB::connection('mysql_gomezapp')->transaction(function () use ($request, $internalRequest, $data) {
            $responseText = $this->bodyText($data['response_json']);
            InternalRequestResponse::create(['internal_request_id' => $internalRequest->id, 'user_id' => $request->user()->id, 'response' => $responseText, 'response_text' => $responseText, 'response_json' => $data['response_json']]);
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
        $this->requirePermission($request, self::PERMISSION_REOPEN);
        $this->requireIssuer($request);
        if ($internalRequest->status !== 'CERRADA') abort(409, 'Solo se puede reabrir una solicitud cerrada.');
        $data = $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        return DB::connection('mysql_gomezapp')->transaction(function () use ($request, $internalRequest, $data) {
            $internalRequest->update(['status' => 'ENVIADA', 'received_at' => null, 'received_by' => null, 'started_at' => null, 'started_by' => null, 'attention_plan' => null, 'closed_at' => null, 'reopen_reason' => $data['reason']]);
            $this->createCycle($internalRequest);
            $this->event($request, $internalRequest, 'SOLICITUD_REABIERTA', 'CERRADA', 'ENVIADA', ['reason' => $data['reason']]);
            return $this->ok($internalRequest->fresh(['cycles']));
        });
    }

    public function destroyDraft(Request $request, InternalRequest $internalRequest)
    {
        $this->requireCrudPermission($request, 'delete');
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
        $this->requireDestination($request, $internalRequest);
        $remaining = 5 - $internalRequest->files()->where('kind', 'EVIDENCIA')->count();
        if ($remaining <= 0) abort(422, 'La solicitud ya tiene el máximo de cinco evidencias.');
        $request->validate(['files' => ['required', 'array', 'max:'.$remaining], 'files.*' => ['file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx', 'max:10240']]);
        $saved = [];
        foreach ($request->file('files') as $file) {
            $path = $file->store("internal-requests/{$internalRequest->id}", 'public');
            $saved[] = InternalRequestFile::create(['internal_request_id' => $internalRequest->id, 'user_id' => $request->user()->id, 'original_name' => $file->getClientOriginalName(), 'path' => $path, 'mime_type' => $file->getMimeType(), 'size' => $file->getSize()]);
        }
        $this->event($request, $internalRequest, 'EVIDENCIA_ADJUNTADA', $internalRequest->status, $internalRequest->status, ['files' => collect($saved)->pluck('original_name')]);
        return $this->ok($saved, 201);
    }

    public function uploadReferenceFile(Request $request, InternalRequest $internalRequest)
    {
        $this->requireIssuer($request);
        if ($internalRequest->created_by !== $request->user()->id && ! $this->hasCrudPermission($request, 'update')) abort(403, 'No tiene permiso para sustituir el oficio adjunto.');
        if ($internalRequest->status !== 'BORRADOR') abort(409, 'El oficio de referencia sólo puede modificarse en borrador.');
        $request->validate(['file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx', 'max:10240']]);
        $file = $request->file('file');
        $path = $file->store("internal-requests/{$internalRequest->id}/reference", 'public');
        $previous = $internalRequest->files()->where('kind', 'OFICIO_REFERENCIA')->get();
        $saved = InternalRequestFile::create([
            'internal_request_id' => $internalRequest->id, 'user_id' => $request->user()->id,
            'kind' => 'OFICIO_REFERENCIA', 'original_name' => $file->getClientOriginalName(),
            'path' => $path, 'mime_type' => $file->getMimeType(), 'size' => $file->getSize(),
        ]);
        $internalRequest->files()->where('kind', 'OFICIO_REFERENCIA')->whereKeyNot($saved->id)->delete();
        Storage::disk('public')->delete($previous->pluck('path')->all());
        $this->event($request, $internalRequest, 'OFICIO_REFERENCIA_ADJUNTADO', 'BORRADOR', 'BORRADOR', ['file' => $saved->original_name]);
        return $this->ok($saved, 201);
    }

    public function metrics(Request $request)
    {
        $query = InternalRequest::query(); $this->scopeVisible($request, $query);
        if ($request->filled('from')) $query->whereDate('created_at', '>=', $request->input('from'));
        if ($request->filled('to')) $query->whereDate('created_at', '<=', $request->input('to'));
        if ($request->filled('destination_department_id')) $query->where('destination_department_id', $request->input('destination_department_id'));
        $rows = $query->get();
        $closed = $rows->whereNotNull('first_responded_at');
        $departmentNames = Department::whereIn('id', $rows->pluck('destination_department_id')->unique())->pluck('department', 'id');
        $byDestination = $rows->groupBy('destination_department_id')->map(fn ($items, $id) => ['department_id' => $id, 'department' => $departmentNames[$id] ?? 'Sin departamento', 'total' => $items->count()])->values();
        $timed = $rows->map(fn ($r) => $this->withTiming($r));
        return $this->ok([
            'total' => $rows->count(), 'by_status' => $rows->countBy('status'), 'by_destination' => $byDestination,
            'by_decision' => $rows->whereNotNull('decision')->countBy('decision'),
            'monthly' => $rows->groupBy(fn ($row) => $row->created_at->format('Y-m'))->map(fn ($items, $month) => ['month' => $month, 'total' => $items->count(), 'closed' => $items->where('status', 'CERRADA')->count()])->sortKeys()->values(),
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
            'incoming_office_number' => ['required', 'string', 'max:80'],
            'incoming_office_at' => ['required', 'date'],
            'incoming_origin_department_id' => ['required', 'integer', Rule::exists('mysql_gomezapp.departments', 'id')->where('active', 1)],
            'incoming_destination_department_id' => ['required', 'integer', Rule::exists('mysql_gomezapp.departments', 'id')->where('active', 1)],
        ]);
        if (!$this->validNode($data['body_json'])) abort(422, 'La redacción contiene elementos no permitidos.');
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
    private function hasPermission(Request $request, string $permission): bool
    {
        if ($this->isSuperAdmin($request)) return true;
        $value = DB::connection('mysql_gomezapp')->table('roles')->where('id', $request->user()->role_id)->value('more_permissions');
        if ($value === 'todas') return true;
        return collect(explode(',', (string) $value))->map(fn ($item) => trim($item))->contains($permission);
    }
    private function menuId(): ?int { return DB::connection('mysql_gomezapp')->table('menus')->where('url', '/admin/solicitudes-internas')->where('type', 'item')->value('id'); }
    private function hasCrudPermission(Request $request, string $field): bool
    {
        if ($this->isSuperAdmin($request)) return true;
        $value = DB::connection('mysql_gomezapp')->table('roles')->where('id', $request->user()->role_id)->value($field);
        if ($value === 'todas') return true;
        return collect(explode(',', (string) $value))->map(fn ($item) => trim($item))->contains((string) $this->menuId());
    }
    private function requireCrudPermission(Request $request, string $field): void { if (! $this->hasCrudPermission($request, $field)) abort(403, 'No cuenta con permiso para realizar esta acción.'); }
    private function requirePermission(Request $request, string $permission): void { if (! $this->hasPermission($request, $permission)) abort(403, "No cuenta con el permiso: {$permission}."); }
    private function requireIssuer(Request $request): Department { $issuer = $this->issuerDepartment($request); if (!$issuer && !$this->isSuperAdmin($request)) abort(403, 'Solo Oficialía Mayor puede realizar esta acción.'); return $issuer ?: Department::where('can_issue_internal_requests', true)->firstOrFail(); }
    private function requireDestination(Request $request, InternalRequest $item): void { if (!$this->isSuperAdmin($request) && !$this->departmentIds($request)->contains($item->destination_department_id)) abort(403, 'La solicitud no corresponde a sus departamentos.'); }
    private function requireVisible(Request $request, InternalRequest $item): void { if (!$this->isSuperAdmin($request) && !$this->issuerDepartment($request) && (!$this->departmentIds($request)->contains($item->destination_department_id) || in_array($item->status, ['BORRADOR', 'NO_AUTORIZADA'], true))) abort(403); }
    private function scopeVisible(Request $request, $query): void { if (!$this->isSuperAdmin($request) && !$this->issuerDepartment($request)) $query->whereIn('destination_department_id', $this->departmentIds($request))->whereNotIn('status', ['BORRADOR', 'NO_AUTORIZADA']); }
    private function event(Request $request, InternalRequest $item, string $type, ?string $from, ?string $to, $metadata = null): void { InternalRequestEvent::create(['internal_request_id' => $item->id, 'user_id' => $request->user()->id, 'type' => $type, 'from_status' => $from, 'to_status' => $to, 'metadata' => $metadata]); }
    private function ok($result, int $status = 200) { return response()->json(['data' => ['status_code' => $status, 'status' => true, 'result' => $result]], $status); }
}

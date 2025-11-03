<?php

namespace App\Http\Controllers\GomezApp;

use App\Http\Controllers\Controller;
use App\Models\GomezApp\SParticular;  //ESTA ES LA TABLA
use App\Models\GomezApp\SpRequests;  //ESTA ES LA VISTA
use App\Models\GomezApp\ReporteIncumplimiento;
use App\Models\GomezApp\SolicitudesXEstatusView;
use App\Models\GomezApp\DepartmentStatusRequestView;
use App\Models\ObjResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SParticularController extends Controller
{


    public function index(Request $request, Response $response)
    {
        $data = SpRequests::all();
        return response()->json($data);
    }
    public function store(Request $request, Response $response)
    {

        $request->validate([
            'observaciones' => 'required|string',
            'calle' => 'required|string|max:255',
            'id_departamento_destino' => 'required',
            'id_asunto' => 'required'

        ]);

        // $longitud_cadena = 5;
        // $caracteres_alfabeticos = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        // $caracter_alfabetico = $caracteres_alfabeticos[mt_rand(0, strlen($caracteres_alfabeticos) - 1)];
        // $numeros = mt_rand(pow(10, $longitud_cadena - 2), pow(10, $longitud_cadena - 1) - 1);
        // $cadena_aleatoria = $caracter_alfabetico . $numeros;
        $nomenclatura = 'CC-SP';
        $registros = SParticular::all()->count();
        $folioSiguiente = $registros + 1;
        $numDocumento = $nomenclatura . "/" . $folioSiguiente . "/" . date('Y');

        try {
            $response->data = ObjResponse::DefaultResponse();
            $reports = new SParticular;
            $reports->fecha_solicitud = date('Y-m-d');
            $reports->folio =  $numDocumento;
            $reports->nombre =  $request->nombre;
            $reports->app =  $request->app;
            $reports->apm =  $request->apm;
            $reports->fecha_nacimiento =  $request->fecha_nacimiento;
            $reports->cargo =  $request->cargo;
            $reports->telefono =  $request->telefono;
            $reports->calle = $request->calle;
            $reports->num_ext = $request->num_ext;
            $reports->num_int = $request->num_int;
            $reports->cp = $request->cp;
            $reports->colonia = $request->colonia;
            $reports->localidad = $request->localidad;
            $reports->municipio = $request->municipio;
            $reports->estado = $request->estado;
            $reports->tipo_localidad = $request->tipo_localidad;
            $reports->id_departamento_destino = $request->id_departamento_destino;
            $reports->id_asunto = $request->id_asunto;
            $reports->tipo_documento = $request->tipo_documento;
            $reports->observaciones = $request->observaciones;
            $reports->id_user_create = $request->id_user_create;

            $reports->save();


            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | usuario eliminado.';
            $response->data["alert_text"] = "Respuesta enviada";
            $response->data["result"] = ["se registro respuesta" =>  $reports];
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    public function destroy(Request $request, Response $response, $id)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $deleteReport = SParticular::find($id);
            $deleteReport->active = 0;
            $deleteReport->deleted_at = now();
            $deleteReport->save();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | usuario eliminado.';
            $response->data["alert_text"] = "Usuario eliminado";
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    public function response(Request $request, Response $response, $id)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $responseRequest = SParticular::find($id);

            if ($responseRequest->respuesta == null) {
                $responseRequest->respuesta = $request->response;
                $responseRequest->respuesta_at = now();
                if ($request->tipoDocumento == 'CONOCIMIENTO') {
                    $responseRequest->completado = 1;
                    $responseRequest->completado_at = now();
                    $responseRequest->estatus = "COMPLETA";
                }
                if ($responseRequest->img_attach_1 != null) {
                    $responseRequest->completado = 1;
                    $responseRequest->completado_at = now();
                    if ($responseRequest->estatus == 'ALTA - FUERA DE TIEMPO') {
                        $responseRequest->estatus = "COMPLETA - FUERA DE TIEMPO";
                    } else {
                        $responseRequest->estatus = "COMPLETA";
                    }
                }
                $responseRequest->save();
            }


            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | Respuesta almacenada.';
            $response->data["alert_text"] = "Respuesta Almacenada";
            $response->data["result"] = $request->all();
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    public function show(Response $response, $id, $user_role_id)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = SpRequests::where("id", $id)->first();
            if ($user_role_id == 4 && $list->visto != 1) {
                $vistoReport = SParticular::find($id);
                $vistoReport->visto = 1;
                $vistoReport->visto_at = now();
                $vistoReport->save();
            }
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de mis reportes.';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    public function update(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        $idRequest = $request->id;
        try {
            $updateReport = SParticular::find($idRequest);
            $updateReport->nombre = $request->nombre;
            $updateReport->app = $request->app;
            $updateReport->apm = $request->apm;
            $updateReport->fecha_nacimiento =  $request->fecha_nacimiento;
            $updateReport->cargo =  $request->cargo;
            $updateReport->telefono = $request->telefono;
            $updateReport->cp = $request->cp;
            $updateReport->calle = $request->calle;
            $updateReport->num_ext = $request->num_ext;
            $updateReport->num_int = $request->num_int;
            $updateReport->colonia = $request->colonia;
            $updateReport->localidad = $request->localidad;
            $updateReport->municipio = $request->municipio;
            $updateReport->estado = $request->estado;
            $updateReport->id_departamento_destino = $request->id_departamento_destino;
            $updateReport->id_asunto = $request->id_asunto;
            $updateReport->observaciones = $request->observaciones;
            $updateReport->tipo_localidad = $request->tipo_localidad;
            $updateReport->tipo_documento = $request->tipo_documento;
            $updateReport->updated_at = now();
            $updateReport->save();


            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | usuario Actualizado.';
            $response->data["alert_text"] = "Usuario Actualizado";
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    public function requestviewByIdDep(Request $request, Response $response)
    {
        $data = $request->all();
        $array = array();
        foreach ($data as $key => $value) {
            $array[] = $value['departamento_id'];
        }
        $response = SpRequests::whereIn("id_departamento_destino", $array)->get();
        return response()->json($response);
    }
    public function attachImgs(Request $request, Response $response, $id)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {

            $responseRequest = SParticular::find($id);

            if ($responseRequest) {
                $img_attach_1 = $this->ImageUp($request, 'img_attach_1', $id, 'evidencia-1', false, "sinEvidencia.png");
                $img_attach_2 = $this->ImageUp($request, 'img_attach_2', $id, 'evidencia-2', false, "sinEvidencia.png");
                $img_attach_3 = $this->ImageUp($request, 'img_attach_3', $id, 'evidencia-3', false, "sinEvidencia.png");
                $img_attach_4 = $this->ImageUp($request, 'img_attach_4', $id, 'evidencia-4', false, "sinEvidencia.png");
                $img_attach_5 = $this->ImageUp($request, 'img_attach_5', $id, 'evidencia-5', false, "sinEvidencia.png");

                if ($request->hasFile('img_attach_1') || $request->img_attach_1 == "") $responseRequest->img_attach_1 = $img_attach_1;
                if ($request->hasFile('img_attach_2') || $request->img_attach_2 == "") $responseRequest->img_attach_2 = $img_attach_2;
                if ($request->hasFile('img_attach_3') || $request->img_attach_3 == "") $responseRequest->img_attach_3 = $img_attach_3;
                if ($request->hasFile('img_attach_4') || $request->img_attach_4 == "") $responseRequest->img_attach_4 = $img_attach_4;
                if ($request->hasFile('img_attach_5') || $request->img_attach_5 == "") $responseRequest->img_attach_5 = $img_attach_5;

                if ($responseRequest->img_attach_1 != null) {
                    if ($responseRequest->respuesta != null) {
                        $responseRequest->completado = 1;
                        $responseRequest->completado_at = now();
                        if ($responseRequest->estatus == 'ALTA - FUERA DE TIEMPO') {
                            $responseRequest->estatus = "COMPLETA - FUERA DE TIEMPO";
                        } else {
                            $responseRequest->estatus = "COMPLETA";
                        }
                    }
                }


                $responseRequest->save();
            }
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | Respuesta almacenada.';
            $response->data["alert_text"] = "Respuesta Almacenada";
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    public function stationeryImgs(Request $request, Response $response, $id)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {

            $responseRequest = SParticular::find($id);

            if ($responseRequest) {
                $img_stationery_1 = $this->imageUpStationery($request, 'img_stationery_1', $id, 'papeleria-1', false, "sinpapeleria.png");
                $img_stationery_2 = $this->imageUpStationery($request, 'img_stationery_2', $id, 'papeleria-2', false, "sinpapeleria.png");
                $img_stationery_3 = $this->imageUpStationery($request, 'img_stationery_3', $id, 'papeleria-3', false, "sinpapeleria.png");
                $img_stationery_4 = $this->imageUpStationery($request, 'img_stationery_4', $id, 'papeleria-4', false, "sinpapeleria.png");
                $img_stationery_5 = $this->imageUpStationery($request, 'img_stationery_5', $id, 'papeleria-5', false, "sinpapeleria.png");

                if ($request->hasFile('img_stationery_1') || $request->img_stationery_1 == "") $responseRequest->img_stationery_1 = $img_stationery_1;
                if ($request->hasFile('img_stationery_2') || $request->img_stationery_2 == "") $responseRequest->img_stationery_2 = $img_stationery_2;
                if ($request->hasFile('img_stationery_3') || $request->img_stationery_3 == "") $responseRequest->img_stationery_3 = $img_stationery_3;
                if ($request->hasFile('img_stationery_4') || $request->img_stationery_4 == "") $responseRequest->img_stationery_4 = $img_stationery_4;
                if ($request->hasFile('img_stationery_5') || $request->img_stationery_5 == "") $responseRequest->img_stationery_5 = $img_stationery_5;

                $responseRequest->save();
                $response->data = ObjResponse::CorrectResponse();
                $response->data["message"] = 'peticion satisfactoria | Respuesta almacenada.';
                $response->data["alert_text"] = "Respuesta Almacenada";
            }
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    private function imageUp($request, $requestFile, $id, $posFix, $create, $nameFake)
    {
        $dir_path = "ATC/sp-solicitudes";
        $dir = public_path($dir_path);
        $img_name = "";
        if ($request->hasFile($requestFile)) {
            $img_file = $request->file($requestFile);
            $instance = new Controller();
            $dir_path = "$dir_path/$id";
            $dir = "$dir/$id";
            $img_name = $instance->imgUpload($img_file, $dir, $dir_path, "$id-$posFix");
        } else {
            if ($create) $img_name = "$dir_path/$nameFake";
        }
        return $img_name;
    }

    private function imageUpStationery($request, $requestFile, $id, $posFix, $create, $nameFake)
    {
        $dir_path = "ATC/sp-solicitudes/papeleria";
        $dir = public_path($dir_path);
        $img_name = "";
        if ($request->hasFile($requestFile)) {
            $img_file = $request->file($requestFile);
            $instance = new Controller();
            $dir_path = "$dir_path/$id";
            $dir = "$dir/$id";
            $img_name = $instance->imgUpload($img_file, $dir, $dir_path, "$id-$posFix");
        } else {
            if ($create) $img_name = "$dir_path/$nameFake";
        }
        return $img_name;
    }
    public function getImg64()
    {
        $dir_path = "ATC/sp-solicitudes";
        $dir = public_path($dir_path);
        $rutaImagen = "$dir/1/1-evidencia-1.PNG";

        // if (File::exists($rutaImagen)) {
        $imagenBytes = file_get_contents($rutaImagen);
        $imagenBase64 = base64_encode($imagenBytes);

        return response()->json(['base64Image' => $imagenBase64]);
        // } else {
        //     return response()->json(['error' => 'Imagen no encontrada'], 404);
        // }
    }
    public function getIncumplimiento()
    {
        $data = ReporteIncumplimiento::all();
        return response()->json($data);
    }
    public function changeIncumplimiento(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {

            $incumplimientoReport = SParticular::find($request->idReporte);
            $incumplimientoReport->estatus =  $request->titulo;
            $incumplimientoReport->save();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria ';
            $response->data["result"] = $incumplimientoReport;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    public function solicitudesxestatus(Response $response)
    {


        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = SolicitudesXEstatusView::all();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de mis reportes.';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    public function requestdepartmentstatus(Response $response)
    {


        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = DepartmentStatusRequestView::all();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de mis reportes.';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
}

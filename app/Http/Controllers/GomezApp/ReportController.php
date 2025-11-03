<?php

namespace App\Http\Controllers\GomezApp;

use App\Http\Controllers\Controller;
use App\Models\GomezApp\Report;
use App\Models\GomezApp\ReportView;
use App\Models\GomezApp\InfoCards;
use App\Models\GomezApp\ReportAsuntos;
use App\Models\GomezApp\ResponseR;
use App\Models\User;
use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Js;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function index(Response $response)
    {

        $response = Report::all();
        return response()->json($response);
    }
    public function getReportsSP_Movil(Response $response, Request $request)
    {
        $token = env('API_TOKEN');
        $tokenIn = $request->bearerToken();

        if ($token == $tokenIn) {
            $response = ReportView::where('id_departamento', 15)->get();
            return response()->json($response);
        } else {

            return response()->json(['response' => 'Token Invalido'], 401);
        }
    }
    public function postReportsSP_Movil(Response $response, Request $request)
    {
        $token = env('API_TOKEN');
        if ($request->token == $token) {
            return response()->json(['data' => $request->input(), 'response' => 'Token Valido']);
        } else {
            return response()->json(['response' => 'Token Invalido'], 401);
        }
    }
    public function reportsview(Response $response)
    {
        $response = ReportView::all();
        return response()->json($response);
    }
    public function reportsviewById(Request $request, Response $response)
    {
        $data = $request->all();
        $array = array();
        foreach ($data as $key => $value) {
            $array[] = $value['departamento_id'];
        }
        $response = ReportView::whereIn("id_departamento", $array)->get();
        return response()->json($response);
    }
    public function getCards(Response $response)
    {
        $response = InfoCards::all();
        return response()->json($response);
    }
    public function saveReport(Request $request, Response $response)
    {
        $latitud = "";
        $longitud = "";
        $lat = $request->latitud == "" ? "" : $request->latitud;
        $long = $request->longitud == "" ? "" : $request->longitud;
        $url = "";
        $pattern = "/[^0-9.-]/";
        if ($request->url != "") {
            $url = explode("@", $request->url);
            if ($url[0] == $request->url) {
                $url = "";
            } else {
                $url2 = explode(",", $url[1]);
                $lat = $url2[0];
                $long = $url2[1];
            }
        }

        $longitud_cadena = 5;
        $caracteres_alfabeticos = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $caracter_alfabetico = $caracteres_alfabeticos[mt_rand(0, strlen($caracteres_alfabeticos) - 1)];
        $numeros = mt_rand(pow(10, $longitud_cadena - 2), pow(10, $longitud_cadena - 1) - 1);
        $cadena_aleatoria = $caracter_alfabetico . $numeros;

        $response->data = ObjResponse::DefaultResponse();
        $result = [];
        try {
            $imgName = "";
            if ($request->hasFile('imgFile')) {
                $image = $request->file('imgFile');
                $imgName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('GomezApp/reportes'), $imgName);
            }


            if ($request->has('op')) {

                if ($request->op === "1") {
                    $users = new User;
                    $users->email = $request->correo;
                    $users->password = Hash::make('123456');
                    $users->role_id = 3;
                    $users->phone = $request->telefono;
                    $users->name = $request->nombre;
                    $users->paternal_last_name = $request->app;
                    $users->maternal_last_name = $request->apm;
                    $users->curp = $request->curp;
                    $users->sexo = $request->genero;
                    $users->save();


                    $reports = new Report;
                    $reports->id_user_create = $request->id_user_create;
                    $reports->fecha_reporte = $request->fecha;
                    $reports->folio =  $cadena_aleatoria;
                    $reports->id_user = $users->id;
                    $reports->calle = $request->calle;
                    $reports->num_ext = $request->num_ext;
                    $reports->num_int = $request->num_int;
                    $reports->cp = $request->cp;
                    $reports->colonia = $request->colonia;
                    $reports->localidad = $request->ciudad;
                    $reports->municipio = "";
                    $reports->latitud = $lat;
                    $reports->longitud = $long;
                    $reports->estado = $request->estado;
                    $reports->id_departamento = $request->id_departamento;
                    $reports->id_origen = $request->origen;
                    $reports->id_estatus = 1;
                    $reports->community_id = $request->community_id;
                    $reports->created_at = now();
                    $reports->save();



                    $reportsAsunt = new ReportAsuntos();
                    $reportsAsunt->id_reporte = $reports->id;
                    $reportsAsunt->id_servicio = $request->tipoServicio;
                    $reportsAsunt->id_asunto = $request->id_asunto;
                    $reportsAsunt->observaciones = $request->observaciones;
                    $reportsAsunt->save();


                    $response->data = ObjResponse::CorrectResponse();
                    $response->dara["result"] = $result;
                    $response->data["message"] = 'Peticion satisfactoria | Lista de mis reportes.';
                } else if ($request->op === "2") {
                    $idReport = $request->id;
                    $idUser = $request->idUser;

                    $users = User::find($idUser);
                    $users->email = $request->correo;
                    $users->phone = $request->telefono;
                    $users->name = $request->nombre;
                    $users->paternal_last_name = $request->app;
                    $users->maternal_last_name = $request->apm;
                    $users->curp = $request->curp;
                    $users->sexo = $request->genero;
                    $users->save();

                    $reports = Report::find($request->id);
                    $reports->fecha_reporte = $request->fecha;
                    $reports->calle = $request->calle;
                    $reports->num_ext = $request->num_ext;
                    $reports->num_int = $request->num_int;
                    $reports->cp = $request->cp;
                    $reports->colonia = $request->colonia;
                    $reports->localidad = $request->ciudad;
                    $reports->estado = $request->estado;
                    $reports->id_departamento = $request->id_departamento;
                    $reports->id_origen = $request->origen;
                    $reports->latitud = $lat;
                    $reports->longitud = $long;
                    $reports->updated_at = now();
                    $reports->save();



                    $reportsAsunt = ReportAsuntos::where("id_reporte", "=", $idReport)
                        ->update([
                            'observaciones' => $request->observaciones,
                            'id_servicio' => $request->tipoServicio,
                            'id_asunto' => $request->id_asunto
                        ]);


                    $response->data = ObjResponse::CorrectResponse();
                    $response->data["result"] = ["se actualizo correctamente" => $reportsAsunt];
                    $response->data["message"] = 'Peticion satisfactoria | Lista de mis reportes.';
                }
            } else {
                $reports = new Report;
                $reports->fecha_reporte = $request->fecha_reporte;
                $reports->img_reporte = "GomezApp/reportes/$imgName";
                $reports->folio = $request->folio;
                $reports->latitud = $request->latitud;
                $reports->longitud = $request->longitud;
                $reports->id_user = $request->id_user;
                $reports->id_departamento = $request->id_departamento;
                $reports->id_estatus = 1;
                $reports->id_origen = 3;
                $reports->referencias = $request->referencias;
                $reports->created_at = now();
                $reports->save();


                $reportsAsunt2 = new ReportAsuntos();
                $reportsAsunt2->id_reporte = $reports->id;
                $reportsAsunt2->id_servicio = 1;
                $reportsAsunt2->id_asunto = $request->id_asunto;
                $reportsAsunt2->observaciones = $request->comentario;
                $reportsAsunt2->save();

                $response->data = ObjResponse::CorrectResponse();
                $response->data["message"] = 'Peticion satisfactoria | Lista de mis reportes.';
            }
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    public function destroy(int $id, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $deleteReport = Report::find($id);
            $deleteReport->active = 0;
            $deleteReport->deleted_at = now();
            $deleteReport->save();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria';
            $response->data["alert_text"] = "Usuario eliminado";
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    public function deleteResponse(Response $response, int $id)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {

            $deleteResponse = ResponseR::where("id_reporte", $id)->delete();

            $reportFind = Report::find($id);
            $reportFind->id_estatus = 1;
            $reportFind->img_attach_1 = null;
            $reportFind->img_attach_2 = null;
            $reportFind->img_attach_3 = null;
            $reportFind->img_attach_4 = null;
            $reportFind->img_attach_5 = null;
            $reportFind->save();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria';
            $response->data["alert_text"] = "Usuario eliminado";
            $response->data["result"] = $deleteResponse;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    public function saveResponse(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        $id = (int)$request->idReport;
        try {
            $exiteRespuesta = ResponseR::where('id_reporte', $id)->first();
            if ($exiteRespuesta) {
                $exiteRespuesta->delete();
            }
            $responseR = new ResponseR;
            $responseR->id_reporte = $request->idReport;
            $responseR->respuesta = $request->response;
            $responseR->fecha_respuesta = now();
            $responseR->save();

            $updateEstatus = Report::find($id);
            $updateEstatus->id_estatus = $request->estatus;
            $updateEstatus->save();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria';
            $response->data["alert_text"] = "Respuesta enviada";
            $response->data["result"] = ["se registro respuesta" =>  $request->idReport];
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    /**
     * Mostrar lista de reportes activos por usuario.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function reportsByUser(Request $request, Response $response, $id_user)
    {

        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = ReportView::where("id_user", $id_user)->get();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de mis reportes.';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
    public function reportsById(Response $response, $id)
    {

        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = ReportView::where("id", $id)->first();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de mis reportes.';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    public function attachImgs(Request $request, Response $response, $id)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {

            $responseRequest = Report::find($id);

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
    private function imageUp($request, $requestFile, $id, $posFix, $create, $nameFake)
    {
        $dir_path = "ATC/atc-solicitudes";
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

    public function show(Response $response, $id)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = Report::where("id", $id)->first();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de mis reportes.';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    public function validarEvidencia(Response $response, $id)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = Report::where("id", $id)->first();
            if (!$list->img_attach_1) {
                $response->data = ObjResponse::CorrectResponse();
                $response->data["message"] = 'Peticion satisfactoria | ValidarRespuesta.';
                $response->data["result"] = [];
            } else {
                $response->data = ObjResponse::CorrectResponse();
                $response->data["message"] = 'Peticion satisfactoria | ValidarRespuesta.';
                $response->data["result"] = $list;
            }
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    public function saveResponseSP_Movil(Request $request, Response $response)
    {
        $token = env('API_TOKEN');
        if ($request->token == $token) {
            $response->data = ObjResponse::DefaultResponse();
            $id = (int)$request->idReport;
            try {
                $validarEvidencia = Report::find($id);
                if ($validarEvidencia->img_attach_1 || $request->estatus == 2) {
                    $exiteRespuesta = ResponseR::where('id_reporte', $id)->first();
                    if ($exiteRespuesta) {
                        $exiteRespuesta->delete();
                    }
                    $responseR = new ResponseR;
                    $responseR->id_reporte = $request->idReport;
                    $responseR->respuesta = $request->response;
                    $responseR->fecha_respuesta = now();
                    $responseR->save();

                    $updateEstatus = Report::find($id);
                    $updateEstatus->id_estatus = $request->estatus;
                    $updateEstatus->save();

                    $response->data = ObjResponse::CorrectResponse();
                    $response->data["message"] = 'peticion satisfactoria';
                    $response->data["alert_text"] = "Respuesta enviada";
                    $response->data["result"] = ["se registro respuesta" =>  $request->idReport];
                } else {
                    $response->data = ObjResponse::CorrectResponse();
                    $response->data["message"] = 'peticion satisfactoria';
                    $response->data["alert_text"] = "TE FALTA EVIDENCIA PARA ESTA OPCION";
                    $response->data["result"] = [];
                }
            } catch (\Exception $ex) {
                $response->data = ObjResponse::CatchResponse($ex->getMessage());
            }
            return response()->json($response, $response->data["status_code"]);
        } else {
            return response()->json(['response' => 'Token Invalido'], 401);
        }
    }
}

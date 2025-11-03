<?php

namespace App\Http\Controllers\GomezApp;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Models\GomezApp\Asuntos;
use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class appController extends Controller
{
    /**
     * Mostrar lista de asuntos activas.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function index(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = Asuntos::where('active', true)
                ->orderBy('asuntos.id', 'asc')->get();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de asuntos.';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Mostrar listado para un selector.
     *
     * @return \Illuminate\Http\Response $response
     */
    public function selectIndex(Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $list = Asuntos::where('active', true)
                ->select('asuntos.id as value', 'asuntos.asunto as text')
                ->orderBy('asuntos.asunto', 'asc')->get();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de asuntos';
            $response->data["result"] = $list;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }


    /**
     * Crear o Actualizar un asunto.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function createOrUpdate(Request $request, Response $response, Int $id = null)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $asunto = Asuntos::find($id);
            if (!$asunto) $asunto = new Asuntos();

            $asunto->asunto = $request->asunto;
            $asunto->bg_circle = $request->bg_circle;
            $asunto->bg_card = $request->bg_card;
            $asunto->letter_black = $request->letter_black;
            $asunto->app = $request->app == 1 ? "1" : "0";

            $asunto->save();

            $icono = $this->ImageUp($request, 'icono', $asunto->id, 'btn', true, "noImage.png");

            if ($request->hasFile('icono'))
                if ($icono != "") $asunto->icono = $icono;
            $asunto->save();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = $id > 0 ? 'peticion satisfactoria | asunto editado.' : 'satisfactoria | asunto registrado.';
            $response->data["alert_text"] = $id > 0 ? 'Asunto editado' : 'Asunto registrado';
            $response->data["result"] = $asunto;
            // return $asunto;
        } catch (\Exception $ex) {
            $msg =  "Error al crear o actualizar asunto: " . $ex->getMessage();
            error_log("$msg");
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
            // return $msg;
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Mostrar asunto.
     *
     * @param   int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function show(Request $request, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            $asunto = Asuntos::find($request->id);

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | asunto encontrado.';
            $response->data["result"] = $asunto;
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Eliminar (cambiar estado activo=false) asunto.
     *
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     */
    public function destroy(Int $id, Response $response)
    {
        $response->data = ObjResponse::DefaultResponse();
        try {
            Asuntos::find($id)
                ->update([
                    'active' => false,
                    'deleted_at' => date('Y-m-d H:i:s'),
                ]);
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria | asunto eliminado.';
            $response->data["alert_text"] = 'Departamento eliminado';
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }


    private function ImageUp($request, $requestFile, $id, $posFix, $create, $nameFake)
    {
        $dir_path = "GomezApp/icons";
        $dir = public_path($dir_path);
        $img_name = "";
        if ($request->hasFile($requestFile)) {
            $img_file = $request->file($requestFile);
            $instance = new UserController();
            // $dir_path = "$dir_path/$id";
            // $dir = "$dir/$id";
            $img_name = $instance->ImgUpload($img_file, $dir, $dir_path, "$id-$posFix");
        } else {
            if ($create) $img_name = "$dir_path/$nameFake";
        }
        return $img_name;
    }
}

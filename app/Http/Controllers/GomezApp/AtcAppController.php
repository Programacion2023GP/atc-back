<?php

namespace App\Http\Controllers\GomezApp;

use App\Http\Controllers\Controller;
use App\Models\GomezApp\Report;
use App\Models\GomezApp\ReportAsuntos;
use App\Models\GomezApp\ResponseR;
use App\Models\ObjResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AtcAppController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return "ENTRA POR FAVOR";
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, Response $response)
    {

        // $latitud = "";
        // $longitud = "";
        // $lat = $request->latitud == "" ? "" : $request->latitud;
        // $long = $request->longitud == "" ? "" : $request->longitud;
        // $url = "";
        // $pattern = "/[^0-9.-]/";
        // if ($request->url != "") {
        //     $url = explode("@", $request->url);
        //     if ($url[0] == $request->url) {
        //         $url = "";
        //     } else {
        //         $url2 = explode(",", $url[1]);
        //         $lat = $url2[0];
        //         $long = $url2[1];
        //     }
        // }

        // $longitud_cadena = 5;
        // $caracteres_alfabeticos = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        // $caracter_alfabetico = $caracteres_alfabeticos[mt_rand(0, strlen($caracteres_alfabeticos) - 1)];
        // $numeros = mt_rand(pow(10, $longitud_cadena - 2), pow(10, $longitud_cadena - 1) - 1);
        // $cadena_aleatoria = $caracter_alfabetico . $numeros;

        // $response->data = ObjResponse::DefaultResponse();
        // $result = [];
        // try {
        //     $imgName = "";
        //     if ($request->hasFile('imgFile')) {
        //         $image = $request->file('imgFile');
        //         $imgName = time() . '.' . $image->getClientOriginalExtension();
        //         $image->move(public_path('GomezApp/appEvidencias'), $imgName);
        //     }


        //     $reports = new Report;
        //     $reports->fecha_reporte = $request->fecha_reporte;
        //     $reports->img_reporte = "GomezApp/appEvidencias/$imgName";
        //     $reports->folio = $cadena_aleatoria;
        //     $reports->latitud = $request->latitud;
        //     $reports->longitud = $request->longitud;
        //     $reports->id_user = $request->id_user;
        //     $reports->id_departamento = $request->id_departamento;
        //     $reports->id_estatus = 1;
        //     $reports->id_origen = 3;
        //     $reports->referencias = $request->referencias;
        //     $reports->created_at = now();
        //     $reports->save();


        //     $reportsAsunt2 = new ReportAsuntos();
        //     $reportsAsunt2->id_reporte = $reports->id;
        //     $reportsAsunt2->id_servicio = 1;
        //     $reportsAsunt2->id_asunto = $request->id_asunto;
        //     $reportsAsunt2->observaciones = $request->comentarios;
        //     $reportsAsunt2->save();

        $response->data = ObjResponse::CorrectResponse();
        $response->data["message"] = 'Peticion satisfactoria | Lista de mis reportes.';
        $response->data["result"] = $request->all();
        // } catch (\Exception $ex) {
        //     $response->data = ObjResponse::CatchResponse($ex->getMessage());
        // }
        return response()->json($response, $response->data["status_code"]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Response $response) {}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function saveAppReports(Request $request, Response $response)
    {
        // $request->validate([
        //     'imgFile' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:10240', // máximo 10MB
        // ]);

        $longitud_cadena = 5;
        $caracteres_alfabeticos = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $caracter_alfabetico = $caracteres_alfabeticos[mt_rand(0, strlen($caracteres_alfabeticos) - 1)];
        $numeros = mt_rand(pow(10, $longitud_cadena - 2), pow(10, $longitud_cadena - 1) - 1);
        $cadena_aleatoria = "APP-" . $caracter_alfabetico . $numeros;

        $response->data = ObjResponse::DefaultResponse();
        try {

            $imgName = "";
            if ($request->hasFile('imgFile')) {
                $image = $request->file('imgFile');
                $imgName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('GomezApp/appEvidencias'), $imgName);
            }

            $dataLocation = json_decode($request->input('dataLocation'), true);
            // $dataLocation = $request->input('dataLocation');

            $cp = $dataLocation['ubication']['postalCode'] ?? null;
            $calle = $dataLocation['ubication']['street'] ?? null;
            $num_ext = $dataLocation['ubication']['streetNumber'] ?? null;
            $colonia = $dataLocation['ubication']['district'] ?? null;

            $reports = new Report;
            $reports->fecha_reporte = $request->fecha_reporte;
            $reports->img_reporte = "GomezApp/appEvidencias/$imgName";
            $reports->folio = $cadena_aleatoria;
            $reports->latitud = $request->latitud;
            $reports->longitud = $request->longitud;
            $reports->id_user = $request->id_user;
            $reports->cp = $cp;
            $reports->calle = $calle;
            $reports->num_ext = $num_ext;
            $reports->colonia = $colonia;
            $reports->id_departamento = $request->id_departamento;
            $reports->id_estatus = 1;
            $reports->id_user_create = $request->id_user;
            $reports->id_origen = 3;
            $reports->referencias = $request->referencias;
            $reports->created_at = now();
            $reports->save();


            $reportsAsunt2 = new ReportAsuntos();
            $reportsAsunt2->id_reporte = $reports->id;
            $reportsAsunt2->id_servicio = 1;
            $reportsAsunt2->id_asunto = $request->id_asunto;
            $reportsAsunt2->id_jornada = $request->id_jornada ?? null;
            $reportsAsunt2->observaciones = $request->comentarios;
            $reportsAsunt2->save();


            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria | Lista de mis reportes.';
            $response->data["result"] = [];
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
}

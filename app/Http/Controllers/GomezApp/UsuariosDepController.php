<?php

namespace App\Http\Controllers\GomezApp;

use App\Http\Controllers\Controller;
use App\Models\GomezApp\UsuariosDep;
use App\Models\ObjResponse;
use App\Models\GomezApp\UserDepTable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UsuariosDepController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $usuriosDep = UsuariosDep::all();
        return $usuriosDep;
    }

    public function indexById($id)
    {
        $usuriosDepById = UsuariosDep::where('user_id', $id)->get();
        return $usuriosDepById;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Response $response)
    {

        try {
            $request->validate([
                'department_id' => 'required',
                'user_id' => 'required'
            ]);

            $usurioDep = new UserDepTable();
            $usurioDep->departamento_id = $request->department_id;
            $usurioDep->user_id = $request->user_id;
            $usurioDep->save();

            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'peticion satisfactoria';
            $response->data["alert_text"] = 'Se Guardo Correctamente';
            $response->data["result"] = $usurioDep;

            return response()->json($response, $response->data["status_code"]);
        } catch (\Exception $error) {
            return $error->getMessage();
        }
    }

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
    public function destroy(Response $response, $id)
    {

        try {
            $response->data = ObjResponse::DefaultResponse();

            $destroy = UserDepTable::where('id', $id)->first();
            // return $destroy;
            $destroy->delete();
            $response->data = ObjResponse::CorrectResponse();
            $response->data["message"] = 'Peticion satisfactoria';
            $response->data["result"] = $destroy;
            return response()->json($response, $response->data["status_code"]);
        } catch (\Exception $ex) {
            $response->data = ObjResponse::CatchResponse($ex->getMessage());
        }
        return response()->json($response, $response->data["status_code"]);
    }
}

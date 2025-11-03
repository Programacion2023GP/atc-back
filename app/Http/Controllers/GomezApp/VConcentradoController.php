<?php

namespace App\Http\Controllers\GomezApp;

use App\Http\Controllers\Controller;
use App\Models\GomezApp\ConcentradoViewAtc;
use Illuminate\Http\Request;

class VConcentradoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = ConcentradoViewAtc::all();
        return response()->json($data);
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
    public function store(Request $request)
    {
        try {
            $consulta = ConcentradoViewAtc::query();

            if ($request->filled('nameDepartamento')) {
                $consulta->where('department', $request->nameDepartamento);
            }

            if ($request->filled('nameAsunto')) {
                $consulta->where('asunto', $request->nameAsunto);
            }

            if ($request->filled('dateInicial') && $request->filled('dateFinal')) {
                $consulta->whereBetween('fecha_reporte', [$request->dateInicial, $request->dateFinal]);
            } elseif ($request->filled('dateInicial')) {
                $consulta->where('fecha_reporte', $request->dateInicial);
            }

            $resultados = $consulta->get();
            $ResultadoAgrupado = [];
            $TotalPeticionesGeneral = 0;
            $TotalPendientesGeneral = 0;

            foreach ($resultados as $registro) {
                $key = $registro->department;
                $keyAsunto = $registro->asunto;

                if (!isset($ResultadoAgrupado[$key])) {
                    $ResultadoAgrupado[$key] = [];
                    $ResultadoAgrupado[$key]['TotalPeticiones'] = 0;
                    $ResultadoAgrupado[$key]['TotalTERMINADO'] = 0;
                    $ResultadoAgrupado[$key]['TotalNO_PROCEDE'] = 0;
                    $ResultadoAgrupado[$key]['TotalEN_TRAMITE'] = 0;
                    $ResultadoAgrupado[$key]['TotalTOTAL'] = 0;
                    $ResultadoAgrupado[$key]['TotalPENDIENTES'] = 0;
                }

                if (!isset($ResultadoAgrupado[$key][$keyAsunto])) {
                    $ResultadoAgrupado[$key][$keyAsunto] = [
                        'department' => $registro->department,
                        'asunto' => $registro->asunto,
                        'PETICIONES' => $registro->PETICIONES,
                        'TERMINADO' => $registro->TERMINADO,
                        'NO_PROCEDE' => $registro->NO_PROCEDE,
                        'EN_TRAMITE' => $registro->EN_TRAMITE,
                        'TOTAL' => $registro->TOTAL,
                        'PENDIENTES' => $registro->PENDIENTES,
                    ];
                } else {
                    $ResultadoAgrupado[$key][$keyAsunto]['PETICIONES'] += $registro->PETICIONES;
                    $ResultadoAgrupado[$key][$keyAsunto]['TERMINADO'] += $registro->TERMINADO;
                    $ResultadoAgrupado[$key][$keyAsunto]['NO_PROCEDE'] += $registro->NO_PROCEDE;
                    $ResultadoAgrupado[$key][$keyAsunto]['EN_TRAMITE'] += $registro->EN_TRAMITE;
                    $ResultadoAgrupado[$key][$keyAsunto]['TOTAL'] += $registro->TOTAL;
                    $ResultadoAgrupado[$key][$keyAsunto]['PENDIENTES'] += $registro->PENDIENTES;
                }
                $ResultadoAgrupado[$key]['TotalPeticiones'] += $registro->PETICIONES;
                $ResultadoAgrupado[$key]['TotalTERMINADO'] += $registro->TERMINADO;
                $ResultadoAgrupado[$key]['TotalNO_PROCEDE'] += $registro->NO_PROCEDE;
                $ResultadoAgrupado[$key]['TotalEN_TRAMITE'] += $registro->EN_TRAMITE;
                $ResultadoAgrupado[$key]['TotalTOTAL'] += $registro->TOTAL;
                $ResultadoAgrupado[$key]['TotalPENDIENTES'] += $registro->PENDIENTES;
                $TotalPeticionesGeneral += $registro->PETICIONES;
                $TotalPendientesGeneral += $registro->PENDIENTES;
            }
            $ResultadoAgrupado['TotalPeticionesGeneral'] = $TotalPeticionesGeneral;
            $ResultadoAgrupado['TotalPendientesGeneral'] = $TotalPendientesGeneral;


            return response()->json([
                'success' => true,
                'data' => $ResultadoAgrupado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud',
                'error' => $e->getMessage()
            ], 500);
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
    public function destroy($id)
    {
        //
    }
}

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;




#region CONTROLLERS INGRESOS


#endregion CONTROLLERS INGRESOS


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::api('usuario',[UsuarioController::class,'index']);




Route::prefix('gomezapp')->group(function () {
    Route::get('/', function () {
        return 'API GomezApp';
    });
    include_once "gomezapp.routes.php";
});

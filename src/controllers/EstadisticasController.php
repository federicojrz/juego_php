<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Models\EstadisticasModel;

class EstadisticasController{

    public static function estadisticas(Request $request, Response $response){
        try{
            $resultados=EstadisticasModel::estadisticas();
            $response->getBody()->write(json_encode($resultados));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        }catch(\Exception $e){
            $error = ['error' => 'No se pudieron obtener las estadísticas', 'detalle' => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
        
    }

}
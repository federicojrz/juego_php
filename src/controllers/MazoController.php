<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\UserModel;
use App\Models\MazoModel;
    


class MazoController{

    public static function crearMazo(Request $request, Response $response){
        $datos=$request->getParsedBody();//deberia recibir un nombre de mazo y un array de 5 id de cartas
        $nombre=$datos['nombre'];
        $cartas=$datos['cartas'];
        $cartasPorMazo = 5;
        $mazosPermitidos=3;
        $usuarioId=$request->getAttribute('id'); //obtengo el id de usuario desde lo que mandó el token

        if (empty($nombre) || !is_array($cartas) || count($cartas)!=$cartasPorMazo){
            $response->getBody()->write(json_encode(['error' => 'Debe enviar un nombre de mazo y exactamente 5 cartas']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        if (count(array_unique($cartas))!= $cartasPorMazo){
            $response->getBody()->write(json_encode(['error' => 'No se pueden repetir cartas']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $cantMazos = MazoModel::contarMazos($usuarioId);
            if ($cantMazos == $mazosPermitidos){
                $response->getBody()->write(json_encode(['error' => 'Máximo 3 mazos permitidos']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

        foreach ($cartas as $cartaId) {
            if (!MazoModel::existeCarta($cartaId)) {
                $response->getBody()->write(json_encode(['error' => "Carta $cartaId no existe"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
        }

        try{
            $mazoId = MazoModel::altaMazo($usuarioId,$nombre,$cartas);
        
            $response->getBody()->write(json_encode(['id' => $mazoId,'nombre' => $nombre]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        }catch(\Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'No se pudo crear el mazo']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
    }
}

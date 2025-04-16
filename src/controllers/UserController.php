<?php
namespace App\Controllers;
require_once __DIR__ . '/../Models/UserModel.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\UserModel;

class UserController{
    public static function registro(Request $request, Response $response){
        $datos = $request->getParsedBody();
        $nombre = $datos['nombre'];
        $usuario = $datos['usuario'];
        $password = $datos['password'];

    if (empty($usuario) || empty($password) || empty($nombre)) {
        $error = ['error' => 'Faltan campos obligatorios'];
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    if (strlen($usuario) < 6 || strlen($usuario) > 20 || !ctype_alnum($usuario)){
        $respuesta = ['error' => 'El nombre de usuario debe ser alfanumérico y tener entre 6 y 20 caracteres'];
        $response->getBody()->write(json_encode($respuesta));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Validaciones de clave
    if (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||     // al menos una mayúscula
        !preg_match('/[a-z]/', $password) ||     // al menos una minúscula
        !preg_match('/[0-9]/', $password) ||     // al menos un número
        !preg_match('/[\W_]/', $password)        // al menos un carácter especial
    ) {
        $respuesta = ['error' => 'La clave debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas, números y caracteres especiales'];
        $response->getBody()->write(json_encode($respuesta));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $resultado = UserModel::registrar($nombre,$usuario,$password);

    $response->getBody()->write(json_encode($resultado));
    return $response;
    }

    public static function getUser(Request $request, Response $response){
        
        $resultado = UserModel::mostrar();

        $response->getBody()->write(json_encode($resultado));
        return $response->withHeader('Content-Type', 'application/json');

    }
}

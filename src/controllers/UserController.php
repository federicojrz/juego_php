<?php
namespace App\Controllers;
require_once __DIR__ . '/../Models/UserModel.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\UserModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Middleware;

class UserController{

    protected static function validarCampos(array $datos):array{ //valida si usuario y contraseña cumplen condiciones (usuario y contraseña o solo contraseña)
        $errores = [];
        
        if (isset($datos['usuario'])){
            $usuario = $datos['usuario'];
            if (strlen($usuario) < 6 || strlen($usuario) > 20 || !ctype_alnum($usuario)){
                $errores['usuario']='Debe tener entre 6 y 20 caracteres alfanumericos';
            }
        }

        if (isset($datos['password'])){    
            $password = $datos['password'];
            if (
                strlen($password) < 8 ||
                !preg_match('/[A-Z]/', $password) ||     // al menos una mayúscula
                !preg_match('/[a-z]/', $password) ||     // al menos una minúscula
                !preg_match('/[0-9]/', $password) ||     // al menos un número
                !preg_match('/[\W_]/', $password)        // al menos un carácter especial
                ){
                    $errores['password']='La clave debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas, números y caracteres especiales';
                }
            }
             return $errores;
    }

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

        $errores = self::validarCampos($datos); //devuelve posibles errores en un array

        if (empty($errores)){ //si el array de errores esta vacio
            $resultado = UserModel::registrar($nombre,$usuario,$password);
            $statusCode=200;
        }else{
            $resultado = $errores;
            $statusCode = 400;
        }

        $response->getBody()->write(json_encode($resultado));       
        return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
    }

  /*  public static function getUser(Request $request, Response $response){ //PRUEBA retorna todos los usuarios
        
        $resultado = UserModel::mostrar();

        $response->getBody()->write(json_encode($resultado));
        return $response->withHeader('Content-Type', 'application/json');

    }
        */


    public static function login(Request $request, Response $response){
        $datos = $request->getParsedBody(); //guardo usuario y password en $datos
        $usuario = $datos['usuario'];
        $password = $datos['clave'];

        var_dump($usuario);

        if (empty($usuario) || empty($password)) { //chequo de campos vacios
            $error = ['error' => 'Faltan campos obligatorios'];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $existe = UserModel::validarUsuario($usuario,$password); //$existe devuelve el id de usuario
        if ($existe){            
            $clave_secreta = "mi_clave_super_secreta"; // clave con la que le servidor valida el token
            $ahora = time();
            $payload = [
                "iat" => $ahora, // emitido en
                "exp" => $ahora + 3600, // expira en 1 hora
                "usuario" => $usuario,
                "id"=>$existe
            ];

            $token = JWT::encode($payload, $clave_secreta, 'HS256');

            $vencimiento = date('Y-m-d H:i:s', $ahora + 3600);

            $ok = UserModel::actualizarToken($usuario,$token,$vencimiento);

            if ($ok){
                $respuesta=['Mensaje'=>'Inicio de sesion','token'=>$token];
                $status=200;
            }else {
                $respuesta = ['Error' => 'No se pudo guardar el token'];
                $status = 500;
            }         

        }else{
            $respuesta = ['Error'=> 'Usuario o contraseña incorrectos'];
            $status=400;           
          }

        $response->getBody()->write(json_encode($respuesta));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);

    }

    public static function getUser(Request $request, Response $response){
        $usuario = $request->getAttribute('usuario');
        $respuesta = UserModel::mostrarUsuario($usuario);
        $respuesta['usuario']=$usuario;

        $response->getBody()->write(json_encode($respuesta));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function updateUser(Request $request, Response $response){
        $datos = $request->getParsedBody();
        $nombreNuevo = $datos['nombre'];
        $passNuevo = $datos['password'];
        $usuario = $request->getAttribute('usuario');

        $errores = self::validarCampos(['password' => $passNuevo]);

        if (!empty($errores)) {
            $response->getBody()->write(json_encode($errores));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        $respuesta = UserModel::actualizarUsuario($usuario,$datos);

        $response->getBody()->write(json_encode($respuesta));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
}

<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/connect.php';


$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$link= new db();
    $pdo = $link->getConnection();

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

    /**
     * faltaria manjear los codigos de errores
     * agregar exceptions
     * slim $response->withCode(401)
     */


$app->post('/registro', function ($request, $response, $args) use ($pdo) {
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

    try {
        // Verificar si el usuario ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuario WHERE usuario = :usuario");
        $stmt->execute([':usuario' => $usuario]);

        if ($stmt->fetch()) {
            $respuesta = ['error' => 'El nombre de usuario ya está en uso'];
            $response->getBody()->write(json_encode($respuesta));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(409); // Conflicto
        }

    
        $stmt = $pdo->prepare("INSERT INTO usuario (nombre,usuario, password) VALUES (:nombre,:usuario, :password)");
        $stmt->execute([
            ':nombre'=> $nombre,
            ':usuario' => $usuario,
            ':password' => $password
        ]);

        $respuesta = ['mensaje' => 'Usuario registrado con éxito'];
        $statusCode = 201;
    } catch (PDOException $e) {
        $respuesta = ['error' => 'Error al registrar el usuario: ' . $e->getMessage()];
        $statusCode = 500;
    }

    $response->getBody()->write(json_encode($respuesta));
    return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
});


$app->post('/login', function ($request, $response, $args) use ($pdo){
    $datos = $request->getParsedBody(); //guardo usuario y password en $datos
    $usuario = $datos['usuario'];
    $password = $datos['clave'];

    if (empty($usuario) || empty($password)) { //chequo de campos vacios
        $error = ['error' => 'Faltan campos obligatorios'];
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $sql=("SELECT password,usuario FROM usuario WHERE usuario=:usuario"); //buscar usuario
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':usuario'=>$usuario]);
    $resultado =$stmt->fetch();
 
    if (($resultado) && ($resultado['password']==$password)){ //si está y concide contraseña
        
        $token = $usuario . rand(1000, 9999);
        $vencimiento = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stmt = $pdo->prepare("UPDATE usuario SET token = :token, vencimiento_token = :vencimiento WHERE usuario = :usuario");
        $stmt->execute([':usuario'=> $usuario ,':token'=>$token, ':vencimiento'=>$vencimiento]);

        $response->getBody()->write(json_encode(['token' => $token]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
       
    } else { //si no lo encuentra
        
        $response->getBody()->write(json_encode(['error' => 'usuario o clave inválidos']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);

    }
   });

    $app->get('/usuarios/{usuario}', function($request, $response, $args) use ($pdo){
        $authHeader = $request->getHeaderLine('Authorization'); //me traigo el token
        $token = str_replace('Bearer ', '', $authHeader); //le saco la palabra Bearer (protocolo)

        $stmt=$pdo->prepare("SELECT * FROM usuario WHERE token = :token AND vencimiento_token > NOW()");
        $stmt->execute([':token'=>$token]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            $response->getBody()->write(json_encode(['error'=>'Token inválido']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }else{
            $userData=['nombre'=>$usuario['nombre'], 'usuario'=>$usuario['usuario']];
            $response->getBody()->write(json_encode($userData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
    
    });

    $app->put('/usuarios/{usuario}', function ($request, $response, $args) use ($pdo){
        
        $authHeader = $request->getHeaderLine('Authorization'); //me traigo el token
        $token = str_replace('Bearer ', '', $authHeader); //le saco la palabra Bearer (protocolo)

        $stmt=$pdo->prepare("SELECT * FROM usuario WHERE token = :token AND vencimiento_token > NOW()");
        $stmt->execute([':token'=>$token]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            $response->getBody()->write(json_encode(['error'=>'Token inválido']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }else{
            $data = $request->getParsedBody();
            $stmt = $pdo->prepare("UPDATE usuario SET nombre = :nombre, password = :password WHERE token = :token");
            $stmt->execute([':token'=>$token,
                            ':nombre' => $data['nombre'],
                            ':password' => $data['password']]);

            $response->getBody()->write(json_encode(['exito'=>'Nombre y password modificados']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
    });

$app->run();


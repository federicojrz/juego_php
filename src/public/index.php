<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use App\Controllers\UserController;
require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Models/DB.php';



$app = AppFactory::create();
$app->addBodyParsingMiddleware();


//require_once __DIR__ . '/../endpoints/usuarios.php';

$app->post('/registro', [UserController::class, 'registro']);

$app->post('/login', [UserController::class, 'login']);

$app->get('/', function (Request $request, Response $response, $args) { //hello world
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->get('/usuarios', [UserController::class, 'getUser']); //endpoint de prueba


    /**
     * faltaria manjear los codigos de errores
     * agregar exceptions
     * slim $response->withCode(401)
     */
/*
$app->get('/prueba', function ($request, $response, $args)use ($pdo){
    $stmt = $pdo->query("SELECT * FROM usuario");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getbody()->write(json_encode($usuarios));
    return $response;
});
*/
$app->run();


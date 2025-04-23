<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Slim\Psr7\Response;

class VerificarToken {
    public static function verificarToken($request, $handler) {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'Token no enviado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = JWT::decode($token, new Key("mi_clave_super_secreta", 'HS256'));
            $request = $request->withAttribute('usuario', $decoded->usuario);
            return $handler->handle($request);
        } catch (\Exception $e) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'Token inválido']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    }
}
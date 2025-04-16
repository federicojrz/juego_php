<?php

function validarToken($request, $pdo) {
    // Obtener el token del header Authorization
    $authHeader = $request->getHeaderLine('Authorization');
    $token = str_replace('Bearer ', '', $authHeader);  // Extraer el token sin 'Bearer'

    // Consultar en la base de datos si el token es válido y no caduco
    $stmt = $pdo->prepare("SELECT nombre,usuario,token FROM usuario WHERE token = :token AND vencimiento_token > NOW()");
    $stmt->execute([':token' => $token]);
    $usuario = $stmt->fetch();
    

    // Si no se encuentra un usuario con el token válido, devolver error
    if (!$usuario) {
        return false;  // Token no válido o vencido
    }
    
    // Si es válido, devolver los datos del usuario
    return $usuario;
}


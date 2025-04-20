<?php

namespace App\Models;
use App\Models\DB;
require_once __DIR__ . '/DB.php';

class UserModel{
    

    public static function registrar($nombre,$usuario,$password){
        $link= new DB();
        $pdo = $link->getConnection();

        try{
            $stmt = $pdo->prepare("SELECT id FROM usuario WHERE usuario = :usuario");
            $stmt->execute([':usuario' => $usuario]);


        if ($stmt->fetch()) {
             return ['error' => 'El nombre de usuario ya esta en uso'];
            }

    
        $stmt = $pdo->prepare("INSERT INTO usuario (nombre,usuario, password) VALUES (:nombre,:usuario, :password)");
        $stmt->execute([
            ':nombre'=> $nombre,
            ':usuario' => $usuario,
            ':password' => $password
        ]);

        return ['Mensaje'=> 'Usuario registrado correctamente']; 
        }catch (PDOException $e) {
            return ['error' => 'Error al registrar el usuario: ' . $e->getMessage()]; //preguntar el codigo de error
        }

        $response->getBody()->write(json_encode($respuesta));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
    

        }

        public static function mostrar(){
            $link= new DB();
            $pdo = $link->getConnection();

            $sql=("SELECT * FROM usuario");

            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $resultado =$stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $resultado;
            
        }

        public static function validarUsuario($usuario,$password){
            $link= new DB();
            $pdo = $link->getConnection();

            $sql=("SELECT password,usuario FROM usuario WHERE usuario=:usuario"); //buscar usuario
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':usuario'=>$usuario]);
            $resultado =$stmt->fetch();
         
            if (($resultado) && ($resultado['password']==$password)){
                return true;

        } else {
            return false;
        }
    }

        public static function actualizarToken($usuario,$token,$vencimiento){
            try{
                $link= new DB();
                $pdo = $link->getConnection();
                
                $stmt = $pdo->prepare("UPDATE usuario SET token = :token, vencimiento_token = :vencimiento WHERE usuario = :usuario");
                $stmt->execute([':usuario'=> $usuario ,':token'=>$token, ':vencimiento'=>$vencimiento]);
            return true;
            }catch (PDOException $e){
                return false;
            }

        }


        
}
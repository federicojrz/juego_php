<?php

namespace App\Models;
use App\Models\DB;
use PDO;

class PartidaModel {

    public static function crearPartida($idUsuario, $idMazo){
        $link = new DB();
        $pdo = $link->getConnection();

        try{
            $stmt = $pdo->prepare("INSERT INTO partida (usuario_id, mazo_id, fecha, estado) VALUES (:idUsuario, :idMazo, :fecha, :estado)");
            $fecha = date('Y-m-d H:i:s');

            $stmt->execute([':idUsuario'=>$idUsuario,':idMazo'=>$idMazo,':fecha'=>$fecha,':estado'=>'en_curso']);

            return $pdo->lastInsertId();
        }catch (PDOException $e){
            return ['error' => 'Error al crear partida: ' . $e->getMessage()];
        }

    }

    public static function finalizarPartida($idPartida){
        $link= new DB();
        $pdo=$link->getConnection();

        $sql="UPDATE partida SET estado = 'finalizada' WHERE id=:idPartida";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([':idPartida'=>$idPartida]);

    }

}
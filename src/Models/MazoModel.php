<?php

namespace App\Models;
use App\Models\DB;
use PDO;

class MazoModel{

    public static function contarMazos($id){ //$id es id de usuario
        try{
            $link= new DB();
            $pdo = $link->getConnection();
            $stmt = $pdo->prepare ("SELECT COUNT(*) FROM mazo WHERE usuario_id = :usuarioId");
            $stmt->execute([':usuarioId' => $id]);
            $resultado =$stmt->fetchColumn();

            return $resultado;
        }catch (PDOException $e){
            return ['error' => 'Error al contar mazos ' . $e->getMessage()]; 
        }        
    }

    public static function existeCarta($cartaId){
        try{
            $link= new DB();
            $pdo = $link->getConnection();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM carta WHERE id = :cartaId");
            $stmt->execute(['cartaId'=>$cartaId]);
            $existe=$stmt->fetchColumn() > 0;

            return $existe;
        }catch (PDOException $e){
            return ['error' => 'Error al buscar carta ' . $e->getMessage()]; 
        }
    }

    public static function altaMazo($idUsuario, $nombreMazo, $cartas){
        try{
            $link= new DB();
            $pdo = $link->getConnection();

            $stmt = $pdo->prepare("INSERT INTO mazo (usuario_id, nombre) VALUES (:usuarioId, :nombre)");
            $stmt->execute(['usuarioId' => $idUsuario, 'nombre' => $nombreMazo]);

            $mazoId = $pdo->lastInsertId();

            foreach ($cartas as $cartaId){
                 $stmt = $pdo->prepare("INSERT INTO mazo_carta (mazo_id, carta_id, estado) VALUES (:mazoId, :cartaId, 'en_mazo')");
                 $stmt->execute(['mazoId'=>$mazoId,'cartaId'=>$cartaId]);
            }

            return $mazoId;
        } catch (PDOException $e){
            return ['error'=>'Error al insertar mazo '.$e->getMessage()];
        }
        
    }
}
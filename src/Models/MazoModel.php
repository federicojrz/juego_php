<?php

namespace App\Models;
use App\Models\DB;
use PDO;

class MazoModel{

    public static function contarMazos($id){ //$id es id de usuario
        try{
            $link= new DB();
            $pdo = $link->getConnection();

            $stmt = $pdo->prepare ("SELECT COUNT(*) FROM mazo 
                                    WHERE usuario_id = :usuarioId");

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

            $stmt = $pdo->prepare("INSERT INTO mazo (usuario_id, nombre) 
                                   VALUES (:usuarioId, :nombre)");

            $stmt->execute(['usuarioId' => $idUsuario, 
                            'nombre' => $nombreMazo]);

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

    public static function verificarMazo($idMazo,$idUsuario){
        $link = new DB();
        $pdo = $link->getConnection();

        $stmt = $pdo->prepare("SELECT COUNT(*) 
                               FROM mazo 
                               WHERE id = :idMazo 
                               AND usuario_id = :idUsuario");
        $stmt->execute([':idUsuario' => $idUsuario, ':idMazo'=>$idMazo]);

        return $stmt->fetchColumn() > 0;
    }

        public static function verificarCarta($idCarta, $idPartida):int{
            $link = new DB();
            $pdo = $link->getConnection();
            try{
                $stmt = $pdo->prepare("SELECT p.mazo_id FROM partida as p 
                                        INNER JOIN mazo_carta AS m ON p.mazo_id = m.mazo_id
                                        WHERE m.carta_id=:idCarta AND p.id=:idPartida AND m.estado ='en_mano'");

            $stmt->execute([':idCarta'=>$idCarta,
                            ':idPartida'=>$idPartida]);

            return ($stmt->fetchColumn());

            

            }catch(PDOException $e){
                return ['error'=>'Error al verificar carta '.$e->getMessage()];
            }
            
    }

    public static function actualizarEstado($idMazo,$idCarta){ 
        $link=new DB;
        $pdo=$link->getConnection();

        if ($idCarta==null){
            try{
                $stmt=$pdo->prepare("UPDATE mazo_carta 
                                    SET estado = :estado 
                                    WHERE mazo_id = :idMazo OR mazo_id = 1");
    
                $stmt->execute([':estado'=>'en_mano',':idMazo'=>$idMazo]);
                
            }catch (PDOException $e){
                return ['error'=>'No se pudo poner el mazo en mano'.$e->getMessage()];
            }          
        }else{
            $stmt=$pdo->prepare("UPDATE mazo_carta
                                SET estado = 'descartado'
                                WHERE mazo_id=:idMazo AND carta_id=:idCarta");
            $stmt->execute([':idMazo'=>$idMazo,
                            ':idCarta'=>$idCarta]);
        }
            
        }
  
        public static function obtenerCartas($idMazo){
            $link=new DB();
            $pdo=$link->getConnection();
    
            try{
                $stmt=$pdo->prepare("SELECT carta_id  FROM mazo_carta WHERE mazo_id = :idMazo");
                $stmt->execute([':idMazo'=>$idMazo]);
                $cartas=$stmt->fetchAll(PDO::FETCH_ASSOC);
                return $cartas;
            }catch(PDOException $e){
                return ['error '=>'No se pudo listar las cartas usadas'.$e->getMessage()];
            }
        }
        
        public static function cartasServidor():array{
        $link=new DB();
        $pdo=$link->getConnection();

        $stmt=$pdo->query("SELECT carta_id FROM mazo_carta
                            WHERE mazo_id = 1 AND estado = 'en_mano'");

        $cartasServer=$stmt->fetchAll(PDO::FETCH_COLUMN);   


        return $cartasServer;


    }
}
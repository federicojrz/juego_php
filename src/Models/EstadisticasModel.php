<?php

namespace App\Models;
use PDO;
use App\Models\DB;

class EstadisticasModel {
    public static function estadisticas() {
        $link = new DB();
        $pdo = $link->getConnection();

        $sql = "SELECT 
                u.usuario AS nombre_usuario,
                COUNT(CASE WHEN p.el_usuario = 'gano' THEN 1 END) AS ganadas,
                COUNT(CASE WHEN p.el_usuario = 'empato' THEN 1 END) AS empatadas,
                COUNT(CASE WHEN p.el_usuario = 'perdio' THEN 1 END) AS perdidas
            FROM partida p
            JOIN usuario u ON p.usuario_id = u.id
            GROUP BY u.usuario";

        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

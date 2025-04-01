<?php

class db {

    private string $host = 'localhost';
    private string $dbname = 'cartas';
    private string $username = 'root';

    private ?PDO $pdo = null;
   
    public function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname}";
        try{
            $this->pdo= new PDO($dsn,$this->username);
        } 
        catch(PDOException $e){
            die($e->getMessage());
        } 
    }

    function getConnection(){
        return $this->pdo;
    }

}



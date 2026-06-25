<?php
// backend/config/Database.php

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $host = '127.0.0.1';
        $db   = 'app-loove';
        $user = 'root';
        $pass = ''; // Mets ton mot de passe si tu en as défini un sur HeidiSQL
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Active les erreurs SQL sous forme d'exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retourne les données sous forme de tableau associatif
            PDO::ATTR_EMULATE_PREPARES   => true,                  // Active l'émulation pour permettre la réutilisation des paramètres nommés
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            // En cas d'échec, on logue l'erreur ou on arrête le script
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}
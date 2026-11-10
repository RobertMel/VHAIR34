<?php
session_start();

function getPDO() {
    // Infos MySQL InfinityFree
    $host = "sql105.infinityfree.com";  // host MySQL correct
    $dbname = "if0_40537671_epiz_12345678_vhair"; // nom de base EXACT
    $user = "if0_40537671";             // utilisateur MySQL correct
    $pass = "VOTRE_MOT_DE_PASSE";            // ton mot de passe MySQL

    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

    try {
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        die("Erreur connexion MySQL : " . $e->getMessage());
    }
}

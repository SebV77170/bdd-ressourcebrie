<?php

require '../../vendor/autoload.php';

function get_pdo (): PDO {
    $dbname = "09007_ressourceb";
    $serveur = "sql01.ouvaton.coop";
    $login = "09007_ressourceb";
    $pass = "LaRessourcerieDeBrie77170!";
    return new PDO("mysql:host=localhost;dbname=objets;charset=utf8;", "root", "root", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
}

function dd(...$vars) {
    foreach($vars as $var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
}

?>
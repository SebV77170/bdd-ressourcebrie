<?php



if (function_exists('date_default_timezone_set') && !ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

function get_pdo (): PDO {
    /* $dbname = "09007_ressourceb";
    $serveur = "sql01.ouvaton.coop";
    $login = "09007_ressourceb";
    $pass = "LaRessourcerieDeBrie77170!"; */
    $dbname = "ressourcebrie_bdd";
    $serveur ="mysql-ressourcebrie.alwaysdata.net";
    $login = "418153";
    $pass = "geMsos-wunxoc-1fucbu";

    $httpHost = $_SERVER['HTTP_HOST'] ?? '';

    if($httpHost=='localhost:8888'):

        return new PDO("mysql:host=localhost;dbname=objets;charset=utf8;", "root", "root", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

    else:

        return new PDO("mysql:host=$serveur;dbname=$dbname;charset=utf8;", $login, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

    endif;
}

function dd(...$vars) {
    foreach($vars as $var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
}

function convertDateFRenDateUS($string){
    $format_us = implode('-',array_reverse  (explode('-',$string)));
    return $format_us;
}

?>

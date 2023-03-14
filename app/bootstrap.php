<?php



function get_pdo (): PDO {
    $dbname = "09007_ressourceb";
    $serveur = "sql01.ouvaton.coop";
    $login = "09007_ressourceb";
    $pass = "LaRessourcerieDeBrie77170!";

    if($_SERVER['HTTP_HOST']=='localhost:8888'):

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

?>
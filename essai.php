<?php


$fichier=nl2br(file_get_contents('tickets/Ticket162.txt'));

function br2nl($string)
{
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

$fichier=br2nl($fichier);


echo($fichier);

?>
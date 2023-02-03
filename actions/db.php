<?php
            $dbname = "objets";
            $serveur = "localhost";
            $login = "root";
            $pass = "root";
            
            try{
                        $db = new PDO("mysql:host=$serveur;dbname=$dbname;charset=utf8;", $login, $pass);
                        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            
            catch(Exception $e){
                        die('Une erreur a été trouvée : '.$e->getMessage());
            }
            
?>
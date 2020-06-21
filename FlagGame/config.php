<?php

    // Les parametres et le login de la base de donnée
    //
    define("DATABASE_IP"            , "localhost:3306");
    define("DATABASE_USER"          , "root");
    define("DATABASE_PASSWORD"      , "");
    define("DATABASE_TABLE"         , "flag_game");    

    // Les paramètres du constructeur
    define("ADMIN_PASSWORD"         , "admin"); // Sera hache en md5 apres insertion
    define("QUIZ_MAX_COUNTRIES"     , 5);
        
?>  
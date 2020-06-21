<?php
    // Démarrer / continuer la session
    session_start();

    // Charger notre bibliothèque 
    require_once("funcs.php");

    //se connecter à la base de données
    require_once("database.php");

    // Variable global
    //
    $Parameters = array(); //
    $UserConnected = false; // variable qui indique si l'utilisateur est connecté
    $IsUserAdmin = false; // variable qui indique si l'utilisateur est un administrateur

    // 
    $__PARAM_LIST = DB_SelectAll("params");

    //
    if(is_array($__PARAM_LIST))
    {
        foreach($__PARAM_LIST as $param)
            $Parameters[$param["name"]] = $param["value"];  
    }

    // 
    unset($__PARAM_LIST);

    // Si le paramètre DataBase_Built n'est pas défini, rediriger vers le générateur
    if(!isset($Parameters["DATABASE_BUILT"]))
    {
        // Site Web non construit
        Redirect("./builder.php");
    }

    // Vérifions si un utilisateur est connecté
    if(isset($_SESSION["UserConnected"]) && $_SESSION["UserConnected"] == true)
    {
        $userData = DB_SelectAll("users", "WHERE email=:email", array(":email"=>$_SESSION["UserData"]["email"]));
        
        if(is_array($userData) && count($userData) > 0)
        {
            // 
            $UserConnected = true;

            //Vérifier si l'utilisateur est un administrateur
            if(strtolower($_SESSION["UserData"]["email"]) == "admin")
                $IsUserAdmin = true;
        }
    }

    // 
    $CONTINENTS = array();
    
    // on obtient les modes de jeu disponibles 
    $__CONTINENT_BUFFER = DB_SelectAll("continents", "ORDER BY name ASC");

    // Construire un array de continents où le continent est la clé
    foreach($__CONTINENT_BUFFER as $continent)
        $CONTINENTS[$continent["continent"]] = $continent;

    //
    unset($__CONTINENT_BUFFER);

?>

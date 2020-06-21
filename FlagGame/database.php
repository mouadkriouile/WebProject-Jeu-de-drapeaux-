<?php
    // Chargement du ficher de configuration
    require_once("config.php");

    // Définir une variable globale pointant vers la base de données
    $database = null;

    // Se connecter à la base de données une fois ce script requis!
    try {
        
        //Se connecter à la base de données
        $GLOBALS['database'] = 
            new PDO('mysql:host='.DATABASE_IP.';dbname='.DATABASE_TABLE, 
                    DATABASE_USER, 
                    DATABASE_PASSWORD);
        
    } catch (PDOException $e) {
        // Arrêter et signaler toute erreur de base de données
        PrintErrorPage("Error connection to database:\n - " . $e->getMessage()."\n\n");
    }

    function DB_InsertRow($table, $data)
    {
        // TARGET REQUEST TO MAKE
        // INSERT INTO {table} (key1, key2, ..., keyn) VALUES(:key1, :key2, ..., :keyn)

        // Créer un key & holder holders
        $keyAndHolder = array();

        // Créer les holders : key1 => :key1, key2 => :key2, ..., keyn => :keyn
        foreach(array_keys($data) as $key)
            $keyAndHolder[$key] = ":".$key;

        // Génerer les keys (key1, key2, ..., keyn)
        $keys = join(", ", array_keys($keyAndHolder));

        // Generate the holders (:key1, :key2, ..., :keyn)
        $holders = join(", ", array_values($keyAndHolder));

        // Preparer la requete
        $request = $GLOBALS['database']->prepare(
            "INSERT INTO $table ($keys) VALUES($holders)");

        // Vérifiez si la requete de base de données a été exécutée
        if($request->execute($data))
            return true;

        // return false (error)
        return false;
    }

    // Sélectionnez les colonnes nécessaires sur les lignes en respectant les filtres 
    function DB_Select($table, $columns = array("*"), $filter = "", $filter_param = array())
    {    
        // TARGET REQUEST TO MAKE
        // SELECT {columns} FROM {table} WHERE filter=filterValue (AND...)
        
        $request = $GLOBALS['database']->prepare(
            "SELECT ".join(", ", $columns)." FROM $table $filter");
        
        if(!$request->execute($filter_param))
            return false;
        
        return $request->fetchAll(); 
    }

    // Sélectionnez toutes les colonnes des lignes en respectant les filtres
    function DB_SelectAll($table, $filter = "", $filter_param = array())
    {    
        // DB_Select() function wrapper
        return DB_Select($table, array("*"), $filter, $filter_param);
    }

    // Sélectionne chaque ligne d'un tableau où la valeur de la colonne est égale à n'importe quelle valeur de la liste
    function DB_SelectMultiple($table, $column, $list = array())
    {    
        // TARGET REQUEST TO MAKE
        // SELECT * FROM {table} WHERE $column IN (value1, value2, ..., valueN)
        
        // Créer une chaîne avec ? répété à la taille de $list
        // E.g : (?, ?, ?, ..., ?)
        //
        $in = join(",", array_fill(0, sizeof($list), "?"));
        
        $request = $GLOBALS['database']->prepare(
            "SELECT * FROM $table WHERE ".$column." IN ($in)");
        
        if(!$request->execute($list))
            return false;

        return $request->fetchAll(); 
    }

    // Mettre à jour une ou plusieurs colonnes d'une ligne sélectionnée
    function DB_Update($table, $selector, $selectorValue, $data = array())
    {   
        // TARGET REQUEST TO MAKE
        // UPDATE {table} SET column1=value1, column2=value2, ..., columnN=valueN WHERE selector=selectorValue
             
        // Create a key & holder holders
        $dataHolders = array();
        
        // Créer les holders :  
        // column1=:column1, column2=:column2, ..., columnN=:valueN
        //
        foreach(array_keys($data) as $key)
            array_push($dataHolders, $key."=:".$key);

        // Collez le array et affichez-le dans la même variable 
        $dataHolders = join(", ", $dataHolders);
              
        // Preparer la requete
        $request = $GLOBALS['database']->prepare(     
            "UPDATE $table SET $dataHolders WHERE $selector=:$selector");
        
        // Ajouter le sélecteur aux données pour être lié à l'exécution
        $data[$selector] = $selectorValue; // Exemple : $request->bindParam(":id", $id);
                
        // Vérifiez si la demande de base de données a été exécutée avec succès et si les lignes affectées sont 1 ou plus
        if(!$request->execute($data))
            return null;
        
        if($request->rowCount() == 0)
            // Aucune ligne affectée, car la modification demandée existe déjà
            return false;
        else
            // Le changement des lignes affectées a réussi
            return true;
    }

    // Une de mise à jour par fonction id wrapper
    function DB_UpdateByID($table, $id, $data)
    {
        return DB_Update($table, "id", $id, $data);
    }

    // Supprime une entrée dans la base de données
    function DB_Delete($table, $filter = "", $filter_param = array())
    {    
        // TARGET REQUEST TO MAKE
        // DELETE FROM {table} WHERE selector=selectorValue (AND...)
        
        // Construire dans la requete SAL 
        $request = $GLOBALS['database']->prepare(
            "DELETE FROM $table $filter");
        
        // Vérifiez si la demande de base de données a été exécutée avec succès et si les lignes affectées sont 1 ou plus
        if($request->execute($filter_param) && $request->rowCount() > 0)
            return true;
        
        return false;
    }

    // Vérifier la disponibilité des e-mails
    function DoEmailExist($email)
    {
        // Preparer la requete
        $request = $GLOBALS['database']->prepare(
            "SELECT * FROM users WHERE email = :email");
        
        // Execution
        if(!$request->execute(array(':email' => $email)))
            return null;
        
        // Récupérer les lignes retournées (résultats)
        $result = $request->fetchAll();
        
        // Vérifiez si cet e-mail existe
        if(is_array($result) && count($result) > 0)
            return true;

        // Pas de résultat trouvé donc false
        return false;
    }

    // Vérifier l'existence de l'utilisateur
    function CheckUserLogin($email, $password)
    {      
        $matchedUsers = DB_SelectAll(
            "users", // Select from users
            "WHERE email=:email AND password=:password", // L'utilisateur avec un email et password
            array( "email" => $email, "password" => md5($password) ) // egal user_email et md5_hash(user_password)
        ); 
        
        if(is_array($matchedUsers) && count($matchedUsers) > 0)
            return $matchedUsers[0];
        
        return null;        
    }

    // Enregistre un utilisateur
    function DB_RegisterUser($email, $password)
    {
        $data = array(
            "email" => $email, // l'email
            "password" => md5($password), // Le mot de passe utilisateur doit être haché à l'aide de md5
            "registration_time" => CurrentDateTime() // L'heure actuelle (heure d'enregistrement)
        );
        
        // Insérez les données que nous avons préparées
        return DB_InsertRow("users", $data);      
    }

    // Récupère le message d'erreur de la dernière requête de base de données
    function RequestErrorInfo(&$request)
    {
        $errorList = $request->errorInfo();
        $errorString = "\n\nDataBase Error Informations\n";
        
        foreach($errorList as $error)
            $errorString .= $error."\n";

        return $errorString; 
    }
?>

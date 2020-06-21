<?php
    // La réponse sera de type JSON
    header('Content-Type: text/html; charset=utf-8');

    // Chargement du ficher de configuration
    require_once("config.php");

    // Chargez notre bibliothèque personnelle de fonctions
    require_once("funcs.php");

    // Se connecter à la base de données
    require_once("database.php");

    // N'arrêtez pas l'exécution car cela prendra du temps
    set_time_limit(3600);


    // A monospace font
    PrintMessage("<style>body{font-family: monospace; white-space: pre;}</style>");
    sleep(2); // Attendre 2 secondes


    // Afficher l'heure
    PrintMessage("! ".CurrentDateTime()."<br>");

    if(!isset($_POST["password"]) || strcmp($_POST["password"], ADMIN_PASSWORD) != 0)
    {
        die(PrintMessage("- Mauvais mot de passe principal"));
    }else{
        PrintMessage("+ Mot de passe principal correct");
    }

    // Début de la construction
    PrintMessage("+ Début de la construction...");

    // CONSTRUIRE LA BASE DE DONNÉES EN IMPORTANT LE FICHIER SQL
    /////////////////////////////////////////////

    if(!importSqlFile($database, "database-tables.sql"))
        die(PrintMessage("- ERREUR : La création des tables de base de données a échoué!"));
    else
        PrintMessage("+ Tables de base de données créés.");       


    // ENREGISTREMENT DU COMPTE ADMIN
    /////////////////////////////////////////////

    if(!DB_RegisterUser("admin", ADMIN_PASSWORD))
        die(PrintMessage("- ERREUR : La création du compte administrateur a échoué!"));
    else
        PrintMessage("+ Compte administrateur par défaut créé.");


    // Ouverture des fichiers contenant les données et la géométrie des pays
    ////////////////////////////////////////////////////////////////

    // Countries data : 
    //      countries by : Mohammed Le Doze (mledoze)
    //      https://github.com/mledoze/countries
    //      Licence : ODC Open Database License (ODbL)
    //
    // Fichier utilisé pour connaître le pays : 
    //      CCA3 | French Name | Capital Name | Common Name (for wikilink) | Center Point | Area | Geometry
    //
    $countriesfile = file_get_contents("sources/mledoze-countries/countries.json");
    $countriesdata = json_decode($countriesfile, true);
    unset($countriesfile); // free memory

    // Vérification de l'intégrité des données
    if(json_last_error() !== JSON_ERROR_NONE)
        die(PrintMessage("- ERREUR : Fichier de propriétés des pays NON chargés!"));
    else
        PrintMessage("+ Fichier de propriétés des pays chargés...");

        

    // Variables globals 
    $continents = array(); // Le array contenant tous les continents
    $countries = array(); //Le array contenant tous les pays
    $largestCountryArea = 0; //La plus grande zone de pays [Utilisé comme base pour le score]

    // Indexation des pays pour un accès facile
    ///////////////////////////////////////////////////////////
    //
    PrintMessage("+ Indexation des données des pays et continents...");

    // Ajoutez les pays au tableau des pays et rends du code de pays la clé (cca3)
    foreach($countriesdata as $country)
    {
        // Obtenir le code du pays, nous utilisons des lettres miniscules
        // Parce que les fichiers de géométrie et les fichiers  flag utilisent des lettres minuscules pour le ccs3
        $cca3 = strtolower($country["cca3"]);

        // Ignorer chaque pays avec un code de pays étrange ou une zone négative / nulle
        if(!ctype_alpha($cca3) || $country["area"] <= 0)
            continue;

        // Ajouter ce pays
        $countries[$cca3] = $country;  

        // Vérifions si c'est le plus grand pays
        if($country["area"] > $largestCountryArea)
            $largestCountryArea = $country["area"];  

        // Récuperer le continent Supprimer les espaces 
        $continentName = str_replace(" ", "", $country["region"]);

        // Ajouter le nom du continent
        $countries[$cca3]["continent"] = $continentName;  

        // S'il s'agit d'un nouveau continent, ajoutez-le à la liste des continents
        if(!in_array($continentName, $continents))
        {
            $continents[$continentName] = array(
                "continent" => $continentName,
            );    
        }

        // Définissez la géométrie sur null. Si nous échouons à la charger, elle reste nulle
        $countries[$cca3]["geometry"] = null;

        // GeoJSON file path
        $geoJsonFilePath = "sources/mledoze-countries/geometries/$cca3.geo.json";
        
        // Vérifiez si le fichier existe
        if(file_exists($geoJsonFilePath))
        {
            // Lisez le fichier geojson s'il existe
            $geoJsonFile = file_get_contents($geoJsonFilePath);

            // Decoder les infos json
            $geoJsonData = json_decode($geoJsonFile, true);

            // Vérifier si le fichier de géométrie a été décodé avec succès
            if(json_last_error() === JSON_ERROR_NONE)
            {
                // Ajouter la géométrie
                if(isset($geoJsonData["features"][0]["geometry"])){
                    $countries[$cca3]["geometry"] = $geoJsonData["features"][0]["geometry"];
                }
            }

            if(isset($geoJsonFile))
                unset($geoJsonFile);

            if(isset($geoJsonFile))
                unset($geoJsonData);
        }
    }

    // liberer $countriesdata depuis que nous avons pris toutes les données dont nous avons besoin dans le array
    unset($countriesdata);

    // Ajouter dans le monde entier comme continent
    $continents["World"] = array("continent" => "World");

    // CONSTRUIRE LA TABLE CONTINENTS
    /////////////////////////////////////////////

    PrintMessage("<br>- Génération de la table des continents mondiale...<br>");

    foreach($continents as $continent => $continentData)
    {    
        // Pour l'autorisation"visitor_allowed" nous utilisons 1 pour permettre aux visiteurs de jouer au continent, sinon nous utilisons 0     
        switch($continent)
        {

            case "Africa": 
                $continentData["name"] = "L'Afrique";        
                $continentData["center"] = "1.850021,14.000000";
                $continentData["visitor_allowed"] = 1;
                break;

            case "Americas":
                $continentData["name"] = "L'Amérique";        
                $continentData["center"] = "23.746161,-79.541015";
                $continentData["visitor_allowed"] = 0;

                break;

            case "Asia":
                $continentData["name"] = "L'Asie";        
                $continentData["center"] = "20.4199201,95.1871381";
                $continentData["visitor_allowed"] = 0;
                break;  

            case "Europe":
                $continentData["name"] = "L'Europe";        
                $continentData["center"] = "54.820340,9.052734";
                $continentData["visitor_allowed"] = 1;
                break;  

            case "Oceania":
                $continentData["name"] = "L'Océanie";        
                $continentData["center"] = "-9.067056,114.927919";
                $continentData["visitor_allowed"] = 0;
                break;
            
            case "World":
                $continentData["name"] = "Le monde entier";        
                $continentData["center"] = "0,0";
                $continentData["visitor_allowed"] = 0;
                break;

            default:
                // Ignorer les autres continents
                PrintMessage (ColoredText(null)." | ".$continent);

                // Désactiver les continents ignorés (les pays associés ne seront pas ajoutés)
                $continents[$continent] = null;
                
                // Continuer à boucler et non pas le switch case
                continue 2;
                break;
        }   

        $reqResult = DB_InsertRow("continents", $continentData);
        PrintMessage (ColoredText($reqResult) ." | ". str_pad($continent, 40)." | ".$continentData["name"]); 
        
        // ajouter un tableau de questionnaires pour une utilisation ultérieure
        // /!\ Nous l'ajoutons après l'insertion du continent car nous n'insérons pas les questionnaires dans la base de données
        $continents[$continent]["quizzes"] = array();
    }

    // CONSTRUIRE LA TABLE COUNTRIES 
    /////////////////////////////////////////////

    PrintMessage("<br>- Génération de la table des pays du monde...<br>");     

    // Construisons notre petit fichier geoJSON avec les propriétés nécessaires
    // Chaque pays n'a qu'une seule caractéristique
    //
    $countryGeoJson = array(
        "type" => "FeatureCollection",
        "features" => array(
        /* Premiere caractéristique feature [0] */
        array(
                "type" => "Feature",
                "properties" => array(
                    "cca3" => null,
                    "continent" => null, 

                    "name" => null,
                    "common" => null,
                    "capital" => null,

                    "latitude" => null,
                    "longitude" => null,
                    "area" => null,
                ),
                "geometry" => null,
            ),        
        ),
    );

    // boucler sur tous les pays
    foreach($countries as $cca3 => $country)
    {     
        // Ignorons chaque pays sans données de géométrie ou son continent n'est pas disponible
        if(!isset($country["geometry"]) || $continents[$country["continent"]] == null)
        {
            // Afficher les informations sur le pays ignoré
            PrintMessage (
                ColoredText() ." | ".
                $cca3." | ".
                CreateLink($country["name"]["common"])." | ".
                "<span style='color:#ff6000; font-weight: bold'>".str_pad((isset($country["geometry"]) ? "#Skipped Continent" : "#No Geometry Data"), 22)."</span> | ".
                str_pad($country["area"]." Km²", 16, " ", STR_PAD_LEFT)." | ".
                $country["translations"]["fra"]["common"] 
            );
            
            continue;
        }
            
        // Construire le geoJsom
        //
        $countryGeoJson["features"][0]["properties"]["cca3"] = $cca3;
        $countryGeoJson["features"][0]["properties"]["continent"] = $country["continent"];

        $countryGeoJson["features"][0]["properties"]["name"] = $country["translations"]["fra"]["common"];
        $countryGeoJson["features"][0]["properties"]["common"] = $country["name"]["common"];
        $countryGeoJson["features"][0]["properties"]["capital"] = $country["capital"][0];   

        $countryGeoJson["features"][0]["properties"]["latlng"] = $country["latlng"];
        $countryGeoJson["features"][0]["properties"]["area"] = $country["area"];

        $countryGeoJson["features"][0]["geometry"] = $country["geometry"];

        // Ligne de base de données à insérer
        $request = array(
            "cca3"      => $cca3,
            "continent" => $country["continent"],   
            "name"      => $country["translations"]["fra"]["common"],
            "geojson"   => json_encode($countryGeoJson)
        );

        // Insérer le pays dans la table countries
        $reqResult = DB_InsertRow("countries", $request);
        
        // Ajouter que les pays qui sont reussis à etre ajouter dans les questionnaires
        if($reqResult == true)
        {
            // Ajoutez le code du pays à son continent respectif pour créer des questionnaires plus tard
            array_push($continents[$country["continent"]]["quizzes"], $cca3);

            // Ajouter uniquement les pays avec une surface plus grand que 100 000 aux questionnaires du monde entier
            if($country["area"] > 100000)
                array_push($continents["World"]["quizzes"], $cca3);  
        }

        // Afficher les informations sur le pays
        PrintMessage (
            ColoredText($reqResult) ." | ".
            $cca3." | ".
            CreateLink($country["name"]["common"])." | ".
            str_pad($country["continent"], 22)." | ".
            str_pad($country["area"]." Km²", 16, " ", STR_PAD_LEFT)." | ".
            $country["translations"]["fra"]["common"]
        );
        
        // Liberer la ram
        // unset($countries[$cca3]);
    }

    // CONSTRUIRE LA TABLE QUIZZES 
    /////////////////////////////////////////////

    PrintMessage("<br>- Génération des quizzes...<br>");

    $quiz = array(
        "continent" => "",
        "countries" => ""
    );

    // Ce script fera des questionnares pour contenir tous les pays disponibles pour chaque continent
    foreach($continents as $continent => $data)
    {
        //Si le continent doit être ignoré, on continue
        if($data == null)
            continue;
            
        // Diviser un array en plusieurs arrays contenant pour chacun 5 éléments
        $chunks = array_chunk($data["quizzes"], QUIZ_MAX_COUNTRIES);
        
        $quiz["continent"] = $continent;
        
        for($i=0; $i < count($chunks); $i++)
        {     
            // Joignez les codes de pays dans une chaîne séparée par un espace ("")
            $quiz["countries"] = join(" ", $chunks[$i]);
            
            if(count($chunks[$i]) == QUIZ_MAX_COUNTRIES)    
                // Ajoutez le questionnaire seulement s'il a le nombre maximum de pays
                $reqResult = DB_InsertRow("quizzes", $quiz);                      
            else
                // reqResult est null pour ajouter la balise ignorée :)
                $reqResult = null;     
                  
            PrintMessage (
                ColoredText($reqResult) ." | ".
                str_pad($continent, 16)." | ".
                $quiz["countries"]
            );                   
        }     
    }


    // CONSTRUIRE LA TABLE PARAMS 
    /////////////////////////////////////////////

    PrintMessage("<br>- Génération de la table des paramètres...<br>");

    $paramList = array();

    // Ajouter le paramètre de base de score
    array_push($paramList, array(
        "name" => "DATABASE_BUILT",
        "value" => "True"
    ));

    // Ajouter le paramètre de base de score
    array_push($paramList, array(
        "name" => "SCORE_CONSTANT",
        "value" => (log10($largestCountryArea) + 1.001)
    ));

    // Ajouter le paramètre de base de score
    array_push($paramList, array(
        "name" => "QUIZ_MAX_COUNTRIES",
        "value" => QUIZ_MAX_COUNTRIES
    ));

    foreach($paramList as $param)
    {
        $reqResult = DB_InsertRow("params", $param);
        PrintMessage (ColoredText($reqResult) ." | ".str_pad($param["name"], 18)." : ".$param["value"]);
    }     
            
    // Fin du script de création de site Web
    PrintMessage("<br><br><br>+ Génération de la base de données terminée avec succès!");




    // FONCTIONS REQUISES POUR LE CONSTRUCTEUR
    /////////////////////////////////////////////

    // Affiche le message
    //
    function PrintMessage($message)
    {
        echo "$message<br>";
        ob_flush();
        flush();
    }

    //Importer le fichier sql 
    //
    function importSqlFile($pdo, $sqlFile, $tablePrefix = null, $InFilePath = null)
    {
    	try {
    		
    		// Activer LOAD LOCAL INFILE
    		$pdo->setAttribute(PDO::MYSQL_ATTR_LOCAL_INFILE, true);
    		
    		$errorDetect = false;
    		
    		// Variable temporaire, utilisée pour stocker la requête actuelle
    		$tmpLine = '';
    		
    		// Lire tous du fichier
    		$lines = file($sqlFile);
    		
    		// Boucler chaque ligne
    		foreach ($lines as $line) {
    			// Ignorez-le s'il s'agit d'un commentaire
    			if (substr($line, 0, 2) == '--' || trim($line) == '') {
    				continue;
    			}
    			
    			// Lire et remplacer le préfixe
    			$line = str_replace(['<<prefix>>', '<<InFilePath>>'], [$tablePrefix, $InFilePath], $line);
    			
    			// Ajoutez cette ligne au segment en cours
    			$tmpLine .= $line;
    			
    			// S'il a un point-virgule à la fin, c'est la fin de la requête
    			if (substr(trim($line), -1, 1) == ';') {
    				try {
                        
    					// Exécuter la requête
    					if($pdo->exec($tmpLine) === false)
                            return false;
                        
    				} catch (PDOException $e) {
    					PrintMessage("Error performing Query: '<strong>" . $tmpLine . "</strong>': " . $e->getMessage());
    					$errorDetect = true;
    				}
    				
    				// Réinitialiser la variable temp à vide
    				$tmpLine = '';
    			}
    		}
    		
    		// Vérifiez si une erreur est détectée
    		if ($errorDetect) {
    			return false;
    		}
    		
    	} catch (Exception $e) {
    		PrintMessage ("Exception => " . $e->getMessage());
    		return false;
    	}
    	
    	return true;
    }

    // Générer un lien vers la page wiki du pays
    //
    function CreateLink($common_name)
    {
        $t = str_replace(" ", "_", $common_name); // lien wiki 
        return "<a href='https://en.wikipedia.org/wiki/$t'>wiki-link</a>";      
    }

    // Créez les textes colorés : FAIL - *OK* - SKIP
    //
    function ColoredText($result = null)
    {
        if($result == null)
            return "<span style='color:orange'>SKIP</span>";
        
        if($result)
            return "<span style='color:green'>*OK*</span>";
        
        else
            return "<span style='color:red'>FAIL</span>";
    }
?>

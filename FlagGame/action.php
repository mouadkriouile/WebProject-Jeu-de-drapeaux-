<?php
    
    require_once("core.php");

    // La réponse sera de type JSON
    header('Content-Type: application/json; charset=utf-8');

    // Les données que le serveur renverra
    $data = array(
        "success" => false, //  success ne devient vrai que si la demande est exécutée 
        "message" => "" // Tout message à afficher
    );

    //L'action doit être disponible
    if(isset($_POST["action"]))
    {
        // Voyons quelle action est demandée
        switch($_POST["action"])
        {
                        
            case "log-in":
                
                
                if(isset($_POST["email"]) && isset($_POST["password"]))
                {
                    $userData = CheckUserLogin($_POST["email"], $_POST["password"]);
                    if(is_array($userData))
                    {
                        //Définir la session de l'utilisateur
                        SetUserSession($userData);
                        
                        // Alerter l'utilisateur qu'il est connecté avec succès
                        ShowAlert("Vous êtes connecté avec succès, profitez du jeu!");            
                    }
                    else 
                    {
                        // Alerter l'utilisateur d'un mauvais e-mail et mot de passe
                        ShowAlert("E-mail ou mot de passe incorrect!");         
                    }
                }else{
                    // sinon si quelque chose a mal tourné
                    ShowAlert("Erreur de connexion au site, veuillez réessayer!");            
                }
                
                // 
                RedirectToPreviousPage();    
                
                //Fin de l'action
                break;
                
                
                
            case "log-out":
                
                // fermer la session utilisateur
                UnsetUserSession();
                
                // Alerter l'utilisateur
                ShowAlert("Vous êtes déconnecté avec succès, à plus tard!");    
                
                 
                RedirectToPreviousPage();
                
                // Fin de l'action
                break;
                
                
                
             case "sign-up":
                
                
                if(isset($_POST["email"]) && isset($_POST["password"]))
                { 
                    // Nous avons vérifié les e-mails (côté client) mais
                    // nous devons le revérifier ici aussi (côté serveur)
                    if(CheckEmail($_POST["email"]))
                    {             
                        // Enregistrer l'utilisateur
                        if(DB_RegisterUser($_POST["email"], $_POST["password"]))
                        {
                            // Si l'enregistrement s'est bien passé, connectez l'utilisateur automatiquement
                            $userData = CheckUserLogin($_POST["email"], $_POST["password"]);
                    
                            if($userData === null)
                            {
                                //si on a un échec de la connexion automatique
                                ShowAlert("Problème lors de la connexion automatique, essayez de vous connecter manuellement.");            
                            }else{
                               
                                SetUserSession($userData);
                                
                                // utilisateur enregistré et connecté avec succès on affiche ..
                                ShowAlert("Vous vous êtes inscrit avec succès, vous pouvez profiter de toutes les fonctionnalités du jeu!");            
                            }             
                        }else{
                            // Échec d'enregistrement de l'utilisateur
                            ShowAlert("Problème lors de l'inscription, réessayer!");            
                        }                   
                    }else{
                        //E-mail incorrect 
                        ShowAlert("Mauvais e-mail envoyé, l'inscription a échoué!"); 
                    }
                }else{
                    // sinon si quelque chose a mal tourné
                    $data["message"] = "Requête mal formée!";
                }
                
                              
                RedirectToPreviousPage();
                
                // Fin de l'action
                break; 
                     
                
                
            case "check-email":
                
                if(isset($_POST["email"]) && CheckEmail($_POST["email"]))
                {
                    $request = DoEmailExist($_POST["email"]);
                    
                    if($request === null)
                    {
                        $data["message"] = "Erreur au niveau de base de donnees !";
                    }else{     
                        
                        $data["success"] = true;
                        
                        // Renvoie l'e-mail 
                        $data["emailExists"] = $request;  
                    }         
                }else{
                    // sinon si quelque chose a mal tourné
                    $data["message"] = "Requête mal formée!";
                }
                
                // End of action
                break;
                     
                
                
            case "get-quiz":
                            
                
                if(isset($_POST["mode"]))
                {                            
                    // La réponse ne sera pas un code HTML mais un texte 
                    //header('Content-Type: text/plain; charset=utf-8');
                    header('Content-Type: application/json; charset=utf-8');

                    if(IsValidContinent($_POST["mode"]))
                    {         
                        // Obtenez tous les quiz disponibles
                        $quizzes = DB_SelectAll("quizzes", "WHERE continent=:continent", array(":continent" => $_POST["mode"]));

                        // Si ya des quiz qui sont disponibles
                        if(is_array($quizzes) && count($quizzes) > 0)
                        {
                            // Initialiser l'identifiant du quiz à 0 (premier quiz de chaque continent)
                            $quiz_id = 0;
                            
                            if($UserConnected)
                            {
                                // Si le tracker de quiz pour ce continent existe
                                //S'il n'est pas défini, l'ID du quiz sera 0 comme défini précédemment
                                //
                                if(isset($_SESSION["QuizTracker"][$_POST["mode"]]))
                                {
                                    // Obtenez le prochain quiz pour ce continent
                                    $quiz_id = $_SESSION["QuizTracker"][$_POST["mode"]] + 1;
                                    
                                    // Si l'ID du quiz sélectionné dépasse le nombre maximal de quiz disponibles, réinitialisé à 0
                                    if($quiz_id > count($quizzes)-1)
                                        $quiz_id = 0;  
                                }  
                                
                                //Enregistrer le tracker du quiz actuel dans la session
                                $_SESSION["QuizTracker"][$_POST["mode"]] = $quiz_id;
                            }
                        
                            // Les pays du quiz sont représentés par des codes de pays séparés par un espace ('')
                            $quizCountryCodes = explode(" ", $quizzes[$quiz_id]["countries"]);

                            // on sélectionne chaque pays avec le code de pays (cca3) trouvé dans le quiz
                            $countries = DB_SelectMultiple("countries", "cca3", $quizCountryCodes);
     
                            // Vérifiez si les pays sélectionnés sont dans un tableau sinon il y a une erreur
                            if(is_array($countries))
                            {
                               // Mélangez la liste pour ne pas jouer dans le même ordre
                                shuffle($countries);
                                
                                $data["success"] = true; //indique que tout est bon et que le quiz est prêt à être joué
                                $data["quiz_size"] = count($countries); // Nombre de pays à jouer 
                                $data["quiz_id"] = $quizzes[$quiz_id]["id"]; // L'identifiant du quiz de la base de données
                                $data["countries"] = array(); //le tableau qui contiendra les données geoJSON des pays

                                for($i=0; $i < count($countries); $i++)
                                {
                                    // Décoder le geoJSON stocké
                                    $geoJSON = json_decode($countries[$i]["geojson"], true);

                                    // Libérez de la mémoire
                                    unset($countries[$i]["geojson"]);

                                    // Mettre dans l'index des questions
                                    $geoJSON["features"][0]["properties"]["index"] = $i;

                                    // stocké les données dans le tableau de résultats 
                                    array_push($data["countries"], $geoJSON); 
                                }
                            }else{
                                //Erreur lors du chargement des questionnaires
                                $data["message"] = "Erreur de chargement du quiz au niveau de base de données !"; 
                            }
                        }else{
                           
                            $data["message"] = "Aucun quiz n'est disponible pour ce continent!"; 
                        }   
                     }else{
                        // Continent inconnu 
                        $data["message"] = "Ce continent n'existe pas dans ce jeu!";
                     }            
                }else{
                  
                    $data["message"] = "Requête mal formée!";
                }

                // fin d'action
                break;
                   
                
                
                
            case "save-score":
                
                if(isset($_POST["mode"]) && IsValidContinent($_POST["mode"])
                   && isset($_POST["quiz_id"]) && isset($_POST["quiz_result"]) 
                   && isset($_POST["score"]) && is_numeric($_POST["score"])
                   && isset($_POST["time"]) && is_numeric($_POST["time"])
                   && isset($_POST["time_multiplier"]) && is_numeric($_POST["time_multiplier"]))
                {    
                    
                    if($UserConnected)
                    {
                        // Les requetes de base de données
                        $request = array(
                            "user_id" => $_SESSION["UserData"]["id"], //Identifiant de l'utilisateur  
                            "mode" => $_POST["mode"], // Le continent 
                            "quiz_id" => $_POST["quiz_id"], // L'identifiant du quiz joué
                            "quiz_result" => $_POST["quiz_result"], // Le résultat du quiz joué
                            "time" => $_POST["time"], // Le temps du quiz joué en secondes
                            "time_multiplayer" => $_POST["time_multiplier"], // Le multiplicateur de temps du quiz joué
                            "score" => $_POST["score"], // Le score du quiz joué
                            "timestamp" => CurrentDateTime() 
                        );

                        // Insérez les états
                        if(DB_InsertRow("stats", $request))
                        {
                            $data["success"] = true; // indique que tout bon et quiz est enregistré
                            $data["message"] = "Votre score a été enregistré."; 

                        }else{
                            // Erreur lors de l'enregistrement du score
                            $data["message"] = "Votre score n'a pas été enregistré a cause d'une erreur!";
                        }
                    }else{
                        // L'utilisateur n'est pas connecté donc le score n'est pas enregistré
                        $data["message"] = "Vous jouez en mode invité, votre score n'est pas enregistré. Pensez à vous abonner!"; 
                    }
                }else{
                    
                    $data["message"] = "Requête mal formée!";
                }
                
                
                break;
               
                
                
                
            case "delete-user":
                
                if($UserConnected && $IsUserAdmin && isset($_POST["id"]) && is_numeric($_POST["id"]) && $_POST["id"] > 0)
                {  
                    //!\ Vérifiez que nous ne nous supprimons pas le compte de l'admin en vérifiant son id avec l'identifiant utilisateur
                    if($_POST["id"] != $_SESSION["UserData"]["id"])
                    {              
                        // Supprimer l'historique d'utilisateurs 
                        DB_Delete("stats", "WHERE user_id=:id", array(":id" => $_POST["id"]));

                        // Supprimer l'utilisateur
                        if(DB_Delete("users", "WHERE id=:id", array(":id" => $_POST["id"])))
                        {
                            //  l'utilisateur est supprimé
                            $data["success"] = true;
                            $data["message"] = "L'utilisateur avec l'identifiant <b>".$_POST["id"]."</b> a été supprimé !";
                            
                        }else{
                            $data["message"] = "Erreur lors de suppression de l'utilisateur !";
                        }                
                    }else{
                        
                        $data["message"] = "Vous ne pouvez pas supprimer l'administrateur";
                    }
                }else{
                  
                    $data["message"] = "Requête mal formée!";
                }
                
                
                break;
            
                
                
                
            case "get-quiz-list":
                
                if($UserConnected && $IsUserAdmin && isset($_POST["continent"]) && IsValidContinent($_POST["continent"]))
                { 
                    //Sélectionner tous les quiz du continent sélectionné
                    $quizzes = DB_SelectAll("quizzes", "WHERE continent=:continent", array("continent" => $_POST["continent"]));

                    
                    if(is_array($quizzes))
                    {
                        $data["success"] = true; 
                        $data["quizzes"] = $quizzes;  
                    }else{
                        // Error loading the quizzes
                        $data["message"] = "Error loading available quizzes!";
                    }
                }else{
                    
                    $data["message"] = "Requête mal formée!";
                }
                
                           
                break; 
                
                
                
                
            case "process-quiz":
                      
                if($UserConnected && $IsUserAdmin && isset($_POST["continent"]) && IsValidContinent($_POST["continent"])
                   && isset($_POST["countries"]) && is_array($_POST["countries"]) && count($_POST["countries"]) > 0 
                   && isset($_POST["quiz_id"]) && is_numeric($_POST["quiz_id"]) && $_POST["quiz_id"] >= 0)
                {                
                     
                    if($_POST["quiz_id"] == 0)
                    {
                       
                        
                        
                        // Générez les données à insérer
                        $newQuizRow = array(
                            "continent" => $_POST["continent"],
                            "countries" => join(" ", $_POST["countries"]),
                        );
                    
                        // Insérez le quiz
                        if(DB_InsertRow("quizzes", $newQuizRow))
                        {
                            $data["success"] = true;    
                            $data["quiz_id"] = $database->lastInsertId();    
                        }else{
                            $data["message"] = "Une erreur s'est produite lors de l'insertion du nouveau questionnaire!";                 
                        }       

                    }else{
                        

                        // Générer les données à mettre à jour
                        $newQuizRow = array("countries" => join(" ", $_POST["countries"]));
          
                        
                        $updateResult = DB_UpdateByID("quizzes", $_POST["quiz_id"], $newQuizRow);
                        
                        if($updateResult !== null)
                        {
                            $data["success"] = true;                          
                            $data["quiz_id"] = $_POST["quiz_id"];  
                            
                            if($updateResult)
                                $data["updated"] = true; 
                            else
                                $data["updated"] = false;

                        }else{
                            $data["message"] = "Une erreur s'est produite lors de la mise à jour du questionnaire!";                 
                        }  
                    }             
                }else{
                  
                    $data["message"] = "Requête mal formée!";
                }
                
                
                break;
                
                
                
                
            case "delete-quiz":
                                
                if($UserConnected && $IsUserAdmin && isset($_POST["quiz_id"]) && is_numeric($_POST["quiz_id"]) && $_POST["quiz_id"] > 0)
                {  
                   
                    if(DB_Delete("quizzes", "WHERE id=:id", array(":id" => $_POST["quiz_id"])))
                    {
                        $data["success"] = true;
                        $data["message"] = "Le questionnaire avec l'identifiant ".$_POST["quiz_id"]." a été supprimé !";
                    }else{
                        $data["message"] = "Erreur lors de la suppression du questionnaire!";
                    }
               
                }else{
                    
                    $data["message"] = "Requête mal formée!";
                }

                
                break;
                
            // si l'admin veut changer les permissions d'un continent 
            case "toggle-continent-permission":
                
                if($UserConnected && $IsUserAdmin && isset($_POST["allowed"]) && is_numeric($_POST["allowed"]) &&
                   isset($_POST["continent"]) && IsValidContinent($_POST["continent"]))
                {                     
        
                    
                    $editedPermission = array("visitor_allowed" => $_POST["allowed"] <= 0 ? 0 : 1);

                   
                    $continent_id = $CONTINENTS[$_POST["continent"]]["id"];
                    
                    // Modifier l'autorisation du continent
                    $updateResult = DB_UpdateByID("continents", $continent_id, $editedPermission);

                    if($updateResult !== null)
                    {
                        // Changement réussi
                        $data["success"] = true;                          

                    }else{
                        $data["message"] = "Une erreur s'est produite lors de la mise à jour des permissions !";                 
                    }  
               
                }else{
                    
                    $data["message"] = "Requête mal formée!";
                }

               
                break;
                
                
                
                
            default:            
                $data["message"] = "Unknown action type : ".$_POST['action'];
                break;  
       
        }    
    }else{     
        
        $data["message"] = "Requête mal formée!";
    }

    // renvoi la reponse
    print(json_encode($data));  

    // fin d'action
    die();
?>

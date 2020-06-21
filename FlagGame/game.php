<?php 
    define('GAME_PHP', true);
    require_once("core.php");

    // Si le mode de jeu n'est pas défini
    if(!isset($_GET["mode"]))
    {
        // Choisissez un continent au hasard
        $randomContinent = SelectRandomContinent();
        
        // Dites à l'utilisateur le continent choisi (facultatif)
        ShowAlert("<b>" . $randomContinent["name"] . "</b> est le continent choisi pour vous, have fun !", "primary");
        
        // Rediriger vers la page du jeu mais avec le mode de jeu sélectionné
        Redirect("game.php?mode=".$randomContinent["continent"]);
        
    }else{    
        
        // Vérifiez si ce continent est disponible
        if(!IsValidContinent($_GET["mode"]))
            // Redirigez vers la page du jeu pour sélectionner un autre continent au hasard
            Redirect("game.php");  
        
        $selectedContinent = $GLOBALS["CONTINENTS"][$_GET["mode"]];
          
        // Si le mode de jeu mondial est sélectionné mais que l'utilisateur n'est pas connecté !!
        if(!$selectedContinent["visitor_allowed"] && !$UserConnected)
        {
            // Choisir un continent au hasard
            $randomContinent = SelectRandomContinent();
            
            // Avertir le joueur qu'il doit être connecté et l'informer du continent choisi
            ShowAlert("<b>".$selectedContinent["name"]."</b> est reservé aux joueurs abonnés sur notre site !<br />".
                      "<b>" . $randomContinent["name"] . "</b> est le continent sélectioné au hazard pour vous maintenant !");
            
            // Rediriger vers la page du jeu mais avec le mode de jeu sélectionné
            Redirect("game.php?mode=".$randomContinent["continent"]);          
        }
    }

    // Ici, le mode doit être défini, sinon l'exécution doit avoir été arrêtée et l'utilisateur sera redirigé !
    // De plus, le monde ne peut pas être sélectionné si l'utilisateur n'est pas connecté !!
    $currentParameters = array(
        "isAdmin"       => $IsUserAdmin,
        "mode"          => $_GET["mode"], 
        "name"          => $CONTINENTS[$_GET["mode"]]["name"],         
        "center"        => explode(",", $CONTINENTS[$_GET["mode"]]["center"]),
        "scoreConstant" => $Parameters["SCORE_CONSTANT"]
    );
    
?>

<!DOCTYPE html>
<html class="game-page" lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes, shrink-to-fit=yes">

    <title>Jeu</title>

    <link rel="stylesheet" href="libraries/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="css/style.css"> <!-- Appeler notre style pour remplacer certaines valeurs -->

    <script src="libraries/jquery/jquery.min.js"></script>
    <script src="libraries/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Include map api -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js"></script>

    <!-- Passons quelques paramètres de php à js -->
    <script>
        // Données continentales actuelles
        const gameParameters = <?php echo json_encode($currentParameters) ?>;

    </script>

    <!-- Include notre game handler -->
    <script src="js/game-handler.js"></script>

</head>

<body>

    <div class="h-100 d-flex flex-column">
        <div class="w-100 p-0">
            <?php 
                include("navbar.php"); 
                include("alerts.php"); 
            ?>
        </div>

        <div class="w-100 p-0 flex-grow-1 d-flex flex-row">

            <div class="flex-grow-1 p-0 d-flex flex-column">

                <div class="score-panel no-select p-0 bg-light border-top border-bottom">
                    <div class="d-flex flex-row ">

                        <div class="mx-0 px-2 mr-auto text-left border-right">
                            <div class="h5 m-0">Continent</div>
                            <div class="text-secondary"><?php echo $currentParameters["name"]?></div>
                        </div>

                        <div class="mx-0 px-2 border-left">
                            <div class="m-0 px-4"> Distance </div>
                            <div class="h5 m-0 text-monospace distance-holder text-dark">--</div>
                        </div>

                        <div class="mx-0 border-left">
                            <div class="m-0 px-2">Tentatives</div>
                            <div class="h5 m-0 text-monospace click-attempt-holder text-dark">--</div>
                        </div>

                        <div class="mx-0 px-2 border-left">
                            <div class="">Temps</div>
                            <div class="h5 m-0 text-monospace time-holder text-danger">--</div>
                        </div>

                        <div class="mx-0 px-2 border-left">
                            <div class="">Facteur Temps</div>
                            <div class="h5 m-0 text-monospace time-factor-holder text-primary">--</div>
                        </div>

                        <div class="mx-0 px-2 border-left">
                            <div class="m-0">Score de base</div>
                            <div class="h5 m-0 text-monospace base-score-holder text-warning">--</div>
                        </div>

                        <div class="mx-0 px-2 border-left">
                            <div class="">Score Total</div>
                            <div class="h5 m-0 px-2 text-monospace score-holder text-success ">--</div>
                        </div>

                    </div>
                </div>

                <div class="flex-grow-1">
                    <div class="container-fluid map-wrapper" id="map-canvas">
                    </div>
                </div>

            </div>

            <div class="game-gui no-select d-flex flex-column bg-light border">

                <div class="p-2 mb-0">

                    <div class="flag">
                        <div class="flag-vignette"></div>
                        <div class="flag-holder">
                            <img class="country-flag" src="flags/unknown-flag.svg" />
                        </div>
                    </div>

                    <div class="quiz-progressBar mt-2 d-flex flex-row">
                        <div quiz-id="0"></div>
                    </div>

                </div>

                <div class="infoPanel flex-grow-1 border-top border-bottom">
                    <div class="pb-0">

                        <div class="m-0 py-2 border-bottom">
                            <div class="title m-0 p-0 px-2 text-bold">Nom du pays <a class="wikilink" target="_blank">Wikipédia</a> </div>
                            <div class="country-name info m-0 px-2">-</div>
                        </div>

                        <div class="m-0 py-2 border-bottom">
                            <div class="title m-0 p-0 pl-2">Nom de la capitale</div>
                            <div class="country-capital info m-0 px-2">-</div>
                        </div>

                        <div class="m-0 py-2 border-bottom">
                            <div class="title m-0 p-0 pl-2">Surface</div>
                            <div class="country-area info m-0 px-2">-</div>
                        </div>

                    </div>
                </div>

                <button class="theBigButton btn btn-primary m-2 disabled" class="score">
                    Veuillez patienter
                </button>
            </div>
        </div>
    </div>
</body>

</html>

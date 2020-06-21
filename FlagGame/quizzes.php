<?php 
    define('INTERFACE_PHP', true);
    define('QUIZZES_PHP', true);
    require_once("core.php");

    // Vérifiez si quelqu'un est connecté car c'est réservé aux administrateurs et aux joueurs uniquement
    if(!$UserConnected || !$IsUserAdmin)
    {
        // Rediriger vers la page principale car l'utilisateur n'est pas connecté ou n'est pas administrateur
        Redirect("index.php"); 
    }

    // Obtenir les données dont nous avons besoin dans la base de données
    $countriesTable = DB_Select(
        "countries", // La table où sont stockées les données des pays
        array("cca3", "continent", "name"), // Les données ou colonnes de données demandées
        "ORDER BY name ASC" // ordonnons les noms par ordre alphabétique de A à Z pour une recherche facile plus tard
    );

    // Variable qui détiendra les pays
    $countriesList = array();

    // Construire la liste des pays en utilisant la méthode décrite ci-dessous
    foreach($countriesTable as $c)
    {
        // Nous allons construire la liste des pays en utilisant cette méthode
        /*
            cca3: {
                continent: "continent"
                name: "name"
            }        
        */
        
        // Ajouter tous les pays
        $countriesList[$c["cca3"]] = array(
            "continent" => $c["continent"],
            "name"      => $c["name"],
        );
    }
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <!-- Balises meta requises pour HTML5-->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Questionnaire</title>

    <link rel="stylesheet" href="libraries/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="css/style.css"> <!-- Appeler notre style pour remplacer certaines valeurs -->

    <script src="libraries/jquery/jquery.min.js"></script>
    <script src="libraries/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="js/quizzes-manager.js"></script>
    <script>
        // Le nombre maximum autorisé de pays par questionnaires
        const QUIZ_MAX_COUNTRIES = <?php echo $Parameters["QUIZ_MAX_COUNTRIES"]; ?>;

        /* Cette variable contiendra tous les pays disponibles dans la base de données */
        const countriesList = <?php echo json_encode($countriesList); ?>;

    </script>
</head>

<body>

    <?php include("navbar.php"); ?>
    <?php include("alerts.php"); ?>

    <div class="container-fluid px-0 py-3 no-select">
        <div class="px-5">

            <h4 class="">Gestion des questionnaires</h4>
            <p class="">
                Veuillez sélectionner un continent afin de pouvoir ajouter / modifier / supprimer des
                questionnaires et également modifier l'autorisation d'accès pour chaque continent.
            </p>

            <div class="row mt-4">
                <div class="col-3">

                    <div class="btn-group w-100">
                        <button class="btn dropdown-toggle btn-dark text-right" type="button" data-toggle="dropdown" aria-expanded="false">
                            <span class="continent-name float-left">Choisissez un continent...</span>
                        </button>

                        <div class="continent-list dropdown-menu dropdown-menu-right w-100 p-0">
                            <?php
                                foreach($CONTINENTS as $continent)
                                {
                                    $region = $continent["continent"];
                                    $name = $continent["name"];
                                    $class = ($continent["visitor_allowed"]) ? "allowed-mode" : "limited-mode";
                                    
                                    echo "<a class='dropdown-item m-1 $class' href='#' select-mode='$region'>$name</a>";                     
                                }     
                            ?>
                        </div>
                    </div>


                    <h4 class="mt-4 mb-3">Actions :</h4>
                    <button class=" w-100 btn btn-success text-left" onclick="addNewQuiz()">
                        Ajouter un nouveau questionnaire
                    </button>
                    <button class="mt-2 w-100 btn btn-primary text-left" onclick="addRandomNewQuiz()">
                        Ajouter un questionnaire au hazard
                    </button>


                    <h4 class="mt-4 mb-3">Permissions :</h4>
                    <div class="border mt-2 p-2 rounded">
                        <p class="m-0">
                            Vous pouvez autoriser ou limiter les joueurs non connectés à jouer à ce mode / continent.
                        </p>

                        <div class="continent-name bg-light border text-center rounded h5 my-3 p-2">Choisissez un continent !</div>

                        <div class="btn-group w-100 m-0" role="group">
                            <button type="button" class="btn btn-dark" onclick="updateContinentPermission(0)">Interdire</button>
                            <button type="button" class="btn btn-success" onclick="updateContinentPermission(1)">Autoriser</button>
                        </div>
                    </div>
                </div>

                <div class="col pr-0">

                    <table id="quizzes-table" class="table table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width:8%" class="text-right">ID</th>
                                <th class="">Pays par questionnaire</th>
                                <th style="width:24%" class="">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="quizzes-table-body">

                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

    <!-- The quiz editor modal -->
    <div class="modal fade no-select" id="quizEditor" tabindex="-1" role="dialog" aria-labelledby="quizEditorLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="quizEditorTitle">Editeur de quiz</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <p class="text-justify px-2 mb-2">
                        Veuillez sélectionner les pays à jouer dans ce quiz,
                        ou cliquez sur "choisir au hasard" pour laisser le
                        script choisir des pays au hasard dans la liste.
                        Vous ne pouvez sélectionner que <b><?php echo $Parameters["QUIZ_MAX_COUNTRIES"]; ?></b> pays comme nombre
                        maximum de pays par quiz.
                    </p>
                    <p class="text-justify px-2">
                        <b>Notez</b> que l'ordre est aléatoire lors de la lecture des
                        quiz sur la page du jeu pour éviter de se souvenir de l'ordre.
                    </p>

                    <hr>
                    <div id="modal-countries-list" class="d-inline-flex flex-row flex-wrap justify-content-center">

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" onclick="modalClose()">Close</button>
                    <button type="button" class="btn btn-info" onclick="modalSelectRandomCountries()">Choisir au hazard</button>
                    <button type="button" class="btn btn-success" onclick="modalSaveChanges()">Enregistrer</button>
                </div>

            </div>
        </div>
    </div>
</body>

</html>

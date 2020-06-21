<?php 
    define('STATS_PHP', true);
    require_once("core.php");

    
    $stats_user_id = 0; // L'ID du joueur dont nous chargerons les statistiques

    $viewingAnotherPlayer = false; // Vrai si nous sommes un administrateur affichant les statistiques d'un autre joueur
    $user_email = ""; // l'email du joueur

    // Vérifier si quelqu'un est connecté car c'est réservé aux administrateurs et aux joueurs uniquement
    if(!$UserConnected)
    {
        // Message 
        ShowAlert("Statistiques disponibles juste pour les joueurs inscrit !");

        // Redirection à page index.php
        Redirect("index.php"); 
    }

    // Vérifiez si nous consultons les statistiques d'un autre joueur (l'utilisateur doit être administrateur)
    if(isset($_GET["user-id"]) && $IsUserAdmin)
    {
        // Nous sommes un administrateur afin que nous puissions voir les statistiques de n'importe quel joueur
        $stats_user_id = $_GET["user-id"];   
        
        // Nous regardons un autre joueur 
        $viewingAnotherPlayer = true;
        
        //Vérifiez si le joueur avec l'ID sélectionné existe et recevez l'e-mail
        $userData = DB_Select("users", array("email"), "WHERE id=:id LIMIT 1", array(":id" => $stats_user_id));
        
        // Si les données de la table est vide
        if(count($userData) == 0)
            // Le joueur n'existe pas
            $user_email = "Utilisateur non existant";
        else
            // Get le email
            $user_email = $userData[0]["email"];
        
    }else{      
        // Ne pas voir un autre joueur donc nous regardons le nôtre (joueur actuel)
        $stats_user_id = $_SESSION["UserData"]["id"];
    }

    // Obtenir les statistiques du joueur sélectionné dans la base de données
    $stats = DB_SelectAll("stats", "WHERE user_id=:user_id ORDER BY timestamp DESC", array(":user_id" => $stats_user_id));

    // Obtenir le nombre de parties jouées
    $statSize = count($stats);

    // Variable du score total
    $totalScore = 0;

    // Ajouter le score
    foreach($stats as $s)
        $totalScore +=  $s["score"];

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <!-- Balises meta requises pour HTML5-->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Statistiques</title>

    <link rel="stylesheet" href="libraries/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="css/style.css"> <!-- Appeler notre style pour remplacer certaines valeurs -->

    <script src="libraries/jquery/jquery.min.js"></script>
    <script src="libraries/bootstrap/js/bootstrap.bundle.min.js"></script>

</head>

<body>

    <?php include("navbar.php"); ?>
    <?php include("alerts.php"); ?>

    <div class="container-fluid px-0 pt-5">

        <div class="px-5">

            <?php 
                // Si nous regardons un joueur en tant qu'administrateur
                if(!$viewingAnotherPlayer) : 
            ?>

            <h4 class="">Historique de jeu</h4>

            <p>
                Voici la liste des parties que vous avez joué précédemment,
                vous pouvez consulter les dates et votre score pour chacune des parties !
                <br>
                Vous avez joué <b><?php echo $statSize; ?></b> partie(s)
                avec un score total de <b><?php echo number_format($totalScore, 2, ',', ' '); ?></b> points
            </p>

            <?php 
                // Si le joueur regarde ses statistiques
                else : 
            ?>

            <h4 class="">Statistiques</h4>
            <h6 class="ml-4 mb-3">
                Indice d'utilisateur : <b><?php echo $stats_user_id ?></b> |
                email : <b><?php echo $user_email; ?></b>
            </h6>

            <p>
                Voici la liste des parties jouées par l'utilisateur sélectioné,
                vous pouvez consulter le date et le score de chacune des parties !
                <br>
                Ce joueur a joué <b><?php echo $statSize; ?></b> partie(s)
                avec un score total de <b><?php echo number_format($totalScore, 2, ',', ' '); ?></b> points
            </p>

            <?php 
                // End if
                endif 
            ?>

        </div>

        <table id="score-table" class="table table-striped mt-5 no-select">
            <thead class="thead-dark">
                <tr>
                    <th class="text-center" style="min-width:7%">Continent</th>
                    <th class="text-center" style="min-width:5%">ID</th>
                    <th>Pays joué(s) dans le questionnaire</th>
                    <th class="text-left" style="min-width:10%">Temps / Facteur</th>
                    <th class="text-left" style="min-width:10%">Base / Score</th>
                    <th class="text-left" style="width:14%">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php    
                    
                    
                    // Une template HTML pour une ligne de tableau
                    $template =
                    '<tr>
                        <th class="p-1 align-middle text-center" scope="row">%s</th>
                        <th class="p-1 align-middle text-center">%s</th>
                        <td class="p-1 align-middle">
                            <div class="my-0 text-white">
                                %s
                            </div>
                        </td>
                        <td class="p-1 align-middle">
                            <div>
                            <div><b>%s</b></div>
                            <div class="mt-1 text-primary"><b>x%s</b></div> 
                            </div>
                        </td>
                        <th class="p-1 align-middle">
                            <div>%s</div>
                            <div class="mt-1 text-success"><b>%s</b></div> 
                        </th>
                        <td class="p-1 align-middle">
                        %s
                        </td>
                    </tr>';

                    // For each statistic row
                    foreach($stats as $s)
                    {

                        // Separer le resultat du questionnaire
                        $result = explode("|", $s["quiz_result"]);
                        
                        // La liste des pays en html
                        $htmlCountriesList = "";
                        
                        // For each pays dans le resultat
                        foreach($result as $c)
                        {              
                            /* 
                                Each country in the result will start with a sign e.g: +France
                                The sign signifies player's answer to the question
                                -----
                                    + Correct answer at first try
                                    ~ Correct answer but not first try
                                    - Wrong answer or skiped question
                            */
                            
                            $sign = substr($c, 0, 1); // Obtenir le premier signe de caractère
                            $countryName = substr($c, 1, strlen($c)-1); // Obtenir le nom du pays joué
                            
                            switch($sign)
                            {
                                case "+":
                                    $htmlCountriesList .= '<span class="country-span bg-success">'.$countryName.'</span>';
                                    break;
                                    
                                case "~":
                                    $htmlCountriesList .= '<span class="country-span bg-warning text-dark">'.$countryName.'</span>';
                                    break;
                                    
                                case "-":
                                    $htmlCountriesList .= '<span class="country-span bg-danger">'.$countryName.'</span>';
                                    break;                                
                            }                         
                        }
                        
                        // Formater la template avec les données et les pays lus et afficher / ajouter une ligne
                        echo sprintf($template, 
                                     
                            $CONTINENTS[$s["mode"]]["name"],
                                     
                            $s["quiz_id"],
                            $htmlCountriesList,
                                     
                            gmdate("H:i:s", $s["time"]),
                            $s["time_multiplayer"],
                                     
                            number_format($s["score"] / $s["time_multiplayer"], 2, ',', ' '),
                            number_format($s["score"], 2, ',', ' '),
                                     
                            $s["timestamp"]
                        );      
                    }      
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>

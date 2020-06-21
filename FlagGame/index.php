<?php 
    define('HOME_PHP', true);
    require_once("core.php");

    
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <!-- Balises meta requises pour HTML5-->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Jeu de drapeaux</title>

    <link rel="stylesheet" href="libraries/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="css/style.css"> <!-- Appeler notre style pour remplacer certaines valeurs -->

    <script src="libraries/jquery/jquery.min.js"></script>
    <script src="libraries/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>

<body>

    <?php include("navbar.php"); ?>
    <?php include("alerts.php"); ?>

    <div class="container py-5">

        <div class="px-4 mt-4">
            <div class="card-deck">

                <div class="card bg-light">
                    <img src="images/Card1.jpg" class="card-img-top" alt="...">
                    <hr class="m-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Flag Game</h5>
                        <p class="card-text">
                            Notre site offre un design cool qui facilite la lecture et la découverte des pays et de leurs emplacements à l'aide de styles fournis par la bibliothèque BootStrap, et de drapeaux de haute qualité basés sur la technologie svg.
                            <br>Rendez-vous sur A PROPOS pour plus de détails. <b>Bon Jeu !</b>
                        </p>
                    </div>
                </div>

                <div class="card bg-light">
                    <img src="images/Card2.jpg" class="card-img-top" alt="...">
                    <hr class="m-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary"> Statistiques </h5>
                        <p class="card-text">Si vous êtes déjà abonné à notre site Web, vous pouvez voir l'historique de tous vos jeux précédents, y compris les scores et les performances de chaque quiz et question.</p>
                    </div>
                </div>

            </div>
        </div>

        <div class="mt-4 p-2 text-center">
            <a href="game.php" class="px-5 btn btn-lg btn-primary">Jouez maintenant !</a>
        </div>
    </div>
</body>

</html>

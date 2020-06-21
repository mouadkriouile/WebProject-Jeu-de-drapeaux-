<!DOCTYPE html>
<html class="builder" lang="fr">

<head>
    <!-- Balises meta requises pour HTML5-->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Constructeur</title>

    <link rel="stylesheet" href="libraries/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="css/style.css"> <!-- Appelez notre style pour remplacer certaines valeurs -->

    <script src="libraries/jquery/jquery.min.js"></script>
    <script src="libraries/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="js/builder.js"></script>

</head>

<body>
    <div class="container py-5 h-100 d-flex flex-column">

        <div class="d-flex align-items-center px-4">
            <img src="images/tow-truck.png" alt="database-icon">
            <h2 class="m-0 mx-4 flex-grow-1 "> Constructeur de la base de donnée</h2>
            <a href="./"><button class="btn btn-dark m-0">ACCUEIL</button></a>
        </div>

        <hr class="w-100 mt-2 mb-4">

        <p class="lead text-justify">
            Bienvenue sur le constructeur du site, cette page vous permet de construire ( ou supprimer si quelque chose tourne mal ) les tables de la base de donnée
            à partir des fichiers de ressources fournis pour un nouveau départ,
            tous les utilisateurs enregistrés, l'historique des scores et des jeux, les quiz et
            les configurations seront mis à votre disposition. Une fois que vous avez entré le mot de passe principal (de l'admin ) et
            cliqué sur <b>"Démarrer la construction"</b>, le processus commencera!
        </p>
        <div class="text-danger">
            Attention: the parameter "<b>max_allowed_packet</b>" of your database must have a large number, minimum value "<b>16M</b>"
        </div>


        <div class="mt-3">
            <form id="form" class="form-inline" action="builder-script.php" method="post" target="output_frame">
                <input id="password-input" class="form-control form-control-lg mr-3 border-primary col" type="password" name="password" placeholder="Mot de passe principal">
                <button id="submit-btn" class="btn btn-primary btn-lg">
                    Démarrer la construction
                </button>
            </form>
        </div>

        <div class="w-100 mt-3 p-0 border border-primary bg-light flex-grow-1">
            <iframe id="output_frame" class="builder-iframe" name="output_frame">
            </iframe>
        </div>

    </div>
</body>

</html>

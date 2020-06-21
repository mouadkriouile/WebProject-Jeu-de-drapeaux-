<?php 
    // Include le modele sign up et le sign-up handler javascript
    // Si aucun joueur n'est connecté (il peut donc créer un nouveau compte)
    if(!$UserConnected)
        include("signup-form.php");
?>

<nav class="main-navbar navbar navbar-expand-lg navbar-light bg-light">

    <a class="navbar-brand mr-5" href="./">JEU DES DRAPEAUX</a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#mainNavBar" aria-controls="mainNavBar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNavBar">

        <ul class="navbar-nav mr-auto">

            <li class="nav-item mr-2 <?php if(defined('HOME_PHP')) echo "active"; ?>">
                <a class="nav-link" href="./">Accueil</a>
            </li>

            <li class="nav-item dropdown ml-0 mr-2 <?php if(defined('GAME_PHP')) echo "active"; ?>">
                <a class="nav-link dropdown-toggle" href="game.php" id="game-modes-selector" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Jouer
                </a>
                <div class="dropdown-menu" aria-labelledby="game-modes-selector">
                    <?php
                        foreach($CONTINENTS as $continent)
                        {
                            $region = $continent["continent"];
                            $name = $continent["name"];
                            $class = (!$continent["visitor_allowed"] && !$UserConnected) ? "text-warning" : "";
                            
                            echo "<a class='dropdown-item $class' href='game.php?mode=$region'>$name</a>";                     
                        }     
                    ?>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="game.php">Jouer au hazard</a>
                </div>
            </li>

            <?php if($UserConnected) : ?>

            <li class="nav-item mr-2 <?php if(defined('STATS_PHP')) echo 'active'; ?>">
                <a class="nav-link" href="stats.php">statistiques</a>
            </li>

            <?php if($IsUserAdmin) : ?>

            <li class="nav-item dropdown ml-0 mr-2 <?php if(defined('INTERFACE_PHP')) echo "active"; ?>">
                <a class="nav-link dropdown-toggle" href="game.php" id="game-modes-selector" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Gestion
                </a>
                <div class="dropdown-menu" aria-labelledby="game-modes-selector">
                    <a class="dropdown-item <?php if(defined('USERS_PHP')) echo "active"; ?>" href="users.php">Gérer les utilisateurs</a>
                    <a class="dropdown-item <?php if(defined('QUIZZES_PHP')) echo "active"; ?>" href="quizzes.php">Gérer les questionnaires</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="builder.php">Constructeur de la base de donnée</a>
                </div>
            </li>

            <?php endif ?>

            <?php endif ?>

            <li class="nav-item mr-2 <?php if(defined('ABOUT_PHP')) echo "active"; ?>">
                <a class="nav-link" href="about.php">a propos</a>
            </li>
        </ul>

        <ul class="navbar-nav">

            <li class="nav-item dropdown">

                <?php if(defined('GAME_PHP')) :?>

                <button class="background-music-btn btn btn-light px-4">.</button>

                <?php endif ?>

                <?php if(!$UserConnected) : ?>

                <button class="btn btn-primary" data-toggle="modal" data-target="#signup-modal">
                    Inscription
                </button>

                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Se connecter
                </button>

                <div class="dropdown-menu dropdown-menu-right login-menu p-3">
                    <form action="action.php" method="post">

                        <h3>Connexion</h3>
                        <hr class="my-1" />
                        <p class="h6 text-justify py-3">
                            Connectez-vous et jouez à plusieurs autres continents et pays,
                            en plus, vous pouvez consulter votre historique des jeux joués.
                        </p>

                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text icon icon-email" id="email-input"></div>
                            </div>
                            <input class="form-control" type="text" name="email" placeholder="Entrer votre email" aria-describedby="email-input" required>
                        </div>

                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text icon icon-password" id="password-input"></div>
                            </div>
                            <input class="form-control" type="password" name="password" placeholder="Entrer votre mot de passe" aria-describedby="password-input" required>
                        </div>

                        <button class="btn btn-success w-100 mt-3" type="submit" name="action" value="log-in">Connexion</button>
                    </form>
                </div>

                <?php else : ?>

                <form action="action.php" method="post" style="display:inline-block">
                    <button class="btn btn-danger" aria-expanded="false" type="submit" name="action" value="log-out">
                        Déconnexion
                    </button>
                </form>

                <?php endif ?>

            </li>
        </ul>
    </div>
</nav>

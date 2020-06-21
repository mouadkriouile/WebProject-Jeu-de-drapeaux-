<?php
    define('ABOUT_PHP', true);
    require_once("core.php");

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>A propos du jeu</title>

    <link rel="stylesheet" href="libraries/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="css/style.css"> <!-- Appelez notre style pour remplacer certaines valeurs -->

    <script src="libraries/jquery/jquery.min.js"></script>
    <script src="libraries/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>

<body>

    <?php include("navbar.php"); ?>
    <?php include("alerts.php"); ?>

    <div class="container pt-5">

        <dl class="row">
            <dt class="col-sm-3"> Règles de jeu</dt>
            <dd class="col-sm-9">
                <ul class="list-unstyled">
                    <li class="h5" style="color:red"></li>
                    <li class="text-justify">
                        <p>
                            Vous devez cliquer sur le pays demandé. Vous avez 4 tentatives possibles.
                            Vos points décroissent en fonction des tentatives réalisées pour trouver la bonne zone et la surface du pays demandé.
                            Si le pays est petit (en termes de surface) vous gagnez plus de points que pour des pays plus grands
                            Dans ce jeux le temps court sur toute la partie. Votre bonus de temps est calculé en fin de partie
                            (il est calculé en fonction du temps que vous avez mis pour trouver les pays demandés). </p>
                        <p>
                            Vous avez la possibilié de s'inscrire gratuitement, cela vous offre
                            la possibilité de jouer sur plus de contienent
                            ainsi que visualiser tout vos historiques de jeu. </p>

                    </li>
                </ul>
            </dd>

            <dt class="col-sm-3">Realisé par</dt>
            <dd class="col-sm-9">
                <ul class="list-unstyled">
                    <li>Hajar FAHSI</li>
                    <li>Ismail FIJHI</li>
                    <li>Mouaad KRIOUILE</li>
                </ul>
            </dd>

            <dt class="col-sm-3">Enseignants superviseurs</dt>
            <dd class="col-sm-9">
                <ul class="list-unstyled">
                    <li>Monsieur Mourad Kmimech </li>
                    <li>Monsieur Nistor Grozavu </li>
                    <li>Monsieur Karim Foughali </li>
                </ul>
            </dd>

            <dt class="col-sm-3">Bibliothèques utilisées</dt>
            <dd class="col-sm-9">
                <ul class="list-unstyled">
                    <li>Bootstrap v4.4.1</li>
                    <li>jQuery v3.4.1</li>
                    <li>Leaflet v1.6.0</li>
                </ul>
            </dd>

            <dt class="col-sm-3">Fichiers Sources</dt>
            <dd class="col-sm-9">
                <ul class="list-unstyled">
                    <li>
                        <p>
                            Countries properties by <b>mledoze</b> / Mohammed Le Doze / <i>countries.json</i><br>
                            Licence : ODC Open Database License | <a href="https://github.com/mledoze/countries">Github</a><br>
                        </p>
                    </li>
                    <li>
                        <p>
                            Countries SVG flags by <b>mledoze</b> / Mohammed Le Doze / <i>{country_code_iso_alpha_3}.svg</i><br>
                            Licence : ODC Open Database License | <a href="https://github.com/mledoze/countries">Github</a><br>
                        </p>
                    </li>
                    <li>
                        <p>
                            Countries Geometries by <b>mledoze</b> / Mohammed Le Doze / <i>{country_code_iso_alpha_3}.geo.json</i><br>
                            Licence : ODC Open Database License | <a href="https://github.com/mledoze/countries">Github</a><br>
                        </p>
                    </li>
                </ul>
            </dd>



            <dt class="col-sm-3">Other resources by respective owners<a target="_blank" href="https://icons8.com"></a></dt>
            <dd class="col-sm-9 icon-list">
                <a target="_blank" href="https://commons.wikimedia.org/wiki/File:Flag.svg">Unknown Flag</a>
                <a target="_blank" href="https://freesound.org/people/Bertrof/sounds/351564/">Correct sfx</a>
                <a target="_blank" href="https://freesound.org/people/Bertrof/sounds/131657/">Miss sfx</a>
                <a target="_blank" href="https://freesound.org/people/Bertrof/sounds/351563/">Fail sfx</a>
                <a target="_blank" href="hhttps://www.youtube.com/watch?v=hfa09D2ChFs">Music 'Thinking About It' by Jeremy Korpas</a>

            </dd>

            <dt class="col-sm-3">Icons by <a target="_blank" href="https://icons8.com">Icons8</a></dt>
            <dd class="col-sm-9 icon-list">
                <a target="_blank" href="https://icons8.com/icons/set/email">Email icon</a>
                <a target="_blank" href="https://icons8.com/icons/set/forgot-password">Forgot Password icon</a>
                <a target="_blank" href="https://icons8.com/icon/15140/voice">muted icon</a>
                <a target="_blank" href="https://icons8.com/icon/19316/voice">unmuted icon</a>
                <a target="_blank" href="https://icons8.com/icon/20865/tow-truck">tow-truck icon</a>
            </dd>

        </dl>
    </div>

</body>

</html>

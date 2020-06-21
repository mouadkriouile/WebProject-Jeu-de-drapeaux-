<?php 
    define('INTERFACE_PHP', true);
    define('USERS_PHP', true);
    require_once("core.php");

    // Vérifiez si quelqu'un est connecté car c'est réservé aux administrateurs et aux joueurs uniquement
    if(!$UserConnected || !$IsUserAdmin)
    {
        // Rediriger vers la page principale car l'utilisateur n'est pas connecté ou n'est pas administrateur
        Redirect("index.php"); 
    }

    // Sélectionner tous les utilisateurs et triez-les par heure d'enregistrement
    $usersList = DB_SelectAll("users", "ORDER BY registration_time ASC");

    // Obtenir le nombre des utilisateurs
    $usersNumber = count($usersList);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <!-- Balises meta requises pour HTML5-->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Utilisateurs</title>

    <link rel="stylesheet" href="libraries/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="css/style.css"> <!-- Appeler notre style pour remplacer certaines valeurs -->

    <script src="libraries/jquery/jquery.min.js"></script>
    <script src="libraries/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="js/users-manager.js"></script>

</head>

<body>

    <?php include("navbar.php"); ?>
    <?php include("alerts.php"); ?>

    <div class="container-fluid px-0 py-5">

        <div class="px-5">

            <h4 class="">Utilisateurs</h4>
            <p class="">
                Voici la liste des utilisateurs insctit au site web,
                vous pouvez consulter leurs historiques de jeu, ou supprimer leurs comptes !
                <br>
                Le nombre d'utilisateur total est de : <b><?php echo $usersNumber ?></b> utilsateur(s)
            </p>

        </div>

        <table id="user-table" class="table mt-5 no-select">
            <thead class="thead-dark">
                <tr>
                    <th style="width:5%" class="text-right" scope="col">ID</th>
                    <th class="">Email</th>
                    <th style="width:20%" class="text-center" scope="col">Date d’inscription</th>
                    <th style="width:20%" class="">Actions</th>
                </tr>
            </thead>
            <tbody>

                <?php      
    
                    // A row template
                    $template =
                    '<tr user-id="%d">
                        <th class="text-right align-middle" scope="row">%d</th>
                        <th class="align-middle">%s</th>
                        <td class="align-middle text-center">%s</td>
                        
                        <td class="align-middle">  
                            <div class="row mx-0">
                                <button class="col-sm mr-2 btn btn-sm btn-primary" onclick="showUserStats(this)">Historique</button>
                                <button class="col-sm mr-2 btn btn-sm btn-danger" onclick="deleteUser(this)">Supprimer</button>
                            </div>
                        </td>
                        
                    </tr>';

                    // Format the template with required data
                    foreach($usersList as $user)
                    {
                        echo sprintf(
                            $template,

                            $user["id"],
                            $user["id"],
                            $user["email"],
                            $user["registration_time"]
                        );                    
                    }
                ?>

            </tbody>
        </table>

    </div>

</body>

</html>

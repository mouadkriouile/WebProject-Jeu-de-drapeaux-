<?php
    // Rediriger l'utilisateur vers un lien
    function Redirect($link)
    {
        header("Location: ".$link);
        die();
    }

    // Redirige l'utilisateur vers la page précédente
    function RedirectToPreviousPage()
    {
        if(isset($_SERVER['HTTP_REFERER']))
            // Aller à la page précédente si il existe 
            Redirect($_SERVER['HTTP_REFERER']);
        else
            // Sinon aller à home
            Redirect("./");
    }

    // Afficher une page d'erreur
    function PrintErrorPage($message)
    {
        die("
        <!DOCTYPE 'html'>
        <html>
            <head>
                <title>Erreur</title>
            </head>
            <body>
                <pre style='white-space: pre-wrap;'>$message\n\nGo to the home page from <a href='./'>here!</a></pre>
            </body>
        </html>
        ");
    }

    // Prépare une alerte à afficher
    function ShowAlert($message)
    {
        $_SESSION["message-text"] = $message;
    }

    // Vérifie si un e-mail est valide
    function CheckEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    // Obtenir la date et l'heure actuelles
    function CurrentDateTime()
    {
        $d = new DateTime('NOW');
        return $d->format('Y-m-d H:i:s');
    }

    // Définir la session utilisateur avec les données utilisateur pour accéder ultérieurement
    function SetUserSession($userData)
    {
            $_SESSION["UserConnected"] = true;
            $_SESSION["UserData"] = $userData;
    }

    // Détruit la session utilisateur (déconnexion)
    function UnsetUserSession()
    {
        unset($_SESSION["UserConnected"]);
        unset($_SESSION["QuizTracker"]);
        unset($_SESSION["UserData"]);
    }

    // Vérifie si un continent existe
    function IsValidContinent($continent)
    {        
        return array_key_exists($continent, $GLOBALS["CONTINENTS"]);
    }

    // fonction qui sélectionne un continent aléatoire
    function SelectRandomContinent()
    {    
        // Clonez le tableau des continents
        $continents = $GLOBALS["CONTINENTS"];
        
        // Mélangez le array des continents
        shuffle($continents);
        
        // Mélangez le array des continents, le premier est le monde entier et nous ne sommes pas connectés
        while(!$continents[0]["visitor_allowed"] && !$GLOBALS["UserConnected"])
            shuffle($continents);
        
        // returner le continents séléctionner
        return $continents[0];
    }
    
?>

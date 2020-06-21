<!-- Charger dans le alert handler javascript -->
<script src="js/alert-handler.js"></script>

<div class="floating-alert alert alert-dismissible fade" role="alert">

    <h5 class="alert-heading">Message :</h5>

    <button type="button" class="close" aria-label="Close" onclick="hideAlert()">
        <span aria-hidden="true">&times;</span>
    </button>

    <span class="alert-message">
        <?php 
            // Si la session a un message à signaler à l'utilisateur
            if(isset($_SESSION["message-text"]))
            {
                // Afficher le message
                echo $_SESSION["message-text"];

                // Et videz le support de message pour ne pas le réimprimer lors du prochain chargement
                unset($_SESSION["message-text"]);
            }
        ?>
    </span>
</div>

// Une expression régulière pour vérifier si l'email est bien formaté
//
function validateEmail(email) {
    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email))
        return true;

    return false;
}

// Attribuer des écouteurs d'événements une fois le document prêt
$(document).ready(function () {

    // Se concentrer sur la première entrée lors de l'ouverture du modele d'inscription
    $("#signup-modal").on("shown.bs.modal", function () {
        $("#signup-email").trigger("focus");
    })

    // Écoutez la soumission du formulaire et vérifiez la saisie
    $("#signup-form").submit(function (e) {

        // Empêcher la soumission du formulaire
        e.preventDefault();

        // Obtenir les valeurs des entrées d'utilisateur
        var email = $("#signup-email").val();
        var password = $("#signup-password").val();
        var repeatedPassword = $("#signup-confirm-password").val();

        // Vérifier l'adresse email
        if (!validateEmail(email)) {
            showAlert("Cette adresse e-mail n'est pas valide!", alertStyles.warning);
            return;
        }

        // Vérifier si le password est vide
        if (password.length == 0) {
            showAlert("Please insert a password to register!", alertStyles.warning);
            return;
        }

        // Vérifier si le password est vide et il est le meme
        if (repeatedPassword != password) {
            showAlert("Le mot de passe répété doit correspondre à votre premier mot de passe!", alertStyles.warning);
            return;
        }

        // La requete de données
        var request = {
            action: "check-email",
            email: email
        };

        // Executer la requete 
        $.post("action.php", request)
            .done(function (data) {

                // Si le serveur dit que tout est bon
                if (data["success"]) {

                    if (data["emailExists"] == true) {
                        // Afficher que cet email existe deja
                        showAlert("Cet email existe déjà dans la base de données!", alertStyles.danger);
                    } else {
                        // Reprendre la soumission du formulaire après avoir tout vérifié!
                        e.target.submit();
                    }
                } else {
                    // Afficher le message de résultat écrit par le serveur
                    showAlert(data["message"], alertStyles.danger);
                }
            })
            .fail(function () {
                // Afficher l'échec de cette demande
                showAlert("La requête a échoué!", alertStyles.danger);

            })
    })
})

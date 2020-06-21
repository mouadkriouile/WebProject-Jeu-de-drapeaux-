/*----------------------------------------------
 Users Manager Java Script File
    
 This script will allow admins to look players
 game history or to delete them
----------------------------------------------*/

// Ajax demande la suppression du joueur
//
function ajaxDeleteUser(userID) {

    // Demander confirmation à l'utilisateur
    var response = confirm(
        "Voulez-vous vraiment supprimer cet utilisateur?\n" +
        "La suppression supprimera toutes les données relatives à cet utilisateur, y compris son historique de jeux"
    );

    // Renvoyer si l'utilisateur n'a pas confirmé
    if (!response)
        return;

    // Les données de la requete
    var request = {
        action: "delete-user",
        id: userID
    };

    // Executer la requete
    $.post("action.php", request)
        .done(function (data) {

            if (data["success"]) {

                // Supprimer la ligne si l'utilisateur a réussi la suppression
                $("tr[user-id='" + userID + "']").fadeOut(800, function () {
                    $(this).remove();
                });

                // Afficher le message du resultat 
                showAlert(data["message"], alertStyles.success);
            } else {
                // Afficher le message de résultat écrit par le serveur
                showAlert(data["message"], alertStyles.danger);
            }
        })
        .fail(function () {
            // Afficher le message du resultat 
            showAlert("La requête de suppression a échoué!", alertStyles.danger);
        });
}


/*----------------------------------------------
       fonctions ci-dessous : Buttons onclick 
----------------------------------------------*/

// Demande la suppression du joueur
//
function deleteUser(buttonElement) {

    var userID = $(buttonElement).closest("[user-id]").attr("user-id");
    ajaxDeleteUser(userID);

}

// Ouvrir les statistiques des joueurs dans la même fenêtre
//
function showUserStats(buttonElement) {

    var userID = $(buttonElement).closest("[user-id]").attr("user-id");
    window.open("stats.php?user-id=" + userID, "_self");

}

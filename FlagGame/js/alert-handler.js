/*----------------------------------------------
 Alert Manager Java Script File
    
 This script allows other scripts to show
 alerts everywhere in the website
----------------------------------------------*/

// Variables contenant des objets jQuery
//
var alertObject = null;
var alertMessageObject = null;

// Array d'objets Styles d'alerte
//
const alertStyles = {

    primary: {
        class: "alert-primary"
    },
    secondary: {
        class: "alert-secondary"
    },
    success: {
        class: "alert-success"
    },
    danger: {
        class: "alert-danger"
    },
    warning: {
        class: "alert-warning"
    },
    info: {
        class: "alert-info"
    },
    light: {
        class: "alert-light"
    },
    dark: {
        class: "alert-dark"
    }
}

// Affiche un message sur l'élément d'alerte
//
function showAlert(message, styleClass = alertStyles.warning) {

    // Masquer l'alerte
    hideAlert();

    // Attendre un peu pour ajouter un effet popping
    //
    setTimeout(function () {

        // Supprimer tous les styles
        for (var key in alertStyles) {
            alertObject.removeClass(alertStyles[key].class);
        }

        // Définir le style souhaité
        alertObject.addClass(styleClass.class);

        // Définir le message
        alertMessageObject.html(message);

        // Afficher l'alerte
        alertObject.addClass("show");

    }, 300);
}

// Masquer l'élément d'alerte
//
function hideAlert() {
    alertObject.removeClass("show");
}

// Lorsque le document est chargé
//
$(document).ready(function () {

    // Sélectionner un objet à l'aide de jQuery
    alertObject = $(".alert");

    // Sélectionnez l'objet conteneur de messages
    alertMessageObject = $(".alert-message");

    // S'il y a déjà un message, montrons-le
    if (alertMessageObject.html().trim().length > 0)
        showAlert(alertMessageObject.html().trim(), alertStyles.primary);

    $("body").click(hideAlert);

});

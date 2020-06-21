// les objets jQuery
var passwordInput = null;
var submitButton = null;
var outputIframe = null;

// Variables globals
var docReady = false; // Indique que le document est prêt
var building = false; // Indique si nous construisons
var interval = null; // Contient l'ID d'intervalle

// Faire défiler l'iframe vers le bas
//
function scrollToBottom() {
    try {
        var iframe = outputIframe.contents();
        iframe.scrollTop(iframe.height());
    } catch (e) {

    }
}

// Submits form
//
function submitForm(e) {
    // Empêcher la soumission du formulaire
    e.preventDefault();

    // Si nous construisons on retourne
    if (!docReady || building)
        return;

    // Nous construisons
    building = true;

    // Soumettre le formulaire
    e.target.submit();

    // Effacer le mot de passe et verrouiller
    submitButton.addClass("disabled");
    passwordInput.prop("readonly", true);;

    // Démarrer l'intervalle de défilement automatique
    interval = setInterval(scrollToBottom, 1); // 1ms
}

// Rappel du chargement de l'iframe
//
function iframeLoaded() {
    // Mettre fin au défilement automatique
    clearInterval(interval);

    // Déverrouillez le bouton
    submitButton.removeClass("disabled");
    passwordInput.prop("readonly", false);

    // Effacez le mot de passe
    passwordInput.val("");

    // Faites défiler une dernière fois
    scrollToBottom();

    // Construction est fini
    building = false;
}

// Lorsque le document est pret
//
$(document).ready(function () {

    // Obrenir les objets DOM 
    passwordInput = $("#password-input");
    submitButton = $("#submit-btn");
    outputIframe = $("#output_frame");

    // Quand nous soumettons le formulaire
    $("#form").submit(submitForm);

    // Lorsque l'iFrame est chargé
    $("#output_frame").on("load", iframeLoaded);

    // Document prêt
    docReady = true;
});

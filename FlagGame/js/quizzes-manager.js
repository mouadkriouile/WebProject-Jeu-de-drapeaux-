/*----------------------------------------------
 quizzes Manager Java Script File
    
 Ce script permettra aux administrateurs d'ajouter, de modifier ou
  supprimer le quiz du jeu!
----------------------------------------------*/

/*----------------------------------------------
    des variables et des jQuery object holders
----------------------------------------------*/

var currentContinent = null; // Le continent actuellement sélectionné
var currentContinentSize = null; // Le nombre de pays disponibles sur le continent actuel

var selectedCountries = null; // Array qui contiendra les pays à soumettre sur le questionnaire nouveau / modifié
var currentQuizId = 0; // ID du questionnaire à modifier, un nouveau quiz sera inséré, il est égal à 0

var continentNameHolders = null; // Les éléments portant le nom du continent
var quizzesTableBody = null; // L'élément de corps de table qui contiendra les questionnaires 

var modalElement = null; // L'élément modal lui-même
var modalCountries = null; // Le conteneur des pays dans l'élément modal

var isBusy = false; // True lorsqu'une demande ajax est en cours de traitement


/*----------------------------------------------
     Quelques tempplates html pour vous faciliter la tâche
      pour ajouter des éléments plus tard dans le document
----------------------------------------------*/

// HTML template pour la ligne d'une table de questionnaire
//
var quizTemplate =
    `<tr quiz-id=":id" quiz-countries=":cca3_list"> 
        <th class="text-right align-middle" scope="row">:id</th>
        <td class="align-middle countries-holder">:countries</td>
        <td class="align-middle">  
            <div class="row mx-0">
                <button class="col-sm mr-2 btn btn-sm btn-warning" onClick="editQuiz(this)">Modifier</button>
                <button class="col-sm mr-2 btn btn-sm btn-danger" onClick="deleteQuiz(this)">Supprimer</button>
            </div>
        </td>
    </tr>`;

// HTML template pour les boutons des pays modaux
//
var countryTemplate =
    `<div class="country-btn" country-code=":cca3">
        :name
    </div>`;


/*----------------------------------------------
  Quelques fonctions utiles nécessaires au script
----------------------------------------------*/

// Génère une liste d'entiers uniques aléatoires
//
function generateUniqueRandomFromRange(size, min, max) {

    if ((max - min + 1) < size) // Si la taille est inférieure à la taille des plages
        size = max - min + 1; // Réduisez la taille pour éviter de boucler indéfiniment

    // Le resultat array
    var result = [];

    // Boucler tant que la taille est plus petite que demandée
    while (result.length < size) {
        // Générer un nombre entre le min et le max
        var r = Math.floor(Math.random() * (max - min + 1) + min);

        // S'il n'existe pas, insérer-le
        if (result.indexOf(r) === -1) result.push(r);
    }

    // Renvoie le résultat trié pour rendre la commande cohérente
    return result.sort();
}

// Compare si 2 arrays ont les données de sauvegarde
// Les arrays doivent être triés pour que cette fonction fonctionne correctement
//
function areArraysEqual(a, b) {

    if (a.length != b.length)
        return false;

    for (var i = 0; i < a.length; i++) {
        if (a[i] != b[i])
            return false;
    }

    return true;
}

/*----------------------------------------------
          Modal functions and methods
----------------------------------------------*/

// Repopulates the modal countries holder by the countries of the current selected continent
//
function modalRepopulateCountries() {

    // Vider la liste des pays dans le modal
    modalCountries.empty();

    // Comptons le nombre d'objets dans le array d'objets
    // Le array d'objets ne prend pas en charge .length alors comptons est dans la boucle suivante
    currentContinentSize = 0;

    // Pour chaque clé (cca3), nous obtenons le nom
    for (var cca3 in countriesList) {

        // Get the country by country code (cca3)
        var country = countriesList[cca3];

        // Si le pays ne fait pas partie du continent sélectionné et que nous ne sélectionnons pas le monde entier, nous le sautons
        if (country["continent"] != currentContinent && currentContinent != "World")
            continue;

        // Incrémenter le compteur de pays
        currentContinentSize++;

        // Créer l'élément bouton
        var countryBtn = $(countryTemplate.replace(":cca3", cca3).replace(":name", country["name"]));

        // Ajoutez-le à la liste
        modalCountries.append(countryBtn);

        // Ajouter un click event listener
        countryBtn.click(function () {
            $(this).toggleClass("country-selected");
        });
    }
}

// Désélectionne tous les pays du modal
//
function modalUnselectCountries() {
    // Effacer tous les pays sélectionnés
    $(".country-selected").removeClass("country-selected");
}

// Ouvrir modal window
//
function modalOpen() {
    modalElement.modal("show"); // Ouvrir modal
}

// Fermer modal window
//
function modalClose() {
    modalElement.modal("hide"); // Fermer modal
}

/*----------------------------------------------
    Table contenant les fonctions de gestions
----------------------------------------------*/

// Insère une ligne dans la table
//
function tableInsertRow(index, countries) {

    // Initialiser un titulaire (de travées) pour les noms de pays
    var countriesSpans = "";

    // Obtenez chaque nom de code de pays
    for (var i = 0; i < countries.length; i++) {
        countriesSpans += '<span class="country-span">' + countriesList[countries[i]]["name"] + '</span>';
    }

    // Mettre en forme la ligne du table en fonction des données
    var entry = quizTemplate
        .replace(":id", index) // Remplacer le premier index
        .replace(":id", index) // Remplacer le deuxième index
        .replace(":id", index) // Remplacer le troisème index
        .replace(":cca3_list", countries.join(" ")) // Remplacer les pays 
        .replace(":countries", countriesSpans); // Remplacer les pays 

    // Insérez la ligne dans le table
    quizzesTableBody.append(entry);
}

// Met à jour les pays affichés dans une ligne du table
//
function tableUpdateRow(index, countries) {

    // Obtenez la ligne contenant cette entrée
    var quizRow = $("[quiz-id='" + index + "']");

    // Mettre à jour l'attribut avec le nouveau code de pays
    quizRow.attr("quiz-countries", countries.join(" "));

    // Initialiser un titulaire (de spans) pour les noms de pays
    var countriesSpans = "";

    // Obtenez chaque nom de code de pays
    for (var i = 0; i < countries.length; i++) {
        countriesSpans += '<span class="country-span">' + countriesList[countries[i]]["name"] + '</span>';
    }

    // Disparaitre une ligne pour ajouter des effets
    quizRow.delay(500).fadeOut(200, function () {

        // Mettre à jour le div.countries_holder avec de nouveaux noms de pays
        $(this).children(".countries-holder").html(countriesSpans);

        // apparaitre ligne après le changement
        $(this).fadeIn(500);
    });
}

// Supprime une ligne du table
//
function tableRemoveRow(index) {

    // Obtenez la ligne contenant cette entrée
    var quizRow = $("[quiz-id='" + index + "']");

    // fadeOut avant de retirer
    quizRow.fadeOut(800, function () {

        // Supprimer la ligne
        $(this).remove();
    });
}

/*----------------------------------------------
       des fonctions de requete Ajax  ci-dessous
----------------------------------------------*/

// Demander la liste des questionnaire du continent sélectionné
//
function ajaxGetquizzesList() {

    // Return if busy
    if (isBusy)
        return;

    // Script is busy sending request
    isBusy = true;

    // Empty the body
    quizzesTableBody.empty();

    // The request data
    var request = {
        action: "get-quiz-list",
        continent: currentContinent
    };

    // Executer la requete
    $.post("action.php", request)
        .done(function (data) {

            // si tout va bien
            if (data["success"]) {

                // Obtenez le nombre de questionnaire disponibles
                var numquizzes = data["quizzes"].length;

                // Afficher le nombre de questionnaires chargés
                showAlert(numquizzes + " questionnaire(s) chargé(s) pour ce continent !", alertStyles.info);

                // Repopulating modal's countries
                modalRepopulateCountries();

                // Parcourez les questionnaires et insérez-les dans la  table
                for (var i = 0; i < numquizzes; i++) {
                    // Les pays sont retournés sous forme de array, alors rejoignons-les par espace (' ')
                    tableInsertRow(data["quizzes"][i]["id"], data["quizzes"][i]["countries"].split(" "));
                }

            } else {
                // Afficher le message de résultat écrit par le serveur
                showAlert("Erreur de chargement des qustionnaires pour ce continent !", alertStyles.danger);
            }

            // La requete est faite
            isBusy = false;
        })
        .fail(function () {
            // Afficher l'échec de cette demande
            showAlert("La requête de cgargement a échoué!", alertStyles.danger);

            // Request is done
            isBusy = false;
        })
}

// Soumettre les pays actuellement sélectionnés dans la table
// Si l'ID du quiz actuel est égal à 0, le serveur ajoutera le questionnaire
// sinon le questionnaire avec l'id donné sera mis à jour
//
function ajaxSubmitQuiz() {

    // Return if busy
    if (currentContinent == null || isBusy)
        return;

    // Script is busy sending request
    isBusy = true;

    // The request data
    var request = {
        action: "process-quiz",
        continent: currentContinent,
        countries: selectedCountries,
        quiz_id: currentQuizId
    };

    // Executer la requete
    $.post("action.php", request)
        .done(function (data) {

            // si tout va bien
            if (data["success"]) {

                // Questionnaire soumis donc fermons le modal
                modalClose();

                if (currentQuizId == 0) {
                    // Insérer la ligne dans la table si nous ne modifions pas
                    tableInsertRow(data["quiz_id"], selectedCountries);

                } else {

                    // Mettre à jour les pays affichés dans la ligne modifiée          
                    tableUpdateRow(data["quiz_id"], selectedCountries);

                    if (data["updated"]) {
                        // Alerter l'utilisateur que le quiz a été mis à jour
                        showAlert("Le questionnaire avec l'identifiant " + data["quiz_id"] + " a été mis à jour.", alertStyles.success);
                    } else {
                        // Alerter l'utilisateur que le quiz n'a pas été mis à jour
                        showAlert("Aucune modification détectée pour la mise à jour", alertStyles.warning);
                    }
                }

            } else {
                // Afficher le message de résultat écrit par le serveur
                showAlert(data["message"], alertStyles.danger);
            }

            // Requete est faite
            isBusy = false;
        })
        .fail(function () {
            // Afficher l'échec de cette demande
            showAlert("La requête a échoué!", alertStyles.danger);

            // Requete est faite
            isBusy = false;
        })
}

// Ajax changera la permission du continent
//
function updateContinentPermission(allowContinent) {

    // Return if busy
    if (currentContinent == null || isBusy)
        return;

    // Script isBusy envoi requete
    isBusy = true;

    // The request data
    var request = {
        action: "toggle-continent-permission",
        continent: currentContinent,
        allowed: allowContinent
    };

    // Executer the requete
    $.post("action.php", request)
        .done(function (data) {

            // si tout va bien
            if (data["success"]) {

                if (allowContinent) {
                    // Insérez la ligne dans la table si nous ne modifions pas
                    showAlert("Ce mode est maintenant autorisé à être joué par les utilisateurs non enregistrés.", alertStyles.success);

                    // Changer la couleur du continent
                    $("[select-mode='" + currentContinent + "']").removeClass("limited-mode").addClass("allowed-mode");

                } else {

                    // Mettre à jour les pays affichés dans la ligne modifiée
                    showAlert("Ce mode est maintenant interdit aux utilisateurs non enregistrés!", alertStyles.dark);

                    // Changer la couleur du continent
                    $("[select-mode='" + currentContinent + "']").removeClass("allowed-mode").addClass("limited-mode");
                }

            } else {
                // Afficher le message de résultat écrit par le serveur
                showAlert(data["message"], alertStyles.danger);
            }

            // Request is done
            isBusy = false;
        })
        .fail(function () {
            // Afficher l'échec de cette requete
            showAlert("La requête a échoué!", alertStyles.danger);

            // Requete est faite
            isBusy = false;
        })

}

// requete la suppression d'un quiz avec l'identifiant donné
//
function ajaxDeleteQuiz(index) {

    // Return if busy
    if (currentContinent == null || isBusy)
        return;

    // demande la confirmation d'utilisateur
    if (!confirm("Voulez-vous vraiment supprimer ce questionnaire?\n"))
        return;

    // Script is busy sending request
    isBusy = true;

    // The request data
    var request = {
        action: "delete-quiz",
        quiz_id: index
    };

    // Executer la requete
    $.post("action.php", request)
        .done(function (data) {

            // si tout va bien
            if (data["success"]) {

                // supprimer la ligne
                tableRemoveRow(index);

                // Afficher le message de résultat
                showAlert(data["message"], alertStyles.success);

            } else {
                // Afficher le message de résultat envoyé par le serveur
                showAlert(data["message"], alertStyles.danger);
            }

            // Requete est faite
            isBusy = false;
        })
        .fail(function () {
            // Afficher l'échec de cette demande
            showAlert("La requête de suppression a échoué!", alertStyles.danger);

            // Requete est faite
            isBusy = false;
        })
}


/*----------------------------------------------
       fonctions buttons onclick si dessous
----------------------------------------------*/

// ouvrir modal pour ajouter un nouveau questionnaire
//
function addNewQuiz() {

    // Return if busy
    if (currentContinent == null || isBusy)
        return;

    // Vider le array des pays sélectionnés
    selectedCountries = Array();

    // Unselect countries in the modal
    modalUnselectCountries();

    // ID 0 puisque nous ajoutons un questionnaire
    currentQuizId = 0;

    // Ouvrez le modal pour que l'utilisateur puisse sélectionner les pays
    modalOpen();
}

// Ajoute un quiz avec des pays sélectionnés au hasard du continent sélectionné
//
function addRandomNewQuiz() {

    // Return if busy
    if (currentContinent == null || isBusy)
        return;

    // Empty the country holder
    selectedCountries = Array();

    // le questionnaire sera ajouter
    currentQuizId = 0;

    // Générez un array d'indices uniques
    var uniqueNumbers = generateUniqueRandomFromRange(QUIZ_MAX_COUNTRIES, 0, currentContinentSize - 1);

    // Sélectionnez les codes de pays par les indices aléatoires sélectionnés
    for (var i = 0; i < uniqueNumbers.length; i++) {
        selectedCountries[i] = modalCountries.children().eq(uniqueNumbers[i]).attr("country-code");
    }

    // Soumettre le quiz généré
    ajaxSubmitQuiz();
}

// Ouvre le modal avec les pays du questionnaire déjà sélectionnés pour l'édition
//
function editQuiz(buttonElement) {

    // Return if busy
    if (currentContinent == null || isBusy)
        return;

    // Récupère l'élément de ligne parent du bouton cliqué 
    var quiz_holder = $(buttonElement).closest("tr");

    // Désélectionner tous les pays
    modalUnselectCountries();

    // Obtenez l'identifiant du questionnaire - un identifiant différent de zéro modifiera le quiz une fois soumis
    currentQuizId = quiz_holder.attr("quiz-id");

    // Charger dans les pays du quiz dans le array des pays sélectionnés
    selectedCountries = quiz_holder.attr("quiz-countries").split(" ");

    // Parcourez les pays et sélectionnez-les dans le modal
    for (var i = 0; i < selectedCountries.length; i++) {
        $("[country-code='" + selectedCountries[i] + "']").addClass("country-selected");
    }

    // Ouvrez le modal pour que l'utilisateur le modifie
    modalOpen();
}

// Supprimer le questionnaire
//
function deleteQuiz(buttonElement) {

    // Return if busy
    if (currentContinent == null || isBusy)
        return;

    // Récupère l'élément de ligne parent du bouton cliqué
    var quiz_holder = $(buttonElement).closest("tr");

    // Récupère l'identifiant du quiz stocké dans l'attribut "quiz-id" de l'élément de ligne
    var quiz_id = quiz_holder.attr("quiz-id");

    // Requete de supression
    ajaxDeleteQuiz(quiz_id);
}

// Sélectionne des pays aléatoires dans le modal
//
function modalSelectRandomCountries() {

    // Return if busy
    if (currentContinent == null)
        return;

    // désélectionner les pays actuellement sélectionnés
    modalUnselectCountries();

    // Générez un array d'indices uniques
    var uniqueNumbers = generateUniqueRandomFromRange(QUIZ_MAX_COUNTRIES, 0, currentContinentSize - 1);

    // Sélectionnez les indices aléatoires sélectionnés
    for (var i = 0; i < uniqueNumbers.length; i++) {
        modalCountries.children().eq(uniqueNumbers[i]).addClass("country-selected");
    }

}

// Enregistre les modifications et soumettre le questionnaire
//
function modalSaveChanges() {

    // Return if busy
    if (currentContinent == null || isBusy)
        return;

    // Vider le array des pays sélectionnés
    tempSelectedCountries = Array();

    // Obtenir les pays sélectionnés
    $(".country-selected").each(function (index) {
        tempSelectedCountries[index] = $(this).attr("country-code");
    });

    // Vérifier si le questionnaire n'a rien à jouer
    if (tempSelectedCountries.length <= 0) {
        showAlert("Vous n'avez rien sélectionné à soumettre!", alertStyles.primary)
        return;
    }

    // Vérifiez si le quiz ne dépasse pas le maximum autorisé
    if (tempSelectedCountries.length > QUIZ_MAX_COUNTRIES) {
        showAlert("Vous avez choisi plus que le maximum de pays autorisés. Vous ne pouvez en choisir que " + QUIZ_MAX_COUNTRIES + " ou moins!", alertStyles.primary)
        return;
    }

    // Comparez si le quiz a vraiment changé car le serveur génère une erreur lorsque les mêmes données sont envoyées
    if (areArraysEqual(selectedCountries, tempSelectedCountries)) {
        showAlert("Vous n'avez rien changé dans ce questionnaire!", alertStyles.warning)
        return;
    }

    // Mettre à jour le array des pays sélectionnés
    selectedCountries = tempSelectedCountries;

    // Soumettre le questionnaire
    ajaxSubmitQuiz();
}


// Lorsque le document est entièrement chargé et prêt
//
$(document).ready(function () {

    // Obtenez l'élément modal et les pays contenant div
    modalElement = $("#quizEditor");
    modalCountries = $("#modal-countries-list");

    // Obtenez le corps du table qui contiendra les quiz
    quizzesTableBody = $("#quizzes-table-body");

    // Le texte déroulant du mode
    continentNameHolders = $(".continent-name");

    // Ajouter un event listener lorsque nous cliquons sur un continent
    $("[select-mode]").click(function () {

        // Return if busy
        if (isBusy)
            return;

        // Obtenez le continent cliqué à partir de la valeur stockée dans l'attribut "select-mode"
        currentContinent = $(this).attr("select-mode");

        // Définissez le titre de l'élément déroulant sur le continent choisi
        continentNameHolders.text($(this).text());

        // Charger dans le continent sélectionné
        ajaxGetquizzesList();

    });
});
 
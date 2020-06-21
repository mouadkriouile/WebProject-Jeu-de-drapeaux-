/*----------------------------------------------
 Game handler Java Script File
    
Ce script est le script qui exécute le jeu
  et gére tous les clics sur les boutons, obtenir un questionnaire,
  préparer un questionnaire, montrer les frontières des pays,
  calculer et soumettre un score et plus encore ...
----------------------------------------------*/

/*----------------------------------------------
    Constants
----------------------------------------------*/

// prochain timeout automatique en millisecondes
const autoNextTimeOutMilliseconds = 700;

// Array des états des boutons avec noms et index
const buttonStates = {
    wait: {
        id: 0,
        text: "Chargement...",
    },

    new_game: {
        id: 1,
        text: "Nouvelle partie",
    },

    skip_question: {
        id: 2,
        text: "Passer la question",
    },
}

// Leaflet borders et styles de remplissage pour afficher les pays
const LayerStyles = {
    invisible: {
        // Les paramètres de remplissage intérieur
        fillColor: "rgb(0, 0, 0)",
        fillOpacity: (gameParameters["isAdmin"] ? 0.2 : 0.0),

        // Les paramètres de bordure
        color: "rgb(0, 0, 0)",
        opacity: (gameParameters["isAdmin"] ? 0.4 : 0.0),
        weight: 1,

    },
    red: {
        fillColor: "#dd1010",
        fillOpacity: 0.7,

        color: "#9d1515",
        opacity: 0.5,
        weight: 2,
    },
    blue: {
        fillColor: "#339bf4",
        fillOpacity: 0.7,

        color: "#165bb9",
        opacity: 0.5,
        weight: 2,
    },
    yellow: {
        fillColor: "#f5e410",
        fillOpacity: 0.7,

        color: "#e89800",
        opacity: 0.5,
        weight: 2,
    },
    green: {
        fillColor: "#1bdb1b",
        fillOpacity: 0.7,

        color: "#0d880d",
        opacity: 0.5,
        weight: 2,
    },

    circle: {
        fillColor: "#d2fdff",
        fillOpacity: 0.3,

        color: "#fff",
        opacity: 0.6,
        weight: 1,

        riseOnHover: false, // Empêcher le survol de la souris
        interactive: false, // Empêcher le clic de souris
        radius: 0,
    }
}

// Comment multiplier le score de la question / du pays au clic positif en fonction du nombre de tentatives
// Cet array contrôle également le nombre de tentatives accordées aux joueurs
// ..
// Exemple de paramètres par défaut: 
//   [4 tentatives données]
//   - 100% score si répondu correctement au premier essai, 
//   -  75% score si répondu correctement au deuxieme essai, 
//   -  50% score si répondu correctement au troisieme essai, 
//   -  25% score si répondu correctement au quatrieme essai, 
//
// Le dépassement est la quantité ajoutée au rayon du cercle d'aide en kilomètres
//
const answerAttemptMultiplier = [
    {
        multiplier: 1,
        overshoot: 0, // Premier clic sans dépassement
    },
    {
        multiplier: 0.75,
        overshoot: 800,
    },
    {
        multiplier: 0.50,
        overshoot: 400,
    },
    {
        multiplier: 0.25,
        overshoot: 0,
    }
];

// The times multipliers
// Every time multiplier has a max time in seconds
// If player exceeds all the max times the multiplier will be the (minimum default)
//
const timeMultiplierMinimumDefault = 1;
const timeMultiplier = [
    {
        maxTime: 15,
        multiplier: 150,
    },
    {
        maxTime: 30,
        multiplier: 100,
    },
    {
        maxTime: 60,
        multiplier: 75,
    },
    {
        maxTime: 90,
        multiplier: 50,
    },
    {
        maxTime: 120,
        multiplier: 25,
    },
];

/*----------------------------------------------
    Variables et document objects holders
----------------------------------------------*/

// Variables liées à map et leaflet 
var map = null; // The map object
var countryLayers = null; // La couche contenant les frontières des pays
var countryClicked = false; // Le clic sur la carte était au-dessus d'un pays dessiné par geoJSON
var countryIndex = false; // L'index du dernier pays cliqué
var helpCircle = null;

// Variables liées à la musique de fond et aux effets sonores
var backGroundMusicToggleBtn = null; // button element de musique du background 
var sounds_muted = false; // Sound state boolean (ON/OFF)
var background_music = null; // background music object
var sfx_correct = null; // Reponse correcte sound effect object
var sfx_miss = null; // Mauvais clic sound effect object
var sfx_fail = null; // Question échouée sound effect object

// Les informations Panel object
var infoPanel_Container = null; // L'information panel elle-même (elle contient les éléments ci-dessous)
var infoPanel_CountryFlag = null; // element InfoPanel au drapeau holder
var infoPanel_ProgressBar = null; // element InfoPanel au conteneur contenant des boutons pour chaque pays
var infoPanel_Distance = null; // InfoPanel element de distance
var infoPanel_ClickAttempts = null; // InfoPanel element aux tentatives de clic
var infoPanel_BaseScore = null; // InfoPanel element au score de base
var infoPanel_TimeFactor = null; // InfoPanel element au multiplicateur de temps
var infoPanel_TotalScore = null; // InfoPanel element au score total
var infoPanel_Time = null; // InfoPanel element de temps 
var infoPanel_CountryName = null; // InfoPanel element de nom de pays
var infoPanel_CountryCapital = null; // InfoPanel element de nom de capital du pays
var infoPanel_CountryArea = null; // InfoPanel element de surface de pays
var infoPanel_WikiLink = null; // InfoPanel element de wiki link

// les variables big button 
var autoNextTimeOut = null; // L'ID du délai d'attente actuel
var theBigButton = null; // L'objet du big button
var currentButtonState = 0; // l'etat du big button actuel

// Variables de jeu
var timeInterval_ID = 0; // L'ID d'intervalle de temps du compteur horaire actuel
var totalTimeInSeconds = 0; // Temps de lecture en secondes
var currentTimeMultiplier = 0; // Multiplicateur de temps actuel (en fonction du temps)
//
var quiz_data = null; //Données de questionnaires, variable contenant les informations du questionnaires, données de taille et de polygone
var quiz_size = 0; // La taille des questionnaires (Nombre de pays à jouer)
//
var baseScore = 0; // Score de base du joueur (non multiplié)
var totalScore = 0; // Score total du joueur après avoir été multiplié par le multiplicateur de temps
var isScoreSent = false; // Indique si la partition est envoyée
//
var isQuizOver = false; // Indique si le questionnaire est terminé
var clickAttempts = 0; // Le nombre de clics / tentatives (basé sur 0)
var current_question = 0; // Current question/country on the quiz
var quiz_results = null; // Une chaîne contenante la façon dont le joueur répond à chaque question / pays dans questionnaire
var allowViewingQuestions = false; // True si le joueur est autorisé à vérifier chaque pays


/*----------------------------------------------
      fonction et methodes Map & Leaflet 
----------------------------------------------*/

// Préparer la map et tuiles et des centres sur les coordonnées
//
function prepareMap() {

    // Les coins des bornes de la carte
    var northWest = L.latLng(90, -360);
    var southEast = L.latLng(-90, 360);

    // Créer les limites de la carte
    var mapBounds = L.latLngBounds(northWest, southEast);

    // Initialiser stamen map layers en utilisant apis (nous utilisons StamenWaterColor)
    var stamenLayer = L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/watercolor/{z}/{x}/{y}.{ext}', {
        attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        ext: 'jpg'
    });

    // Initialiser une instance map en utilisant la bibliotheque Leaflet 
    // 'map-canvas' est notre map holder div
    map = new L.Map('map-canvas', {
        minZoom: 2, // definir notre minimum zoom
        maxZoom: 18, // definir notre maximum zoom
        zoom: 3, // zoom initial 

        // Limiter la zone du jeu 
        maxBounds: mapBounds,

        // randre la map répétable, lorsque nous faisons défiler le dépliant nous fera sans problème revenir à la carte d'origine
        worldCopyJump: true,

        // Center la map au center du continent selectionné
        center: L.latLng(gameParameters["center"])
    });

    // Ajouter stamen layer à Leaflet map instance
    map.addLayer(stamenLayer);

    // Disactiver le zooming avec doubleclick
    map.doubleClickZoom.disable();

    // Ajouter un click handler
    map.on('click', checkAnswer);
}

// Réinitialise le zoom de la carte et centre le continent
//
function resetMap() {
    // Aller au centre du continent et zoomer / dézoomer au niveau 3
    map.flyTo(L.latLng(gameParameters["center"]), 3);
}

// Masquer toutes les couches de pays
//
function hideAllCoutryLayers() {
    // masquer les layers affichés
    for (var i = 0; i < quiz_size; i++) {

        if (!allowViewingQuestions) {
            countryLayers[i].setStyle(LayerStyles.invisible);
        } else {

            if (questionButton(i).hasClass("failed"))
                countryLayers[i].setStyle(LayerStyles.red);

            else if (questionButton(i).hasClass("missed"))
                countryLayers[i].setStyle(LayerStyles.yellow);

            else
                countryLayers[i].setStyle(LayerStyles.green);
        }
    }
}

// Appliquer un style au pays avec l'identifiant donné
//
function showCountryLayer(index, style) {

    // Appliquer le style choisi
    countryLayers[index].setStyle(style);

    // Si nous regardons les réponses, redirections-nous vers le pays
    if (allowViewingQuestions) {

        // Obtenir les limites du pays
        var countryBounds = countryLayers[index].getBounds();

        // se diriger vers le centre du pays
        map.flyToBounds(
            countryBounds, {
                padding: [100, 100] // Ajoutez un padding pour éviter de trop zoomer
            });
    }
}

// ajouter un layer de pays
//
function addCountryLayer(index) {
    map.addLayer(countryLayers[index]);
}

// supprimer yous les layers
//
function removeAllCountryLayers() {

    if (countryLayers == null)
        return;

    // boucler sur la taille du array et non la taille du questionnaire
    for (var i = 0; i < countryLayers.length; i++) {
        if (countryLayers[i] != null)
            map.removeLayer(countryLayers[i]);
    }
}

// supprimer le cercle d'aide et réinitialiser la distance dans le infoPanel
//
function removeHelpCircle() {

    // réinitialiser info panel
    infoPanel_Distance.html("--");

    // supprimer layer si il n'existe pas
    if (helpCircle != null)
        map.removeLayer(helpCircle);

    // rendre la variable null
    helpCircle = null;
}

// Dessiner un cercle du point donné au centre du pays de la question actuelle
//
function showHelpCircle(circleCenterPoint) {

    // Obtenir les coordonnées du centre du pays actuel
    var countryCenter = L.latLng(CountryInfo(current_question, "latlng"));

    // Calculer la distance entre le point de clic et le point central
    var distance = map.distance(circleCenterPoint, countryCenter); // La distance est renvoyée en mètres

    // Nous ajoutons un dépassement multiplié par 1000 pour convertir les kilomètres en mètres
    distance += answerAttemptMultiplier[clickAttempts].overshoot * 1000;

    // Charger le style de cercle
    var styleObject = LayerStyles.circle;

    // Modifier le rayon dans le style pour qu'il soit la distance entre le clic et le centre du pays
    styleObject.radius = distance

    // Supprimer le cercle precedent
    removeHelpCircle();

    // Afficher la distance
    infoPanel_Distance.html(parseFloat(distance / 1000).toFixed(2) + " Km");

    // créer un cercle pour aider
    helpCircle = L.circle(circleCenterPoint, styleObject);

    // Ajouter le cercle à la map
    map.addLayer(helpCircle);
}

/*----------------------------------------------
      les fonctions Ajax 
----------------------------------------------*/

// Demande un nouveau questionnaires au serveur
//
function requestNewQuiz() {

    // Retourner si nous attendons déjà
    if (currentButtonState == buttonStates.wait)
        return;

    // Faire attendre l'utilisateur jusqu'au chargement du questionnaire
    changeButtonState(buttonStates.wait);

    // Interdire l'affichage des anciennes questions
    allowViewingQuestions = false;

    // Réinitialise la vue de la carte
    resetMap();

    // Supprimer les informations du panel
    clearInformationPanel();

    // Masquer tous les pays sur la carte (sera invisible car allowViewingQuestions est désormais faux)
    hideAllCoutryLayers();

    // Les données de la requete
    var request = {
        action: "get-quiz",
        mode: gameParameters["mode"], // de la variable passée de php
    };

    // Envoyer la requete au serveur
    $.post("action.php", request)
        .done(function (data) {

            quiz_data = data; // Stocker les données reçues dans une variable
            quiz_size = data["quiz_size"]; // Enregistrer la taille des questionnaires

            // Vérifiez si la demande ne s'est pas déroulée comme prévu
            if (quiz_data["success"]) {

                // Retarder le démarrage du questionnaire de quelques millisecondes
                // Donc la barre de progression est claire
                setTimeout(prepareNewQuiz, 400);

            } else {
                // Afficher le message d'erreur généré par le serveur
                showAlert(quiz_data["message"], alertStyles.primary);

                // Changer le bouton pour un nouvel état de jeu
                changeButtonState(buttonStates.new_game);
            }

        })
        .fail(function () {
            // Afficher l'échec de cette demande
            showAlert("La requête a échoué, questionaire non chargé !", alertStyles.primary);

            // Changer le bouton pour un nouvel état de jeu
            changeButtonState(buttonStates.new_game);
        })
}

// Soumettre la partition au serveur à enregistrer
//
function submitScore() {

    // Si le score a déjà été envoyé, ne le spammons pas
    if (isScoreSent || !isQuizOver)
        return;

    // Score envoyé
    isScoreSent = true;

    // les info de requete
    var request = {
        action: "save-score",
        mode: gameParameters["mode"],
        quiz_id: quiz_data["quiz_id"],
        quiz_result: quiz_results.join('|'),
        time: totalTimeInSeconds,
        time_multiplier: currentTimeMultiplier,
        score: totalScore
    };

    // Soumettre la requete
    $.post("action.php", request)
        .done(function (data) {
            // Afficher le message d'erreur généré par le serveur
            showAlert(data["message"], alertStyles.primary);
        })
        .fail(function () {
            // Afficher l'échec de cette requete
            showAlert("La requête a échoué, votre score n'a pas été enregistré!");
        })
}

/*----------------------------------------------
     Fonctions de mise à jour du panel d'information
      et sous-fonctions connexes
----------------------------------------------*/

// Efface toutes les informations dans le panel d'informations
//
function clearInformationPanel() {

    loadFlag(); // Charger dans le drapeau inconnu

    removeHelpCircle(); // supprimer le cercle d'aide
    resetAtempts(); // réinitialiser le compteur de tentatives de clics
    resetTime(); // Réinitialiser le compteur de temps
    resetScore(); // Renitialiser le score

    infoPanel_CountryName.html("-"); // effacer le nom du pays
    infoPanel_CountryCapital.html("-"); // effacer ke name native du pays
    infoPanel_CountryArea.html("-"); // effacer la surface du pays

    infoPanel_WikiLink.attr("href", ""); // supprimer le lien
    infoPanel_WikiLink.fadeOut(600); // cacher le lien wiki 

    // Parcourez tous les enfants et supprimez toutes les classes (classes de couleurs)
    infoPanel_ProgressBar.children("div").each(function () {
        $(this).removeClass();
    });

    // Réduire tout
    infoPanel_ProgressBar.removeClass("click-allowed");
}

// Mettre à jour le panel d'information par les informations du pays données par l'index (id)
//
function showQuestionInfo(index) {

    // Charger le drapeau
    loadFlag(CountryInfo(index, "cca3"));

    infoPanel_CountryName.html(CountryInfo(index, "name")); // Afficher le nom du pays
    infoPanel_CountryCapital.html(CountryInfo(index, "capital")); // Afficher le nom du pays d'origine
    infoPanel_CountryArea.html(toFrenchNumber(CountryInfo(index, "area")) + " Km²"); // Afficher la surface du pays

    // Masquer les pays, le cas échéant
    hideAllCoutryLayers();

    if (allowViewingQuestions) {
        // Dessiner le pays si nous regardons des questions
        showCountryLayer(index, LayerStyles.blue);

        // Ne mettre le lien vers le pays que si nous consultons les réponses
        infoPanel_WikiLink.attr("href", generateWikiLink(index));
    }
}

// Charge un drapeau en utilisant le code du pays
// 
function loadFlag(cca3 = null) {

    // lien de drapeau par defaut
    flagLink = "flags/unknown-flag.svg";

    // Générez le lien vers le drapeau en utilisant le code cca3
    if (cca3 != null)
        flagLink = "flags/" + cca3 + ".svg";

    // modifier le lien source de l'élément d'image de drapeau pour charger le drapeau
    infoPanel_CountryFlag.attr("src", flagLink);
}

// Intervalle de temps pour chaque seconde
//
function timeTick() {

    // Ajouter une seconde
    totalTimeInSeconds++;

    // Mise à jour à l'heure affichée
    updateTime();

    // Mettre à jour le multiplicateur de temps et la valeur affichée
    updateTimeMultiplier();

    // Mettre à jour le score en cas de changement du multiplicateur de temps
    updateScore();
}

// Mese à jour de l'heure affichée dans le panel d'informations
//
function updateTime() {
    // Date.toISOString() retourne la date en cette format : YYYY-MM-DDTHH:mm:ss.sssZ
    // substr prend : YYYY-MM-DDTHH:[mm:ss].sssZ
    var value = new Date(totalTimeInSeconds * 1000).toISOString().substr(14, 5);

    // Afficher la valeur de temps
    infoPanel_Time.html(value);
}

// Mise à jour de la variable multiplicateur de temps actuelle
//
function updateTimeMultiplier(seconds) {

    // Régler le multiplicateur par default minimum au cas où nous ne trouverions aucun multiplicateur
    currentTimeMultiplier = timeMultiplierMinimumDefault;

    // Get the multiplier
    for (var i = 0; i < timeMultiplier.length; i++) {
        if (totalTimeInSeconds <= timeMultiplier[i].maxTime) {
            currentTimeMultiplier = timeMultiplier[i].multiplier; // Régler le multiplicateur de temps
            break; // On a trouvé le multiplicateur donc on fait un break
        }
    }

    // Mise à jour du facteur temps
    infoPanel_TimeFactor.html("x" + currentTimeMultiplier);
}

// Démarrer le compteur de temps
function startTimer() {
    // Commencez l'intervalle de temps qui coche toutes les secondes
    timeInterval_ID = setInterval(timeTick, 1000);
}

// Arrêtez le compteur de temps
//
function stopTimer() {
    clearInterval(timeInterval_ID);
    //infoPanel_Time.html()
}

// Arrêtez le compteur de temps
//
function resetTime() {
    totalTimeInSeconds = 0;
    updateTime();
}

// Mettre à jour la valeur de tentative de clic affichée dans le infoPanel
//
function updateClickAttempt() {

    // Preparer le texte
    var attemptText = clickAttempts + "/" + answerAttemptMultiplier.length;

    if (clickAttempts < answerAttemptMultiplier.length)
        // Si ce n'est pas la dernière tentative, écrivez-le normalement
        infoPanel_ClickAttempts.html(attemptText);
    else
        // Colorier-le en rouge si toutes les tentatives sont manquées
        infoPanel_ClickAttempts.html("<span class='px-2 bg-danger text-light'><b>" + attemptText + "</b></span>");
}

// Ajouter une tentative de clic
//
function addAttempt() {
    clickAttempts++;
    updateClickAttempt();
}

// Réinitialiser le compteur de tentatives de clic
//
function resetAtempts() {
    clickAttempts = 0;
    updateClickAttempt();
}

// Obtenir des informations sur les pays par index et attribut pour une lecture facile du code
//
function CountryInfo(index, attriute) {
    // retourner les informations demandées
    return quiz_data["countries"][index]["features"][0]["properties"][attriute];
}

// Génère le lien vers la page wikipedia du pays
//
function generateWikiLink(index) {
    // https://en.wikipedia.org/wiki/ + titre avec remplacement des espaces par des traits '_'
    // Remplacer les espaces par des traits 
    return "https://en.wikipedia.org/wiki/" +
        CountryInfo(index, "common").split(" ").join("_");
}

/*----------------------------------------------
      fonctions du jeu
----------------------------------------------*/

// Préparer le jeu à jouer (appelé après le chargement d'un nouveau quiz depuis le serveur)
//
function prepareNewQuiz() {

    // Effacer les anciens progrès du questionnaire
    infoPanel_ProgressBar.empty();

    // Videz les anciennes couches et zones de pays du questionnaire, si disponible
    removeAllCountryLayers();

    // Créer un nouveau array qui contiendra les couches de pays
    countryLayers = Array(quiz_size);

    // Nouveau résultat du questionnaire
    quiz_results = Array(quiz_size);

    // Renitialiser les variables
    isQuizOver = false;
    isScoreSent = false;

    // Ajouter un progress pour chaque questionnaire
    for (var i = 0; i < quiz_size; i++) {

        // Charger dans le fichier GeoJSON respectif
        countryLayers[i] = L.geoJSON(
            quiz_data["countries"][i], {
                className: "geojson-layer",
                style: LayerStyles.invisible,
                onEachFeature: processFeature
            }
        );


        // Cela attirera les pays mais sera transparent
        // Nous pouvons donc vérifier si nous avons cliqué à l'intérieur du pays ou non
        //
        addCountryLayer(i);

        // Créer un nouveau bouton de question
        var questionButton = $(`<div quiz-id="${i}" onclick="previewAnswer(${i})"></div>`);

        // Ajouter à la barre de progression du questionnaire
        infoPanel_ProgressBar.append(questionButton);
    }

    // Démarer la musique du Background 
    if (background_music.paused && !sounds_muted)
        background_music.play();

    // Charger la première question 0
    loadNextQuestion(0);

    // Démarrer le compteur de temps
    startTimer();
}

// Traite la fonctionnalité (country geoJSON) et ajoute un click event listener
//
function processFeature(feature, layer) {

    // Assurons-nous que le curseur est également en croix afin que nous ne puissions pas tricher ;)
    layer.on("mouseover", function () {
        $(this).css('cursor', 'crosshair');
    });

    // Ajoute un click event listener lorsque le pays est cliqué
    layer.on("click", function (e) {
        onCountryLayerClick(e, feature["properties"]["index"]);
    });
}

// Charge une question si l'identifiant est donné sinon charge la question suivante
//
function loadNextQuestion(index = -1) {

    if (index >= 0)
        // Charger la question avec un ID
        current_question = index;
    else
        // Id de la question suivante
        current_question++;

    // Ajouter la classe actuelle au bouton de question
    questionButton(current_question).addClass("current");

    // Afficher les informations du pays
    showQuestionInfo(current_question);

    // question posée - le bouton sautera les questions
    changeButtonState(buttonStates.skip_question);

    // Réinitialiser le compteur de tentatives
    resetAtempts();

    // Réinitialiser le pays cliqué
    countryIndex = -1;
    countryClicked = false;
}

// Ignore la question actuelle
//
function skipQuestion() {
    // Question sans réponse : échoué !!
    failQuestion(current_question);

    // Dessiner une couche de pays lors de la vérification de la réponse
    showCountryLayer(current_question, LayerStyles.red);
}

// Renitialiser le score
//
function resetScore() {
    baseScore = 0;
    totalScore = 0;
    updateScore();
}

// Mettez à jour les valeurs de score et affichez-les dans le infoPanel
//
function updateScore() {
    // Multipliez le score de base par le multiplicateur de temps
    totalScore = baseScore * currentTimeMultiplier;

    // Mise à jour du score de base et le score total
    infoPanel_BaseScore.html(toFrenchNumber(baseScore));
    infoPanel_TotalScore.html(toFrenchNumber(totalScore));
}

// Calcule le score de la question
//
function addBaseScore(index) {

    // Obtenir la base de score et non le (score de base)
    // La base de score est calculée par le constructeur pour contenir une valeur qui facilite le shiffting
    // le log (10) du plus grand pays à 1 donc tous les log (10) sont positifs
    //
    var scoreConstant = parseFloat(gameParameters["scoreConstant"]);

    // Calculer le log (10) de la surface pays
    var current_country_log = Math.log10(parseFloat(CountryInfo(index, "area")));

    // Calculer le score en utilisant notre logarithme de recherche de score
    var calculatedScore = Math.floor((scoreConstant - current_country_log) * 10);

    // Multiplier le score par le multiplicateur de tentatives
    calculatedScore *= answerAttemptMultiplier[clickAttempts].multiplier;

    // Ajouter le score total 
    baseScore += calculatedScore;

    // Définir le score total
    updateScore(calculatedScore);
}

// Montrer que la réponse à la question est correcte
function passQuestion(index) {

    // Jouer un effet sonore correct
    playSound(sfx_correct);

    // Masquer les cercles d'aide
    removeHelpCircle();

    // Ajouter le score
    addBaseScore(index);


    // Ajoutez le nom du pays au quiz_result pour enregistrer dans les statistiques
    if (clickAttempts == 0) {

        // Afficher que la question est passée dans la barre de progression
        questionButton(index).removeClass("current").addClass("passed");

        // affiche la couche de pays en vert (bonne réponse)
        showCountryLayer(index, LayerStyles.green);

        // Réponse à la question correctement au premier essai
        quiz_results[index] = "+" + CountryInfo(index, "name");

    } else {

        // Afficher que la question est passée dans la barre de progression
        questionButton(index).removeClass("current").addClass("missed");

        // affiche la couche de pays en jaune 
        showCountryLayer(index, LayerStyles.yellow);

        // La question a répondu correctement mais pas au premier essai
        quiz_results[index] = "~" + CountryInfo(index, "name");
    }

    if (index == quiz_size - 1) {
        // si c'était le dernier questionnaire de fin de question   
        endQuiz();
    } else {
        // Charger la question suivante
        autoLoadNextQuestion();
    }
}

// Échec de la question sélectionnée actuelle, ajoutez-la au résultat du questionnaire
//
function failQuestion(index) {

    // demarer le failed sound effect
    playSound(sfx_fail);

    // Masquer les cercles d'aide
    removeHelpCircle();

    // Afficher que la question a échoué dans la barre de progression
    questionButton(index).removeClass("current").addClass("failed");

    // Ajoutez le nom du pays commençant par '!' dans le quiz_result pour enregistrer les statistiques en tant que réponse ayant échoué
    quiz_results[index] = "-" + CountryInfo(index, "name");

    // afficher la couche de pays en rouge (mauvaise réponse)
    showCountryLayer(index, LayerStyles.red);

    if (index == quiz_size - 1) {
        // si c'était la derniere question, fin de questionnaire   
        endQuiz();
    } else {
        // Charger la question suivante
        autoLoadNextQuestion();
    }
}

// Vérifiez si le clic est une bonne ou une mauvaise réponse
//
function checkAnswer(e) {

    // Si le bouton est sur un état autre que sauter la question, nous ne faisons rien
    // Seulement quand c'est sur skip_question que nous répondons à la question
    if (currentButtonState != buttonStates.skip_question)
        return;

    // Vérifiez s'il s'agit d'un clic positif
    if (countryClicked && countryIndex == current_question) {
        // Bonne réponse
        passQuestion(current_question);
    } else {

        // Mauvais clic, nouvelle tentative de réponse
        addAttempt();

        // S'il ne reste aucune tentative, le joueur n'a pas répondu à la question
        if (clickAttempts == answerAttemptMultiplier.length) {
            // Toutes les tentatives étaient mauvaises
            failQuestion(current_question);
        } else {
            // Jouer un effet sonore erroné
            playSound(sfx_miss);

            // Afficher de l'aide
            showHelpCircle(e.latlng);
        }
    }
}

// Lorsque le pays est cliqué
//
function onCountryLayerClick(e, index) {

    // Se souvenir que le dernier clic était sur un pays
    countryClicked = true;

    // Se souvenir du ID du pays cliqué
    countryIndex = index;

    // Prévisualiser la réponse (cette fonction vérifiera si elle est autorisée)
    previewAnswer(index);
}

// Prévisualiser la réponse (uniquement si l'aperçu est autorisé)
//
function previewAnswer(index) {

    if (!allowViewingQuestions)
        return;

    // Désélectionner la question sélectionnée précédente
    infoPanel_ProgressBar.children(".selected").removeClass("selected");

    // Sélectionner le bouton question
    questionButton(index).addClass("selected");

    // Afficher les informations sur le bouton de question sélectionné
    showQuestionInfo(index);
}

// Fin du questionnaire actuel 
//
function endQuiz() {

    // Si c'est déjà fini on ne fais rien
    if (isQuizOver)
        return;

    // verrouiller le questionnaire pour éviter de le refermer
    isQuizOver = true;

    // Stop Timer
    stopTimer();

    // Multiplier le score par le multiplicateur de temps
    updateScore();

    // Envoyer le score au serveur
    submitScore();

    // Autoriser l'utilisateur à cliquer pour afficher l'ancienne question
    allowViewingQuestions = true;

    // Développer les boutons de question
    infoPanel_ProgressBar.addClass("click-allowed");

    // Visualiser la dernière réponse
    previewAnswer(quiz_size - 1);

    // afficher le lien wiki 
    infoPanel_WikiLink.fadeIn(600);

    // Changer l'état du bouton
    changeButtonState(buttonStates.new_game);
}


/*----------------------------------------------
     fonctions du Background et les sound effects 
----------------------------------------------*/

// Demarer un sound effect
//
function playSound(sfxObject) {
    sfxObject.pause();
    sfxObject.currentTime = 0;
    sfxObject.play();
}

// Initialise le background Music toggle switch
//
function backgroundMusicInitialize() {

    // Rendre le background music toggle button
    backGroundMusicToggleBtn = $(".background-music-btn");
    backGroundMusicToggleBtn.click(backGroundMusicToggle);

    // Charger dans la music et les sound effects
    background_music = new Audio("sfx/music.ogg"); //document.getElementById('background-music');
    sfx_correct = new Audio("sfx/correct.ogg");
    sfx_miss = new Audio("sfx/miss.ogg");
    sfx_fail = new Audio("sfx/fail.ogg");

    // Configurer les volumes
    background_music.volume = 0.3;
    sfx_correct.volume = 0.2;
    sfx_miss.volume = 0.1;
    sfx_fail.volume = 0.2;

    // Charger l'etat du background music toggle button
    // La variable est déjà fausse, nous la définissons sur true si c'est true
    if (getCookie("music_muted") === "true")
        sounds_muted = true;

    // Mettre à jour l'icône du bouton
    updateSoundsButton();
}

// Basculer la musique du background 
//
function backGroundMusicToggle() {
    if (sounds_muted) {
        sounds_muted = false;
        if (background_music.paused) {
            background_music.currentTime = 0;
            background_music.play();
        }

    } else {
        sounds_muted = true;
        if (!background_music.paused)
            background_music.pause();

    }

    setCookie("music_muted", sounds_muted.toString());
    updateSoundsButton();
}

// Mise à jour de l'icône du bouton de musique de fond (par classe)
//
function updateSoundsButton() {
    if (sounds_muted) {
        backGroundMusicToggleBtn.addClass("sound-muted");
    } else {
        backGroundMusicToggleBtn.removeClass("sound-muted");
    }
}

/*----------------------------------------------
     fonctions BIG button 
----------------------------------------------*/

// Effacer la prochaine temporisation automatique
//
function autoNextClear() {
    clearTimeout(autoNextTimeOut);
    autoNextTimeOut = null;
}

// Rappel automatique suivant
//
function autoNextCallback() {
    loadNextQuestion();
    autoNextClear();
}

// Démarre automatiquement la prochaine fois
//
function autoLoadNextQuestion() {
    // Changer le bouton pour attendre l'état
    changeButtonState(buttonStates.wait);

    // Commencez le temps à la question suivante 
    autoNextTimeOut = setTimeout(autoNextCallback, autoNextTimeOutMilliseconds);
}

// Modifie l'état du big button
//
function changeButtonState(stateObject) {

    currentButtonState = stateObject;
    theBigButton.html(stateObject.text);

    if (stateObject == buttonStates.wait)
        theBigButton.addClass("disabled");
    else
        theBigButton.removeClass("disabled");
}

// Fait des actions quand on clique sur le big bouton
//
function quizButtonHandler() {

    switch (currentButtonState.id) {
        case buttonStates.new_game.id: // Demarer un nouveau questionnaire
            requestNewQuiz();
            break;

        case buttonStates.skip_question.id: // Skip question
            skipQuestion();
            break;
    }

}

/*----------------------------------------------
     Autres fonctions et methodes requises
----------------------------------------------*/

// Obtenez l'objet jQuery sur le bouton de question
//
function questionButton(index) {
    return infoPanel_ProgressBar.children("div[quiz-id='" + index + "']");
}

// Ajouter des espaces au nombres 
/// e.g: 15214.69 -> 15 214,69 (french numbers)
//
function toFrenchNumber(x) {
    // Divisez l'entier et la décimale
    var parts = x.toString().split(".");

    // Ajoutez de l'espace tous les milliers
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, " ");

    // Rejoignez l'entier et la décimale avec une virgule (,)
    return parts.join(",");
}

//// fonctions de vookies setter/getter depuis
//// https://www.w3schools.com/js/js_cookies.asp
///
// Définir ou mettre à jour le cookie
//
function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
///
// Obtenir les cookies enregistrés par nom
//
function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

// Lorsque le document est entièrement chargé
//
$(document).ready(function () {

    // Obtenez des pointeurs vers des objets html
    infoPanel_Container = $(".infoPanel");
    //
    infoPanel_CountryFlag = $(".country-flag");
    infoPanel_Distance = $(".distance-holder");
    infoPanel_ClickAttempts = $(".click-attempt-holder");
    infoPanel_BaseScore = $(".base-score-holder");
    infoPanel_TimeFactor = $(".time-factor-holder");
    infoPanel_TotalScore = $(".score-holder");
    infoPanel_Time = $(".time-holder");
    infoPanel_ProgressBar = $(".quiz-progressBar");
    //
    infoPanel_CountryName = $(".country-name");
    infoPanel_CountryCapital = $(".country-capital");
    infoPanel_CountryArea = $(".country-area");
    infoPanel_WikiLink = $(".wikilink");

    // Initialiser le son
    backgroundMusicInitialize();

    // initialiser information panel
    clearInformationPanel();

    // Préparez la zone du jeu une fois le document prêt
    prepareMap();

    // Obtenir le big button et ajouter un click event listener
    theBigButton = $(".theBigButton");
    theBigButton.click(quizButtonHandler);

    // Nous sommes prêts à démarrer un jeu
    theBigButton.removeClass("disabled");
    changeButtonState(buttonStates.new_game);

});

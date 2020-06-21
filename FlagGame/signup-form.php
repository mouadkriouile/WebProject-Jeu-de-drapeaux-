<!-- Signup handler java script -->
<script src="js/signup-handler.js"></script>

<!-- Signup modal -->
<div class="modal fade" id="signup-modal" tabindex="-1" role="dialog" aria-labelledby="signup-modal-label" aria-hidden="true">
    <div class="modal-dialog " role="document">
        <form id="signup-form" class="px-2" action="action.php" method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Inscription</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-4">
                        L'inscription à notre site Web vous permet d'accéder à un nombre
                        illimité de quiz, y compris un mode pour le monde entier.
                        L'abonnement vous donne accès à tous vos jeux précédents auxquels
                        vous avez joué, aux statistiques et aux scores.
                    </p>

                    <!-- INSCRIPTION PAR EMAIL -->
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <div class="input-group-text icon icon-email"></div>
                        </div>
                        <input id="signup-email" class="form-control" type="text" name="email" placeholder="Entrer votre email">
                    </div>

                    <!-- INSCRIPTION D'UN MOT DE PASSE -->
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <div class="input-group-text icon icon-password"></div>
                        </div>
                        <input id="signup-password" class="form-control" type="password" name="password" placeholder="Entrer un mot de passe">
                    </div>

                    <!-- INSCRIPTION RÉPÉTER L'ENTRÉE DU MOT DE PASSE -->
                    <div class="input-group ">
                        <div class="input-group-prepend">
                            <span class="input-group-text icon icon-password"></span>
                        </div>
                        <input id="signup-confirm-password" class="form-control" type="password" name="re-password" placeholder="Répeter le mot de passe">
                    </div>
                </div>

                <div class="modal-footer">
                    <!-- /!\ type d'action non soumis via le bouton car il est rejeté lorsque nous empêchons la soumission -->
                    <input name="action" value="sign-up" hidden readonly>

                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Fermer</button>
                    <button class="btn btn-primary" type="submit">Inscription</button>
                </div>
            </div>
        </form>
    </div>
</div>

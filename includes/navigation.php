<!-- Navigation -->
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-3" href="index.php">
        <img src="img/logoNavbar.webp" alt="Logo" width="30" height="30" class="d-inline-block align-text-top">
        WebCMS
    </a>
    <a class="nav-link text-white" href="index.php">Accueil</a>
    <!-- Navbar-->
    <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION["user_id"])): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" id="navbarDropdown" href="#" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="me-2 d-none d-lg-inline text-white"><?php echo $_SESSION["username"]; ?></span>
                    <img src="img/photo_profil/<?php echo $_SESSION["user_img"]; ?>" alt="Photo de profil"
                        class="rounded-circle" width="30" height="30" style="object-fit: cover;">
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user fa-fw me-2"></i>Profil</a></li>
                    <li><a class="dropdown-item" href="my_articles.php"><i class="fas fa-newspaper fa-fw me-2"></i>Mes
                            articles</a></li>
                    <li><a class="dropdown-item" href="new_article.php"><i class="fas fa-plus fa-fw me-2"></i>Nouvel
                            article</a></li>
                    <?php if (isset($_SESSION["user_role"]) && ($_SESSION["user_role"] == "admin" || $_SESSION["user_role"] == "modo")): ?>
                        <li><a class="dropdown-item" href="admGestion/index.php"><i
                                    class="fas fa-cog fa-fw me-2"></i>Administration</a></li>
                    <?php endif; ?>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                            <i class="fas fa-sign-out-alt fa-fw me-2"></i>Déconnexion</a></li>
                </ul>
            </li>
        <?php else: ?>
            <li class="nav-item">
                <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt fa-fw me-2"></i>Connexion</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="register.php"><i class="fas fa-user-plus fa-fw me-2"></i>Inscription</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Prêt à partir?</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">Sélectionnez "Déconnexion" ci-dessous si vous êtes prêt à terminer votre session
                actuelle.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
                <a class="btn btn-primary" href="logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Vérifier si Bootstrap est disponible
        if (typeof bootstrap !== 'undefined') {
            // Initialiser le dropdown
            const dropdownElement = document.querySelector('#navbarDropdown');
            if (dropdownElement) {
                const dropdown = new bootstrap.Dropdown(dropdownElement);

                // Ajouter un gestionnaire d'événements pour le clic
                dropdownElement.addEventListener('click', function (e) {
                    e.preventDefault();
                    dropdown.toggle();
                });
            }
        } else {
            console.error('Bootstrap n\'est pas chargé correctement');
        }
    });
</script>
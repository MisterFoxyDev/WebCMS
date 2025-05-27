<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-info sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
        <div class="sidebar-brand-icon">
            <img src="../img/logoNavbar.webp" alt="logo" width=50>
        </div>
        <div class="sidebar-brand-text mx-3">WebCMS</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="index.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Tableau de bord</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        PAGES
    </div>

    <!-- Nav Item - Articles -->
    <li class="nav-item">
        <a class="nav-link" href="#">
            <i class="fas fa-fw fa-folder"></i>
            <span>Articles</span>
        </a>
        <ul class="nav-item-submenu" style="list-style: none; padding-left: 2.5rem;">
            <li style="list-style: none;">
                <a class="nav-link" href="new_article.php">
                    <i class="fas fa-fw fa-plus"></i>
                    <span>Nouvel article</span>
                </a>
            </li>
            <li style="list-style: none;">
                <a class="nav-link" href="all_articles.php">
                    <i class="fas fa-fw fa-list"></i>
                    <span>Tous les articles</span>
                </a>
            </li>
            <li style="list-style: none;">
                <a class="nav-link" href="comments.php">
                    <i class="fas fa-fw fa-comments"></i>
                    <span>Tous les commentaires</span>
                </a>
            </li>
        </ul>
    </li>

    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <!-- Nav Item - Users -->
        <li class="nav-item">
            <a class="nav-link" href="users.php">
                <i class="fas fa-fw fa-users"></i>
                <span>Utilisateurs</span></a>
        </li>

        <!-- Nav Item - Categories -->
        <li class="nav-item">
            <a class="nav-link" href="categories.php">
                <i class="fas fa-fw fa-chart-area"></i>
                <span>Catégories</span></a>
        </li>
    <?php endif; ?>

    <!-- Nav Item - Tables -->
    <!-- <li class="nav-item">
        <a class="nav-link" href="tables.html">
            <i class="fas fa-fw fa-table"></i>
            <span>Tables</span></a>
    </li> -->

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
<!-- End of Sidebar -->
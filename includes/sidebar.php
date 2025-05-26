<!-- Sidebar -->
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Catégories</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php
                            // Récupération des catégories
                            $categoriesReq = $bdd->query("SELECT * FROM categories ORDER BY category_name ASC");
                            $categories = $categoriesReq->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($categories as $category) {
                                $categoryId = $category['category_id'];
                                $categoryName = $category['category_name'];
                                $active = (isset($_GET['category_id']) && $_GET['category_id'] == $categoryId) ? 'active' : '';
                                echo "<a class='list-group-item list-group-item-action $active' href='index.php?category_id=$categoryId'>";
                                echo htmlspecialchars($categoryName);
                                echo "</a>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</div>
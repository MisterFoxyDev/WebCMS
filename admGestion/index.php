<?php
require_once "includes/admin_header.php";
require_once "includes/bdd.php";
require_once "functions.php";

// Récupérer les statistiques actuelles
$stats = [
    'articles' => articlesNum($bdd),
    'commentaires' => commentsNum($bdd),
    'membres' => usersNum($bdd),
    'categories' => categoriesNum($bdd)
];

// Définir les scripts spécifiques à cette page
$pageScripts = [
    '<!-- Google Charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load("current", {"packages":["corechart"]});
      google.charts.setOnLoadCallback(drawVisualization);

      function drawVisualization() {
        var data = google.visualization.arrayToDataTable([
          ["Type", "Articles", "Articles en attente", "Espace1", "Commentaires", "Espace2", "Membres", "Espace3", "Catégories"],
          ["Total", ' . $stats['articles'] . ', ' . articlesPendingNum($bdd) . ', 0, ' . $stats['commentaires'] . ', 0, ' . $stats['membres'] . ', 0, ' . $stats['categories'] . ']
        ]);

        var options = {
          title: "Statistiques du site",
          vAxis: {title: "Nombre"},
          hAxis: {title: "Type"},
          seriesType: "bars",
          colors: ["#4e73df", "#e74a3b", "transparent", "#36b9cc", "transparent", "#1cc88a", "transparent", "#f6c23e"],
          bar: {
            groupWidth: "60%"
          },
          series: {
            0: {targetAxisIndex: 0},
            1: {targetAxisIndex: 0},
            2: {targetAxisIndex: 0, barWidth: 200, visibleInLegend: false},
            3: {targetAxisIndex: 0},
            4: {targetAxisIndex: 0, barWidth: 200, visibleInLegend: false},
            5: {targetAxisIndex: 0},
            6: {targetAxisIndex: 0, barWidth: 200, visibleInLegend: false},
            7: {targetAxisIndex: 0}
          },
          legend: { 
            position: "top",
            textStyle: {
              color: "#5a5c69",
              fontSize: 12
            }
          }
        };

        var chart = new google.visualization.ColumnChart(document.getElementById("chart_div"));
        chart.draw(data, options);
      }
    </script>',
    '<!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>',
    '<!-- Pie Chart -->
    <script>
      document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById("myPieChart");
        var myPieChart = new Chart(ctx, {
          type: "doughnut",
          data: {
            labels: ["Administrateurs", "Modérateurs", "Utilisateurs"],
            datasets: [{
              data: [
                ' . usersByRoleNum($bdd, "admin") . ',
                ' . usersByRoleNum($bdd, "modo") . ',
                ' . usersByRoleNum($bdd, "user") . '
              ],
              backgroundColor: ["#4e73df", "#e74a3b", "#f6c23e"],
              hoverBackgroundColor: ["#2e59d9", "#be2617", "#dda20a"],
              hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
          },
          options: {
            maintainAspectRatio: false,
            tooltips: {
              backgroundColor: "rgb(255,255,255)",
              bodyFontColor: "#858796",
              borderColor: "#dddfeb",
              borderWidth: 1,
              xPadding: 15,
              yPadding: 15,
              displayColors: false,
              caretPadding: 10,
            },
            legend: {
              display: false
            },
            cutoutPercentage: 80,
          },
        });
      });
    </script>'
];

displayHeader();
addPageScripts($pageScripts);
?>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php require_once "includes/admin_left_sidebar.php"; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <?php require_once "includes/admin_topbar.php"; ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <!-- <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Tableau de bord</h1>
                        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                class="fas fa-download fa-sm text-white-50"></i> Générer un rapport</a>
                    </div> -->

                    <!-- Content Row -->
                    <br>
                    <div class="row">

                        <!-- Total des articles -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total des articles</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo articlesNum($bdd); ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-newspaper fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total des membres -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Total des membres</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo usersNum($bdd); ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total des commentaires -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total
                                                des commentaires
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                                        <?php echo commentsNum($bdd); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-comments fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total des catégories -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Total des catégories</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo categoriesNum($bdd); ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <hr>
                    <hr>
                    <div class="row">

                        <!-- Area Chart -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Statistiques du site</h6>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div id="chart_div" style="width: 100%; height: 320px; min-width: 100%;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Pie Chart -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Types d'utilisateurs</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Dropdown Header:</div>
                                            <a class="dropdown-item" href="#">Action</a>
                                            <a class="dropdown-item" href="#">Another action</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#">Something else here</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2"
                                        style="width: 100%; min-width: 100%; height: 320px;">
                                        <canvas id="myPieChart"></canvas>
                                    </div>
                                    <div class="mt-4 text-center small">
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-primary"></i> Administrateurs
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-danger"></i> Modérateurs
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-warning"></i> Utilisateurs
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <?php require_once "includes/admin_footer.php"; ?>

        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php require_once "includes/admin_logout_modal.php"; ?>
    <?php require_once "includes/admin_scripts.php"; ?>

</body>

</html>
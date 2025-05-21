<?php
// Démarrage de la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification de l'authentification
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || ($_SESSION["user_role"] != "admin" && $_SESSION["user_role"] != "modo")) {
    header("location:../../login.php");
    exit();
}

// Fonction pour afficher l'en-tête HTML
function displayHeader()
{
    ?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>Dashboard</title>

        <!-- Custom fonts for this template-->
        <link href="/webcms/admGestion/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
        <link
            href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
            rel="stylesheet">

        <!-- Custom styles for this template-->
        <link href="/webcms/admGestion/css/sb-admin-2.min.css" rel="stylesheet">

        <!-- DataTables CSS -->
        <link href="/webcms/admGestion/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

        <!-- Bootstrap core CSS -->
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">

        <!-- Summernote CSS -->
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs4.min.css" rel="stylesheet">

        <!-- Styles personnalisés pour Summernote -->
        <style>
            .note-editor .dropdown-toggle::after {
                display: none;
            }

            .note-editor .dropdown-menu {
                position: absolute;
                will-change: transform;
                top: 0px;
                left: 0px;
                transform: translate3d(0px, 40px, 0px);
            }

            .note-editor .note-dropdown-menu {
                min-width: 200px;
            }

            .note-editor .note-dropdown-menu>li>a {
                display: block;
                padding: 3px 20px;
                clear: both;
                font-weight: normal;
                line-height: 1.42857143;
                color: #333;
                white-space: nowrap;
            }

            .note-editor .note-dropdown-menu>li>a:hover,
            .note-editor .note-dropdown-menu>li>a:focus,
            .note-editor .note-dropdown-menu>.active>a {
                background-color: #f5f5f5;
            }
        </style>

        <!-- Core JavaScript -->
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>

        <!-- Summernote JavaScript -->
        <script>
            // Fonction pour charger Summernote
            function loadSummernote() {
                return new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs4.min.js';
                    script.onload = () => resolve();
                    script.onerror = () => reject();
                    document.head.appendChild(script);
                });
            }

            // Charger Summernote et initialiser
            $(document).ready(function () {
                loadSummernote().then(() => {
                    if (typeof $.fn.summernote !== 'undefined') {
                        if ($('#summernote').length) {
                            $('#summernote').summernote({
                                placeholder: 'Contenu de l\'article',
                                tabsize: 2,
                                height: 400,
                                dialogsInBody: true,
                                disableDragAndDrop: true,
                                toolbar: [
                                    ['style', ['style']],
                                    ['font', ['bold', 'underline', 'strikethrough', 'clear']],
                                    ['fontname', ['fontname']],
                                    ['fontsize', ['fontsize']],
                                    ['color', ['color']],
                                    ['para', ['ul', 'ol', 'paragraph']],
                                    ['height', ['height']],
                                    ['table', ['table']],
                                    ['insert', ['link', 'picture']],
                                    ['view', ['fullscreen', 'codeview', 'help']]
                                ],
                                fontNames: [
                                    'Arial', 'Arial Black', 'Comic Sans MS', 'Courier New',
                                    'Helvetica Neue', 'Helvetica', 'Impact', 'Lucida Grande',
                                    'Tahoma', 'Times New Roman', 'Verdana'
                                ],
                                fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '24', '36', '48'],
                                callbacks: {
                                    onInit: function () {
                                        // Gestion des menus déroulants
                                        $(document).on('click', function (e) {
                                            if (!$(e.target).closest('.note-editor .dropdown').length) {
                                                $('.note-editor .dropdown').removeClass('show');
                                                $('.note-editor .dropdown-menu').removeClass('show');
                                            }
                                        });

                                        $('.note-editor .dropdown-toggle').on('click', function (e) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            $('.note-editor .dropdown').not($(this).parent()).removeClass('show');
                                            $('.note-editor .dropdown-menu').not($(this).next()).removeClass('show');
                                            $(this).parent().toggleClass('show');
                                            $(this).next('.dropdown-menu').toggleClass('show');
                                        });

                                        $('.note-editor .dropdown-menu').on('click', function (e) {
                                            e.stopPropagation();
                                        });
                                    }
                                }
                            });
                        }
                    }
                });
            });
        </script>

        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <?php
        // Fonction pour ajouter des scripts spécifiques à une page
        function addPageScripts($scripts)
        {
            if (is_array($scripts)) {
                foreach ($scripts as $script) {
                    echo $script . "\n";
                }
            }
        }
        ?>
    </head>
    <?php
}
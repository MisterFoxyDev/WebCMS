<?php
session_start();
require_once "includes/bdd.php";

if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
    try {
        $req = "SELECT * FROM users WHERE user_id= :user_id";
        $stmt = $bdd->prepare($req);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $line = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($line === false) {
            // L'utilisateur n'existe pas
            session_unset();
            session_destroy();
            header("location:login.php");
            exit();
        }

        $user_name = $line["user_name"];
        $user_firstname = $line["user_firstname"];
        $user_email = $line["user_email"];
        $username = $line["username"];
        $user_img = $line["user_img"];

        if (isset($_POST["delete_validate"])) {
            $delReq = $bdd->prepare("DELETE FROM users WHERE user_id=:user_id");
            $delReq->bindValue(":user_id", $user_id);
            $delRes = $delReq->execute();

            if ($delRes) {
                if ($_SESSION) {
                    session_unset();
                    session_destroy();
                    header("location:index.php");
                }
            } else {
                $message = "Votre compte n'a pas pu être supprimé, veuillez vous reconnecter et rééssayer";
                $success = false;
            }
        }
    } catch (PDOException $e) {
        $message = "Une erreur est survenue lors de la récupération des informations du profil";
        $success = false;
    }
} else {
    header("location:login.php");
    exit();
}

if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    $success = true;
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    $success = false;
    unset($_SESSION['error_message']);
}
?>

<?php require_once "includes/login_header.php"; ?>
<?php require_once "includes/sweet_alert.php"; ?>

<body class="bg-primary">

    <body id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container mb-5">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header">
                                    <h3 class="text-center font-weight-light my-4">Profil</h3>
                                    <?php if (isset($message)) {
                                        if (isset($success) && $success) {
                                            echo "<div class='alert alert-success text-center mb-4'>$message</div>";
                                        } else {
                                            echo "<div class='alert alert-danger text-center mb-4'>$message</div>";
                                        }
                                    } ?>
                                </div>
                                <div class="card-header">
                                    <?php if (isset($user_img)) {
                                        echo "<center><img width=150 class='media-object' src='img/photo_profil/$user_img' alt='photo de profil'></center>";
                                    } ?>
                                </div>
                                <div class="card-body">
                                    <p><?php if (isset($user_name))
                                        echo "Nom : " . $user_name ?></p>
                                        <p><?php if (isset($user_firstname))
                                        echo "Prénom : " . $user_firstname ?></p>
                                        <p><?php if (isset($user_email))
                                        echo "Mail : " . $user_email ?></p>
                                        <p><?php if (isset($username))
                                        echo "Nom d'utilisateur : " . $username ?></p>
                                        <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                            <a class='small' href='index.php'>Retour</a>
                                            <div>
                                            <?php echo "<a class='small' href='profile.php?supprimer_compte=$user_id'>Supprimer mon compte</a>"; ?>
                                            <?php echo "<a class='small ms-3' href='modifier_profil.php?modifier_compte=$user_id'>Modifier mon profil</a>"; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if (isset($_GET["supprimer_compte"]) && isset($_SESSION["user_id"]) && $_GET["supprimer_compte"] == $_SESSION["user_id"]) {
                                    echo '<div class="card-footer text-center">';
                                    echo "Voulez vous vraiment supprimer votre compte ?";
                                    echo '<form action="" method="post">
                                        <div class="d-grid">
                                        <input type="submit" class="btn btn-danger mt-3" name="delete_validate" value="Supprimer définitivement" />
                                        </div>
                                        </form>';
                                    echo "</div>";
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <?php require_once "includes/footer.php"; ?>

        <!-- Scripts -->
        <?php require_once "includes/scripts.php"; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <script>
                Swal.fire({
                    title: 'Succès!',
                    text: '<?php echo $_SESSION['success_message']; ?>',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            </script>
            <?php
            // Supprimer le message après l'avoir affiché
            unset($_SESSION['success_message']);
        endif;
        ?>
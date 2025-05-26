<?php
session_start();
require_once "includes/bdd.php";

if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
    $req = "SELECT * FROM users WHERE user_id= $user_id AND user_role='admin' OR user_role='modo' ";
    $res = $bdd->query($req);
    $line = $res->fetch(PDO::FETCH_ASSOC);

    $user_name = $line["user_name"];
    $user_firstname = $line["user_firstname"];
    $user_email = $line["user_email"];
    $username = $line["username"];
    $user_img = $line["user_img"];

} else {
    header("location:login.php");
}

// Afficher le message de succès de la session s'il existe
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Supprimer le message après l'avoir affiché
}
?>

<?php require_once "includes/login_header.php"; ?>

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
                                    <!-- Page Heading -->
                                    <h1 class="h3 mb-4 text-gray-800">Profil</h1>

                                    <?php if (isset($message)) {
                                        if (
                                            $message === "Votre profil a bien été modifié !"
                                        ) {
                                            echo "<div class='text-center mb-4'><b style=\"color: black; background-color: #d4edda; padding: 10px; border-radius: 5px;\">$message</b></div>";
                                        } else {
                                            echo "<div class='text-center mb-4'><b style=\"color: black; background-color: #f8d7da; padding: 10px; border-radius: 5px;\">$message</b></div>";
                                        }
                                    } ?>
                                </div>
                                <div class="card-header">
                                    <?php if (isset($user_img)) {
                                        echo "<center><img width=150 class='media-object' src='../img/photo_profil/$user_img' alt='photo de profil'></center>";
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
                                            <a class='small' href='./index.php'>Retour</a>
                                        <?php echo "<a class='small' href='./modifier_profil.php?modifier_compte=$user_id'>Modifier mon profil</a>"; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <?php require_once "includes/footer.php"; ?>
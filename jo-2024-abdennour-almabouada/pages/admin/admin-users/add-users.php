<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nom_utilisateur = filter_input(INPUT_POST, 'nomUitilisateur', FILTER_SANITIZE_STRING);
    $prenom_utilisateur = filter_input(INPUT_POST, 'prenomUitilisateur', FILTER_SANITIZE_STRING);
    $login_utilisateur = filter_input(INPUT_POST, 'loginUitilisateur', FILTER_SANITIZE_STRING);
    $mdp_utilisateur = filter_input(INPUT_POST, 'mdpUitilisateur', FILTER_SANITIZE_STRING);

    // Vérifiez si le nom d'utilisateur est vide
    if (empty($nom_utilisateur)) {
        $_SESSION['error'] = "Le nom de l'utilisateur ne peut pas être vide.";
        header("Location: add-users.php");
        exit();
    }

    try {
        // Vérifiez si le nom d'utilisateur existe déjà
        $queryCheck = "SELECT id_utilisateur FROM UTILISATEUR WHERE nom_utilisateur = :nomUitilisateur";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nomUitilisateur", $nom_utilisateur, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Le nom d'utilisateur existe déjà.";
            header("Location: add-users.php");
            exit();
        } else {
            // Hacher le mot de passe
            $hashed_password = password_hash($mdp_utilisateur, PASSWORD_BCRYPT);

            // Requête pour ajouter un utilisateur
            $query = "INSERT INTO UTILISATEUR (nom_utilisateur, prenom_utilisateur, login, password) VALUES (:nomUitilisateur, :prenomUitilisateur, :loginUitilisateur, :mdpUitilisateur)";
            $statement = $connexion->prepare($query);
            $statement->bindParam(":nomUitilisateur", $nom_utilisateur, PDO::PARAM_STR);
            $statement->bindParam(":prenomUitilisateur", $prenom_utilisateur, PDO::PARAM_STR);
            $statement->bindParam(":loginUitilisateur", $login_utilisateur, PDO::PARAM_STR);
            $statement->bindParam(":mdpUitilisateur", $hashed_password, PDO::PARAM_STR);

            // Exécutez la requête
            if ($statement->execute()) {
                $_SESSION['success'] = "L'utilisateur a été ajouté avec succès.";
                header("Location: manage-users.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout de l'utilisateur.";
                header("Location: add-users.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-users.php");
        exit();
    }
}


// Afficher les erreurs en PHP
// (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../css/normalize.css">
    <link rel="stylesheet" href="../../../css/styles-computer.css">
    <link rel="stylesheet" href="../../../css/styles-responsive.css">
    <link rel="shortcut icon" href="../../../img/favicon-jo-2024.ico" type="image/x-icon">
    <title>Ajouter un utilisateur - Jeux Olympiques 2024</title>
    <style>
        /* Ajoutez votre style CSS ici */
    </style>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="manage-sports.php">Gestion Sports</a></li>
                <li><a href="manage-places.php">Gestion Lieux</a></li>
                <li><a href="manage-events.php">Gestion Calendrier</a></li>
                <li><a href="manage-countries.php">Gestion Pays</a></li>
                <li><a href="manage-gender.php">Gestion Genres</a></li>
                <li><a href="manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Ajouter un utilisateur</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="add-users.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cet utilisateur?')">
            <label for="nomUitilisateur">Nom d'utilisateur :</label>
            <input type="text" name="nomUitilisateur" id="nomUitilisateur" required>
            <label for="prenomUitilisateur">Prénom d'utilisateur :</label>
            <input type="text" name="prenomUitilisateur" id="prenomUitilisateur" required>
            <label for="loginUitilisateur">Login d'utilisateur :</label>
            <input type="text" name="loginUitilisateur" id="loginUitilisateur" required>
            <label for="mdpUitilisateur">Mot de passe :</label>
            <input type="password" name="mdpUitilisateur" id="mdpUitilisateur" required>

            <input type="submit" value="Ajouter l'utilisateur">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-users.php">Retour à la gestion des utilisateurs</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>

</body>

</html>

<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'utilisateur est fourni dans l'URL
if (!isset($_GET['id_utilisateur'])) {
    $_SESSION['error'] = "ID de l'utilisateur manquant.";
    header("Location: manage-users.php");
    exit();
}

$id_utilisateur = filter_input(INPUT_GET, 'id_utilisateur', FILTER_VALIDATE_INT);

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nom_utilisateur = filter_input(INPUT_POST, 'nomUtilisateur', FILTER_SANITIZE_STRING);
    $prenom_utilisateur = filter_input(INPUT_POST, 'prenomUtilisateur', FILTER_SANITIZE_STRING);
    $login_utilisateur = filter_input(INPUT_POST, 'loginUtilisateur', FILTER_SANITIZE_STRING);
    $mdp_utilisateur = filter_input(INPUT_POST, 'mdpUtilisateur', FILTER_SANITIZE_STRING);

    // Hacher le nouveau mot de passe
    $hashed_password = password_hash($mdp_utilisateur, PASSWORD_BCRYPT);

    // Vérifiez si le nom de l'utilisateur est vide
    if (empty($nom_utilisateur)) {
        $_SESSION['error'] = "Le nom de l'utilisateur ne peut pas être vide.";
        header("Location: modify-users.php?id_utilisateur=$id_utilisateur");
        exit();
    }

    try {
        // Requête pour mettre à jour l'utilisateur
        $query = "UPDATE UTILISATEUR SET nom_utilisateur = :nomUtilisateur, prenom_utilisateur = :prenomUtilisateur, login = :loginUtilisateur, password = :mdpUtilisateur WHERE id_utilisateur = :idUtilisateur";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nomUtilisateur", $nom_utilisateur, PDO::PARAM_STR);
        $statement->bindParam(":prenomUtilisateur", $prenom_utilisateur, PDO::PARAM_STR);
        $statement->bindParam(":loginUtilisateur", $login_utilisateur, PDO::PARAM_STR);
        $statement->bindParam(":mdpUtilisateur", $hashed_password, PDO::PARAM_STR); 
        $statement->bindParam(":idUtilisateur", $id_utilisateur, PDO::PARAM_INT);


        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "L'utilisateur a été modifié avec succès.";
            header("Location: manage-users.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de l'utilisateur.";
            header("Location: modify-users.php?id_utilisateur=$id_utilisateur");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-users.php?id_utilisateur=$id_utilisateur");
        exit();
    }
}

// Récupérez les informations de l'utilisateur pour affichage dans le formulaire
try {
    $queryUser = "SELECT nom_utilisateur, prenom_utilisateur, login FROM UTILISATEUR WHERE id_utilisateur = :idUtilisateur";
    $statementUser = $connexion->prepare($queryUser);
    $statementUser->bindParam(":idUtilisateur", $id_utilisateur, PDO::PARAM_INT);
    $statementUser->execute();

    if ($statementUser->rowCount() > 0) {
        $user = $statementUser->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Utilisateur non trouvé.";
        header("Location: manage-users.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-users.php");
    exit();
}
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
    <title>Modifier un Utilisateur - Jeux Olympiques 2024</title>
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
        <h1>Modifier un Utilisateur</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="modify-users.php?id_utilisateur=<?php echo $id_utilisateur; ?>" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cet utilisateur?')">
            <label for="nomUtilisateur">Nom d'utilisateur :</label>
            <input type="text" name="nomUtilisateur" id="nomUtilisateur"
                value="<?php echo htmlspecialchars($user['nom_utilisateur']); ?>" required>
            <label for="prenomUtilisateur">Prénom d'utilisateur :</label>
            <input type="text" name="prenomUtilisateur" id="prenomUtilisateur"
                value="<?php echo htmlspecialchars($user['prenom_utilisateur']); ?>" required>
            <label for="loginUtilisateur">Login d'utilisateur :</label>
            <input type="text" name="loginUtilisateur" id="loginUtilisateur"
                value="<?php echo htmlspecialchars($user['login']); ?>" required>
            <label for="mdpUtilisateur">Mot de passe :</label>
            <input type="password" name="mdpUtilisateur" id="mdpUtilisateur" required>

            <input type="submit" value="Modifier l'utilisateur">
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
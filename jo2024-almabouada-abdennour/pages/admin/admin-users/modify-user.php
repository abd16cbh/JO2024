<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID utilisateur est fourni dans l'URL
if (!isset($_GET['id_utilisateur'])) {
    $_SESSION['error'] = "ID de l'utilisateur manquant.";
    header("Location: manage-users.php");
    exit();
}

$id_utilisateur = filter_input(INPUT_GET, 'id_utilisateur', FILTER_VALIDATE_INT);

// Vérifiez si l'ID de l'utilisateur est un entier valide
if (!$id_utilisateur && $id_utilisateur !== 0) {
    $_SESSION['error'] = "ID de l'utilisateur invalide.";
    header("Location: manage-users.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nom_utilisateur = filter_input(INPUT_POST, 'nom_utilisateur', FILTER_SANITIZE_STRING);
    $prenom_utilisateur = filter_input(INPUT_POST, 'prenom_utilisateur', FILTER_SANITIZE_STRING);
    $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);




    // Vérifiez si le nom de l'utilisateur est vide
    if (empty($nom_utilisateur)) {
        $_SESSION['error'] = "Le nom de l'utilisateur ne peut pas être vide.";
        header("Location: modify-user.php?id_utilisateur=$id_utilisateur");
        exit();
    }

    try {
        // Vérifiez si l'utilisateur existe déjà
        $queryCheck = "SELECT id_utilisateur FROM UTILISATEUR WHERE nom_utilisateur=:nom_utilisateur AND id_utilisateur=:id_utilisateur";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nom_utilisateur", $nom_utilisateur, PDO::PARAM_STR);
        $statementCheck->bindParam(":id_utilisateur", $id_utilisateur, PDO::PARAM_INT);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'utilisateur existe déjà.";
            header("Location: modify-user.php?id_utilisateur=$id_utilisateur");
            exit();
        }

        // Requête pour mettre à jour l'utilisateur
        $query = "UPDATE UTILISATEUR SET nom_utilisateur=:nom_utilisateur, prenom_utilisateur=:prenom_utilisateur, login=:login_utilisateur, password=:password_utilisateur WHERE id_utilisateur=:id_utilisateur";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nom_utilisateur", $nom_utilisateur, PDO::PARAM_STR);
        $statement->bindParam(":prenom_utilisateur", $prenom_utilisateur, PDO::PARAM_INT);
        $statement->bindParam(":login_utilisateur", $login, PDO::PARAM_INT);
        $statement->bindParam(":password_utilisateur", $password, PDO::PARAM_INT);
        $statement->bindParam(":id_utilisateur", $id_utilisateur, PDO::PARAM_INT);


        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "L'utilisateur a été modifié avec succès.";
            header("Location: manage-users.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de l'utilisateur.";
            header("Location: modify-user.php?id_utilisateur=$id_utilisateur");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-user.php?id_utilisateur=$id_utilisateur");
        exit();
    }
}

// Récupérez les informations de l'utilisateur pour affichage dans le formulaire
try {
    $query_utilisateur = "SELECT nom_utilisateur, prenom_utilisateur, login, password FROM UTILISATEUR WHERE id_utilisateur = :id_utilisateur";
    $statement_utilisateur = $connexion->prepare($query_utilisateur);
    $statement_utilisateur->bindParam(":id_utilisateur", $id_utilisateur, PDO::PARAM_INT);
    $statement_utilisateur->execute();

    if ($statement_utilisateur->rowCount() > 0) {
        $utilisateur = $statement_utilisateur->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Utilisateur non trouvé.";
        header("Location: manage-utilisateurs.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-utilisateurs.php");
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
        <form action="modify-user.php?id_utilisateur=<?php echo $id_utilisateur; ?>" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cet utilisateur?')">
            <label for=" nom_utilisateur">Nom de l'utilisateur :</label>
            <input type="text" name="nom_utilisateur" id="nom_utilisateur"
                value="<?php echo htmlspecialchars($utilisateur['nom_utilisateur']); ?>" required>
                <label for=" prenom_utilisateur">Prénom de l'utilisateur :</label>
            <input type="text" name="prenom_utilisateur" id="prenom_utilisateur"
                value="<?php echo htmlspecialchars($utilisateur['prenom_utilisateur']); ?>" required>
                <label for=" login">Identifiant de l'utilisateur :</label>
            <input type="text" name="login" id="login"
                value="<?php echo htmlspecialchars($utilisateur['login']); ?>" required>
                <label for=" password">Mot de passe de l'utilisateur :</label>
            <input type="text" name="password" id="password"
                value="<?php echo htmlspecialchars($utilisateur['password']); ?>" required>
            <input type="submit" value="Modifier l'Utiliateur">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-utilisateurs.php">Retour à la gestion des utilisateurs</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>
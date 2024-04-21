<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID du genre est fourni dans l'URL
if (!isset($_GET['id_genre'])) {
    $_SESSION['error'] = "ID du genre manquant.";
    header("Location: manage-gender.php");
    exit();
}

$id_genre = filter_input(INPUT_GET, 'id_genre', FILTER_VALIDATE_INT);

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nom_genre = filter_input(INPUT_POST, 'nom_genre', FILTER_SANITIZE_STRING);

    // Vérifiez si le nom du genre est vide
    if (empty($nom_genre)) {
        $_SESSION['error'] = "Le nom du genre ne peut pas être vide.";
        header("Location: modify-gender.php?id_genre=$id_genre");
        exit();
    }

    try {
        // Requête pour mettre à jour le genre
        $query = "UPDATE GENRE SET nom_genre = :nom_genre WHERE id_genre = :id_genre";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nom_genre", $nom_genre, PDO::PARAM_STR);
        $statement->bindParam(":id_genre", $id_genre, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le genre a été modifié avec succès.";
            header("Location: manage-gender.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du genre.";
            header("Location: modify-gender.php?id_genre=$id_genre");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-gender.php?id_genre=$id_genre");
        exit();
    }
}

// Récupérez les informations du genre pour affichage dans le formulaire
try {
    $queryGenre = "SELECT nom_genre FROM GENRE WHERE id_genre = :id_genre";
    $statementGenre = $connexion->prepare($queryGenre);
    $statementGenre->bindParam(":id_genre", $id_genre, PDO::PARAM_INT);
    $statementGenre->execute();

    if ($statementGenre->rowCount() > 0) {
        $genre = $statementGenre->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Genre non trouvé.";
        header("Location: manage-gender.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-gender.php");
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
    <title>Modifier un Genre - Jeux Olympiques 2024</title>
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
        <h1>Modifier un genre</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="modify-gender.php?id_genre=<?php echo $id_genre; ?>" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce genre?')">
            <label for="nom_genre">Nom</label>
            <input type="text" name="nom_genre" id="nom_genre" value="<?php echo $genre['nom_genre']; ?>" required>

            <input type="submit" value="Modifier le genre">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-gender.php">Retour à la gestion des genres</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>

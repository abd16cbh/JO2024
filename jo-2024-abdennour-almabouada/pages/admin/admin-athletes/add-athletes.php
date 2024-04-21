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
    $nom_athlete = filter_input(INPUT_POST, 'nomAthlete', FILTER_SANITIZE_STRING);
    $prenom_athlete = filter_input(INPUT_POST, 'prenomAthlete', FILTER_SANITIZE_STRING);
    $id_pays = filter_input(INPUT_POST, 'pays', FILTER_VALIDATE_INT);
    $id_genre = filter_input(INPUT_POST, 'genre', FILTER_VALIDATE_INT);

    // Vérifiez si les champs obligatoires sont vides
    if (empty($nom_athlete) || empty($prenom_athlete) || empty($id_pays) || empty($id_genre)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
        header("Location: add-athlete.php");
        exit();
    }

    try {
        // Requête pour ajouter l'athlète
        $query = "INSERT INTO ATHLETE (nom_athlete, prenom_athlete, id_pays, id_genre) VALUES (:nomAthlete, :prenomAthlete, :pays, :genre)";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nomAthlete", $nom_athlete, PDO::PARAM_STR);
        $statement->bindParam(":prenomAthlete", $prenom_athlete, PDO::PARAM_STR);
        $statement->bindParam(":pays", $id_pays, PDO::PARAM_INT);
        $statement->bindParam(":genre", $id_genre, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "L'athlète a été ajouté avec succès.";
            header("Location: manage-athletes.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout de l'athlète.";
            header("Location: add-athlete.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-athlete.php");
        exit();
    }
}

// Récupérer la liste des pays et des genres depuis la base de données pour les options du formulaire

try {
    // Requête pour récupérer la liste des pays
    $queryPays = "SELECT id_pays, nom_pays FROM PAYS";
    $statementPays = $connexion->prepare($queryPays);
    $statementPays->execute();
    $pays = $statementPays->fetchAll(PDO::FETCH_ASSOC);

    // Requête pour récupérer la liste des genres
    $queryGenre = "SELECT id_genre, nom_genre FROM GENRE";
    $statementGenre = $connexion->prepare($queryGenre);
    $statementGenre->execute();
    $genres = $statementGenre->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: add-athlete.php");
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
    <title>Ajouter un athlète - Jeux Olympiques 2024</title>
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
        <h1>Ajouter un athlète</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="add-athletes.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cet athlète ?')">
            <label for="nomAthlete">Nom</label>
            <input type="text" name="nomAthlete" id="nomAthlete" required><br>

            <label for="prenomAthlete">Prénom</label>
            <input type="text" name="prenomAthlete" id="prenomAthlete" required><br>

            <label for="pays">Pays</label>
            <select name="pays" id="pays" required>
                <option value="" disabled selected>Sélectionnez un pays</option>
                <?php
                foreach ($pays as $pays) {
                    echo '<option value="' . $pays['id_pays'] . '">' . $pays['nom_pays'] . '</option>';
                }
                ?>
            </select><br>

            <label for="genre">Genre</label>
            <select name="genre" id="genre" required>
                <option value="" disabled selected>Sélectionnez un genre</option>
                <?php
                foreach ($genres as $genre) {
                    echo '<option value="' . $genre['id_genre'] . '">' . $genre['nom_genre'] . '</option>';
                }
                ?>
            </select><br>

            <input type="submit" value="Ajouter l'athlète">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-athletes.php">Retour à la gestion des athlètes</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>

</body>

</html>

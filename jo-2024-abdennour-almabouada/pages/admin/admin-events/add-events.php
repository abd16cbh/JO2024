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
    $nom_epreuve = filter_input(INPUT_POST, 'nom_epreuve', FILTER_SANITIZE_STRING);
    $date_epreuve = filter_input(INPUT_POST, 'date_epreuve', FILTER_SANITIZE_STRING);
    $heure_epreuve = filter_input(INPUT_POST, 'heure_epreuve', FILTER_SANITIZE_STRING);
    $id_lieu = filter_input(INPUT_POST, 'id_lieu', FILTER_VALIDATE_INT);
    $id_sport = filter_input(INPUT_POST, 'id_sport', FILTER_VALIDATE_INT);

    // Vérifiez si les champs obligatoires sont vides
    if (empty($nom_epreuve) || empty($date_epreuve) || empty($heure_epreuve) || !$id_lieu || !$id_sport) {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
        header("Location: add-events.php");
        exit();
    }

    try {
        // Requête pour ajouter l'épreuve
        $query = "INSERT INTO EPREUVE (nom_epreuve, date_epreuve, heure_epreuve, id_lieu, id_sport) VALUES (:nom_epreuve, :date_epreuve, :heure_epreuve, :id_lieu, :id_sport)";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nom_epreuve", $nom_epreuve, PDO::PARAM_STR);
        $statement->bindParam(":date_epreuve", $date_epreuve, PDO::PARAM_STR);
        $statement->bindParam(":heure_epreuve", $heure_epreuve, PDO::PARAM_STR);
        $statement->bindParam(":id_lieu", $id_lieu, PDO::PARAM_INT);
        $statement->bindParam(":id_sport", $id_sport, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "L'épreuve a été ajoutée avec succès.";
            header("Location: manage-events.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout de l'épreuve.";
            header("Location: add-events.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-events.php");
        exit();
    }
}

// Récupérer la liste des lieux et des sports depuis la base de données pour les options du formulaire
try {
    // Requête pour récupérer la liste des lieux
    $queryLieux = "SELECT id_lieu, nom_lieu FROM LIEU";
    $statementLieux = $connexion->prepare($queryLieux);
    $statementLieux->execute();
    $lieux = $statementLieux->fetchAll(PDO::FETCH_ASSOC);

    // Requête pour récupérer la liste des sports
    $querySports = "SELECT id_sport, nom_sport FROM SPORT";
    $statementSports = $connexion->prepare($querySports);
    $statementSports->execute();
    $sports = $statementSports->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: add-events.php");
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
    <title>Ajouter une épreuve - Jeux Olympiques 2024</title>
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
        <h1>Ajouter une épreuve</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="add-events.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cette épreuve ?')">
            <label for="nom_epreuve">Nom de l'épreuve :</label>
            <input type="text" name="nom_epreuve" id="nom_epreuve" required><br>

            <label for="date_epreuve">Date de l'épreuve :</label>
            <input type="date" name="date_epreuve" id="date_epreuve" required><br>

            <label for="heure_epreuve">Heure de l'épreuve :</label>
            <input type="time" name="heure_epreuve" id="heure_epreuve" required><br>

            <label for="id_lieu">Lieu :</label>
            <select name="id_lieu" id="id_lieu" required>
                <option value="" disabled selected>Sélectionnez un lieu</option>
                <?php
                foreach ($lieux as $lieu) {
                    echo '<option value="' . $lieu['id_lieu'] . '">' . $lieu['nom_lieu'] . '</option>';
                }
                ?>
            </select><br>

            <label for="id_sport">Sport :</label>
            <select name="id_sport" id="id_sport" required>
                <option value="" disabled selected>Sélectionnez un sport</option>
                <?php
                foreach ($sports as $sport) {
                    echo '<option value="' . $sport['id_sport'] . '">' . $sport['nom_sport'] . '</option>';
                }
                ?>
            </select><br>

            <input type="submit" value="Ajouter l'épreuve">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-events.php">Retour à la gestion des épreuves</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>

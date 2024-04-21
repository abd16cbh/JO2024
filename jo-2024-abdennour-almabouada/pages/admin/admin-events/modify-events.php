<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'épreuve est fourni dans l'URL
if (!isset($_GET['id_epreuve'])) {
    $_SESSION['error'] = "ID de l'épreuve manquant.";
    header("Location: manage-events.php");
    exit();
}

$id_epreuve = filter_input(INPUT_GET, 'id_epreuve', FILTER_VALIDATE_INT);

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nom_epreuve = filter_input(INPUT_POST, 'nom_epreuve', FILTER_SANITIZE_STRING);
    $date_epreuve = filter_input(INPUT_POST, 'date_epreuve', FILTER_SANITIZE_STRING);
    $heure_epreuve = filter_input(INPUT_POST, 'heure_epreuve', FILTER_SANITIZE_STRING);
    $id_lieu = filter_input(INPUT_POST, 'id_lieu', FILTER_VALIDATE_INT);
    $id_sport = filter_input(INPUT_POST, 'id_sport', FILTER_VALIDATE_INT);

    // Vérifiez si des champs requis sont vides ou invalides
    if (empty($nom_epreuve) || empty($date_epreuve) || empty($heure_epreuve) || !$id_lieu || !$id_sport) {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
        header("Location: modify-events.php?id_epreuve=$id_epreuve");
        exit();
    }

    try {
        // Requête pour mettre à jour l'épreuve
        $query = "UPDATE EPREUVE 
                  SET nom_epreuve = :nom_epreuve, date_epreuve = :date_epreuve, heure_epreuve = :heure_epreuve, id_lieu = :id_lieu, id_sport = :id_sport 
                  WHERE id_epreuve = :id_epreuve";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nom_epreuve", $nom_epreuve, PDO::PARAM_STR);
        $statement->bindParam(":date_epreuve", $date_epreuve, PDO::PARAM_STR);
        $statement->bindParam(":heure_epreuve", $heure_epreuve, PDO::PARAM_STR);
        $statement->bindParam(":id_lieu", $id_lieu, PDO::PARAM_INT);
        $statement->bindParam(":id_sport", $id_sport, PDO::PARAM_INT);
        $statement->bindParam(":id_epreuve", $id_epreuve, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "L'épreuve a été modifiée avec succès.";
            header("Location: manage-events.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de l'épreuve.";
            header("Location: modify-events.php?id_epreuve=$id_epreuve");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-events.php?id_epreuve=$id_epreuve");
        exit();
    }
}

// Récupérez les informations de l'épreuve pour affichage dans le formulaire
try {
    $queryEvent = "SELECT e.*, l.nom_lieu, s.nom_sport
                   FROM EPREUVE e
                   JOIN LIEU l ON e.id_lieu = l.id_lieu
                   JOIN SPORT s ON e.id_sport = s.id_sport
                   WHERE e.id_epreuve = :id_epreuve";
    $statementEvent = $connexion->prepare($queryEvent);
    $statementEvent->bindParam(":id_epreuve", $id_epreuve, PDO::PARAM_INT);
    $statementEvent->execute();

    if ($statementEvent->rowCount() > 0) {
        $event = $statementEvent->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Épreuve non trouvée.";
        header("Location: manage-events.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-events.php");
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
    <title>Modifier une épreuve - Jeux Olympiques 2024</title>
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
        <h1>Modifier une épreuve</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="modify-events.php?id_epreuve=<?php echo $id_epreuve; ?>" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cette épreuve?')">
            <label for="nom_epreuve">Nom de l'épreuve :</label>
            <input type="text" name="nom_epreuve" id="nom_epreuve" value="<?php echo htmlspecialchars($event['nom_epreuve']); ?>" required>
            <label for="date_epreuve">Date de l'épreuve :</label>
            <input type="date" name="date_epreuve" id="date_epreuve" value="<?php echo htmlspecialchars($event['date_epreuve']); ?>" required>
            <input type="time" name="heure_epreuve" id="heure_epreuve" value="<?php echo htmlspecialchars($event['heure_epreuve']); ?>" required>
            <label for="id_lieu">Lieu :</label>
            <select name="id_lieu" id="id_lieu" required>
                <option value="<?php echo $event['id_lieu']; ?>"><?php echo htmlspecialchars($event['nom_lieu']); ?></option>
                <?php
                // Liste des lieux à charger depuis la base de données
                $queryLieux = "SELECT * FROM LIEU";
                $statementLieux = $connexion->query($queryLieux);
                while ($lieu = $statementLieux->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . $lieu['id_lieu'] . '">' . htmlspecialchars($lieu['nom_lieu']) . '</option>';
                }
                ?>
            </select>
            <label for="id_sport">Sport :</label>
            <select name="id_sport" id="id_sport" required>
                <option value="<?php echo $event['id_sport']; ?>"><?php echo htmlspecialchars($event['nom_sport']); ?></option>
                <?php
                // Liste des sports à charger depuis la base de données
                $querySports = "SELECT * FROM SPORT";
                $statementSports = $connexion->query($querySports);
                while ($sport = $statementSports->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . $sport['id_sport'] . '">' . htmlspecialchars($sport['nom_sport']) . '</option>';
                }
                ?>
            </select>
            <input type="submit" value="Modifier l'épreuve">
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

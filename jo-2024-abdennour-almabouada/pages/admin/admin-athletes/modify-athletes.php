<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'athlète est fourni dans l'URL
if (!isset($_GET['id_athlete'])) {
    $_SESSION['error'] = "ID de l'athlète manquant.";
    header("Location: manage-athletes.php");
    exit();
}

$id_athlete = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nom_athlete = filter_input(INPUT_POST, 'nom_athlete', FILTER_SANITIZE_STRING);
    $prenom_athlete = filter_input(INPUT_POST, 'prenom_athlete', FILTER_SANITIZE_STRING);
    $id_pays = filter_input(INPUT_POST, 'id_pays', FILTER_VALIDATE_INT);
    $id_genre = filter_input(INPUT_POST, 'id_genre', FILTER_VALIDATE_INT);

    // Vérifiez si le nom de l'athlète est vide
    if (empty($nom_athlete)) {
        $_SESSION['error'] = "Le nom de l'athlète ne peut pas être vide.";
        header("Location: modify-athletes.php?id_athlete=$id_athlete");
        exit();
    }

    try {
        // Requête pour mettre à jour l'athlète
        $query = "UPDATE ATHLETE SET nom_athlete = :nom_athlete, prenom_athlete = :prenom_athlete, id_pays = :id_pays, id_genre = :id_genre WHERE id_athlete = :id_athlete";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nom_athlete", $nom_athlete, PDO::PARAM_STR);
        $statement->bindParam(":prenom_athlete", $prenom_athlete, PDO::PARAM_STR);
        $statement->bindParam(":id_pays", $id_pays, PDO::PARAM_INT);
        $statement->bindParam(":id_genre", $id_genre, PDO::PARAM_INT);
        $statement->bindParam(":id_athlete", $id_athlete, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "L'athlète a été modifié avec succès.";
            header("Location: manage-athletes.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de l'athlète.";
            header("Location: modify-athletes.php?id_athlete=$id_athlete");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-athletes.php?id_athlete=$id_athlete");
        exit();
    }
}

// Récupérez les informations de l'athlète pour affichage dans le formulaire
try {
    $queryAthlete = "SELECT * FROM ATHLETE WHERE id_athlete = :id_athlete";
    $statementAthlete = $connexion->prepare($queryAthlete);
    $statementAthlete->bindParam(":id_athlete", $id_athlete, PDO::PARAM_INT);
    $statementAthlete->execute();

    if ($statementAthlete->rowCount() > 0) {
        $athlete = $statementAthlete->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Athlète non trouvé.";
        header("Location: manage-athletes.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-athletes.php");
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
    <title>Modifier un athlète - Jeux Olympiques 2024</title>
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
        <h1>Modifier un athlète</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="modify-athletes.php?id_athlete=<?php echo $id_athlete; ?>" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cet athlète?')">
            <label for="nom_athlete">Nom :</label>
            <input type="text" name="nom_athlete" id="nom_athlete"
                value="<?php echo htmlspecialchars($athlete['nom_athlete']); ?>" required>
            <label for="prenom_athlete">Prénom :</label>
            <input type="text" name="prenom_athlete" id="prenom_athlete"
                value="<?php echo htmlspecialchars($athlete['prenom_athlete']); ?>" required>
            <!-- Vous devez ajouter des champs pour les autres attributs de l'athlète -->
            <input type="submit" value="Modifier l'athlète">
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

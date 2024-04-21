<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID du pays est fourni dans l'URL
if (!isset($_GET['id_pays'])) {
    $_SESSION['error'] = "ID du pays manquant.";
    header("Location: manage-countries.php");
    exit();
}

$id_pays = filter_input(INPUT_GET, 'id_pays', FILTER_VALIDATE_INT);

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nom_pays = filter_input(INPUT_POST, 'nomPays', FILTER_SANITIZE_STRING);

    // Vérifiez si le nom du pays est vide
    if (empty($nom_pays)) {
        $_SESSION['error'] = "Le nom du pays ne peut pas être vide.";
        header("Location: modify-countries.php?id_pays=$id_pays");
        exit();
    }

    try {
        // Requête pour mettre à jour le pays
        $query = "UPDATE PAYS SET nom_pays = :nom_pays WHERE id_pays = :id_pays";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nom_pays", $nom_pays, PDO::PARAM_STR);
        $statement->bindParam(":id_pays", $id_pays, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le pays a été modifié avec succès.";
            header("Location: manage-countries.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du pays.";
            header("Location: modify-countries.php?id_pays=$id_pays");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-countries.php?id_pays=$id_pays");
        exit();
    }
}

// Récupérez les informations du pays pour affichage dans le formulaire
try {
    $queryPays = "SELECT nom_pays FROM PAYS WHERE id_pays = :id_pays";
    $statementPays = $connexion->prepare($queryPays);
    $statementPays->bindParam(":id_pays", $id_pays, PDO::PARAM_INT);
    $statementPays->execute();

    if ($statementPays->rowCount() > 0) {
        $pays = $statementPays->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Pays non trouvé.";
        header("Location: manage-countries.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-countries.php");
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
    <title>Modifier un Pays - Jeux Olympiques 2024</title>
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
        <h1>Modifier un pays</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="modify-countries.php?id_pays=<?php echo $id_pays; ?>" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce pays?')">
            <label for="nomPays">Nom du Pays</label>
            <input type="text" name="nomPays" id="nomPays" value="<?php echo htmlspecialchars($pays['nom_pays']); ?>" required>

            <input type="submit" value="Modifier le pays">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-countries.php">Retour à la gestion des pays</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>

</body>

</html>

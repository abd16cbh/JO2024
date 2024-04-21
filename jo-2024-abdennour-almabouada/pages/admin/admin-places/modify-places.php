<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID du lieu est fourni dans l'URL
if (!isset($_GET['id_lieu'])) {
    $_SESSION['error'] = "ID du lieu manquant.";
    header("Location: manage-places.php");
    exit();
}

$id_lieu = filter_input(INPUT_GET, 'id_lieu', FILTER_VALIDATE_INT);

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nom_lieu = filter_input(INPUT_POST, 'nom_Lieu', FILTER_SANITIZE_STRING);
    $adresse_lieu = filter_input(INPUT_POST, 'adresse_Lieu', FILTER_SANITIZE_STRING);
    $ville_lieu = filter_input(INPUT_POST, 'ville_Lieu', FILTER_SANITIZE_STRING);
    $cp_lieu = filter_input(INPUT_POST, 'cp_Lieu', FILTER_SANITIZE_STRING);

    // Vérifiez si le nom du lieu est vide
    if (empty($nom_lieu)) {
        $_SESSION['error'] = "Le nom du lieu ne peut pas être vide.";
        header("Location: modify-places.php?id_lieu=$id_lieu");
        exit();
    }

    try {
        // Requête pour mettre à jour le lieu
        $query = "UPDATE LIEU SET nom_lieu = :nomlieu, adresse_lieu = :adresse_lieu, cp_lieu = :cp_lieu, ville_lieu = :ville_lieu WHERE id_lieu = :id_lieu";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nomlieu", $nom_lieu, PDO::PARAM_STR);
        $statement->bindParam(":adresse_lieu", $adresse_lieu, PDO::PARAM_STR);
        $statement->bindParam(":cp_lieu", $cp_lieu, PDO::PARAM_STR);
        $statement->bindParam(":ville_lieu", $ville_lieu, PDO::PARAM_STR);
        $statement->bindParam(":id_lieu", $id_lieu, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le lieu a été modifié avec succès.";
            header("Location: manage-places.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du lieu.";
            header("Location: modify-places.php?id_lieu=$id_lieu");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-places.php?id_lieu=$id_lieu");
        exit();
    }
}

// Récupérez les informations du lieu pour affichage dans le formulaire
try {
    $queryLieu = "SELECT nom_lieu, adresse_lieu, cp_lieu, ville_lieu FROM LIEU WHERE id_lieu = :id_lieu";
    $statementLieu = $connexion->prepare($queryLieu);
    $statementLieu->bindParam(":id_lieu", $id_lieu, PDO::PARAM_INT);
    $statementLieu->execute();

    if ($statementLieu->rowCount() > 0) {
        $lieu = $statementLieu->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Lieu non trouvé.";
        header("Location: manage-places.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-places.php");
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
    <title>Modifier un Lieu - Jeux Olympiques 2024</title>
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
        <h1>Modifier un lieu</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="modify-places.php?id_lieu=<?php echo $id_lieu; ?>" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce lieu?')">
            <label for="nomLieu">Nom</label>
            <input type="text" name="nom_Lieu" id="nomLieu" value="<?php echo $lieu['nom_lieu']; ?>" required>
            <label for="adresseLieu">Adresse :</label>
            <input type="text" name="adresse_Lieu" id="adresseLieu" value="<?php echo $lieu['adresse_lieu']; ?>" required>
            <label for="cpLieu">Code postal :</label>
            <input type="text" name="cp_Lieu" id="cpLieu" value="<?php echo $lieu['cp_lieu']; ?>" required>
            <label for="villeLieu">Ville</label>
            <input type="text" name="ville_Lieu" id="villeLieu" value="<?php echo $lieu['ville_lieu']; ?>" required>

            <input type="submit" value="Modifier le lieu">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-places.php">Retour à la gestion des lieux</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>

</body>

</html>

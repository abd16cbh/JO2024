<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

$login = $_SESSION['login'];
$nom_utilisateur = $_SESSION['prenom_utilisateur'];
$prenom_utilisateur = $_SESSION['nom_utilisateur'];
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
    <title>Gestion calendrier - Jeux Olympiques 2024</title>
    <style>
        /* Ajoutez votre style CSS ici */
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .action-buttons button {
            background-color: #1b1b1b;
            color: #d7c378;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .action-buttons button:hover {
            background-color: #d7c378;
            color: #1b1b1b;
        }
    </style>


</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="../admin-sports/manage-sports.php">Gestion Sports</a></li>
                <li><a href="../admin-places/manage-places.php">Gestion Lieux</a></li>
                <li><a href="../admin-events/manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-gender/manage-gender.php">Gestion Genres</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Gestion calendrier</h1>
        <div class="action-buttons">
            <button onclick="openAddAthleteForm()">Ajouter un épreuve</button>
            <!-- Autres boutons... -->
        </div>
        <!-- Tableau des athlètes -->
        <?php
        require_once("../../../database/database.php");

        try {
            // Requête pour récupérer la liste des athlètes depuis la base de données
            $query = "SELECT * FROM EPREUVE";
            $statement = $connexion->prepare($query);
            $statement->execute();

            // Vérifier s'il y a des résultats
            if ($statement->rowCount() > 0) {
                echo "<table><tr><th>Nom</th><th>Date épreuve</th><th>Heure</th><th>Lieu</th><th>Sport</th><th>Modifier</th><th>Supprimer</th></tr>";

                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    // Assainir les données avant de les afficher
                    echo "<td>" . htmlspecialchars($row['nom_epreuve']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['date_epreuve']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['heure_epreuve']) . "</td>";


                    // Récupérer le nom du pays
                    $queryPays = "SELECT nom_lieu FROM LIEU WHERE id_lieu = :id_lieu";
                    $statementPays = $connexion->prepare($queryPays);
                    $statementPays->bindParam(":id_lieu", $row['id_lieu'], PDO::PARAM_INT);
                    $statementPays->execute();
                    $pays = $statementPays->fetch(PDO::FETCH_ASSOC);

                    echo "<td>" . htmlspecialchars($pays['nom_lieu']) . "</td>";

                    // Récupérer le genre
                    $queryGenre = "SELECT nom_sport FROM SPORT WHERE id_sport = :id_sport";
                    $statementGenre = $connexion->prepare($queryGenre);
                    $statementGenre->bindParam(":id_sport", $row['id_sport'], PDO::PARAM_INT);
                    $statementGenre->execute();
                    $genre = $statementGenre->fetch(PDO::FETCH_ASSOC);

                    echo "<td>" . htmlspecialchars($genre['nom_sport']) . "</td>";

                    echo "<td><button onclick='openModifyAthleteForm({$row['id_epreuve']})'>Modifier</button></td>";
                    echo "<td><button onclick='deleteAthleteConfirmation({$row['id_epreuve']})'>Supprimer</button></td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Aucun athlète trouvé.</p>";
            }
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
        ?>
        <p class="paragraph-link">
            <a class="link-home" href="../admin.php">Accueil administration</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
    <script>
        function openAddAthleteForm() {
            // Rediriger vers la page pour ajouter un athlète
            window.location.href = 'add-events.php';
        }

        function openModifyAthleteForm(id_epreuve) {
            // Rediriger vers la page pour modifier un athlète
            window.location.href = 'modify-events.php?id_epreuve=' + id_epreuve;
        }

        function deleteAthleteConfirmation(id_epreuve) {
            if (confirm("Êtes-vous sûr de vouloir supprimer cet athlète?")) {
                // Ajoutez ici le code pour la suppression de l'athlète
                window.location.href = 'delete-events.php?id_epreuve=' + id_epreuve;
            }
        }
    </script>
</body>

</html>

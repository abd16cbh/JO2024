<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/normalize.css">
    <link rel="stylesheet" href="../css/styles-computer.css">
    <link rel="stylesheet" href="../css/styles-responsive.css">
    <link rel="shortcut icon" href="../img/favicon-jo-2024.ico" type="image/x-icon">
    <title>Résultats des épreuves - Jeux Olympiques 2024</title>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../index.php">Accueil</a></li>
                <li><a href="sports.php">Sports</a></li>
                <li><a href="events.php">Calendrier des évènements</a></li>
                <li><a href="results.php">Résultats</a></li>
                <li><a href="login.php">Accès administrateur</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Résultats des épreuves</h1>
        <?php
        require_once("../database/database.php");

        try {
            // Requête pour récupérer la liste des épreuves depuis la base de données
            $query = "SELECT PARTICIPER.resultat, ATHLETE.nom_athlete, ATHLETE.prenom_athlete, EPREUVE.nom_epreuve
            FROM PARTICIPER
            INNER JOIN ATHLETE ON PARTICIPER.id_athlete = ATHLETE.id_athlete
            INNER JOIN EPREUVE ON PARTICIPER.id_epreuve = EPREUVE.id_epreuve";
            $statement = $connexion->prepare($query);
            $statement->execute();

            // Vérifier s'il y a des résultats
            if ($statement->rowCount() > 0) {
                echo "<table>";
                echo "<tr><th class='color'>Athlète</th>
                      <th class='color'>Épreuve</th>
                      <th class='color'>Résultat</th>
                      </tr>";

                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nom_athlete'] . ' ' . $row['prenom_athlete']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_epreuve']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['resultat']) . "</td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>ERREUR.</p>";
            }
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
        ?>
        <p class="paragraph-link">
            <a class="link-home" href="../index.php">Retour Accueil</a>
        </p>

    </main>
    <footer>
        <figure>
            <img src="../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>
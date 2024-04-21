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
} else {
    $id_epreuve = filter_input(INPUT_GET, 'id_epreuve', FILTER_VALIDATE_INT);
    // Vérifiez si l'ID de l'épreuve est un entier valide
    if (!$id_epreuve && $id_epreuve !== 0) {
        $_SESSION['error'] = "ID de l'épreuve invalide.";
        header("Location: manage-events.php");
        exit();
    } else {
        try {
            // Préparez la requête SQL pour supprimer l'épreuve
            $sql = "DELETE FROM EPREUVE WHERE id_epreuve = :id_epreuve";
            // Exécutez la requête SQL avec le paramètre
            $statement = $connexion->prepare($sql);
            $statement->bindParam(':id_epreuve', $id_epreuve, PDO::PARAM_INT);
            $statement->execute();
            // Vérifiez si des lignes ont été affectées (c'est-à-dire si la suppression a réussi)
            if ($statement->rowCount() > 0) {
                $_SESSION['success'] = "L'épreuve a été supprimée avec succès.";
            } else {
                $_SESSION['error'] = "Échec de la suppression de l'épreuve.";
            }
            // Redirigez vers la page précédente après la suppression
            header('Location: manage-events.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
            header("Location: manage-events.php");
            exit();
        }
    }
}
?>

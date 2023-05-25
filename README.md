# tpazuremai19.github.io
Création d'une infrastructure sur azure avec :
  - Une machine linux avec docker, pour y installer Sonarqube & Jenkins
  - Une machine linux avec LAMP, pour y installer notre serveur web et notre bdd. (nous avons été limité à deux machines, le mieux de créer une machine par service)

Création d'un site web en PHP, avec des failles XSS & SQL pour tester le fonctionnement, voici le site :
"
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
  V2
    <title>Recherche d'utilisateurs !</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            color: #333;
        }

        form {
            margin-bottom: 20px;
        }

        input[type="text"] {
            padding: 5px;
            font-size: 16px;
        }

        input[type="submit"] {
            padding: 5px 10px;
            font-size: 16px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table td, table th {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Recherche d'utilisateurs et tout</h1>

    <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="search">Nom :</label>
        <input type="text" id="search" name="search" placeholder="Entrez un nom" required>
        <input type="submit" value="Rechercher">
    </form>

    <?php
    // Logins pour la BDD
    $servername = "localhost";
    $username = "mael";
    $password = "TPqualiteCODE35";
    $dbname = "qualite";

    try {
        // Connexion à la bdd avec PDO
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Traiter la requete de recherche
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
            $sql = "SELECT nom, prenom, date_naissance, adresse, cp, ville FROM utilisateurs WHERE nom LIKE :search";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                echo "<table>";
                echo "<tr><th>Nom</th><th>Prénom</th><th>Date de Naissance</th><th>Adresse</th><th>CP</th><th>Ville</th></tr>";
                foreach ($result as $row) {
                    echo "<tr>";
                    echo "<td>".$row['nom']."</td>";
                    echo "<td>".$row['prenom']."</td>";
                    echo "<td>".$row['date_naissance']."</td>";
                    echo "<td>".$row['adresse']."</td>";
                    echo "<td>".$row['cp']."</td>";
                    echo "<td>".$row['ville']."</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "Aucun résultat trouvé.";
            }
        }

        // Fermer la connexion à la bdd
        $conn = null;
    } catch (PDOException $e) {
        echo "Erreur de connexion à la base de données : " . $e->getMessage();
    }

    // ERREUR 1 : ecrire une variable non définie 
    echo $CetteVariableExistePasAhah;

    // ERREUR 1 : ecrire une fonction non définie 
    CetteFonctionExistePasAhah();

    // ERREUR 3 : Injection SQL
    $unsafeSearch = $_GET['search'];
    $sqlInjection = "SELECT nom, prenom, date_naissance, adresse, cp, ville FROM utilisateurs WHERE nom LIKE '$unsafeSearch'";
    $stmt = $conn->query($sqlInjection);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ERREUR 4 : Injection XSS
    $unsafeSearch = $_GET['search'];
    echo "<script>var searchTerm = '$unsafeSearch';</script>";
    ?>

</body>
</html>
"


<!DOCTYPE html>
<html>
<head>
//TRUC DE FOU ET TOUT C LA V2
    <meta charset="UTF-8">
    <title>Recherche d'utilisateurs !!!!!</title>
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
    <h1>Recherche d'utilisateurs !!!!</h1>

    <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="search">Nom :</label>
        <input type="text" id="search" name="search" placeholder="Entrez un nom" required>
        <input type="submit" value="Rechercher">
    </form>

    <?php
    // Paramètres de connexion à la base de données
    $servername = "localhost";
    $username = "mael";
    $password = "TPqualiteCODE35";
    $dbname = "qualite";

    try {
        // Connexion à la base de données avec PDO
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Traitement de la requête de recherche
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

        // Fermeture de la connexion à la base de données
        $conn = null;
    } catch (PDOException $e) {
        echo "Erreur de connexion à la base de données : " . $e->getMessage();
    }

    // Error introduced: Undefined variable $undefinedVariable
    echo $undefinedVariable;

    // Error introduced: Using an undefined function
    undefinedFunction();

    // Error introduced: SQL injection vulnerability
    $unsafeSearch = $_GET['search'];
    $sqlInjection = "SELECT nom, prenom, date_naissance, adresse, cp, ville FROM utilisateurs WHERE nom LIKE '$unsafeSearch'";
    $stmt = $conn->query($sqlInjection);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Error introduced: XSS vulnerability
    $unsafeSearch = $_GET['search'];
    echo "<script>var searchTerm = '$unsafeSearch';</script>";
    ?>

</body>
</html>

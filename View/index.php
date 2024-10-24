<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />    <title>Hotel MYHW</title>    <link rel="stylesheet" href="style.css" />
</head>
<?php
// Incluir arquivos necessários
include("./header.php");
include("./connexion.php");
?>
<body>
<form id="city">
        <label for="city">
            Your city hotel
        </label>
        <input list="places" type="text" id="city" name="city" required autoComplete="off" pattern="Amsterdam|Berlin|Dublin|London|Paris"/>
        <datalist id="places">
            <option>Amsterdam</option>
            <option>Berlin</option>
            <option>Dublin</option>
            <option>London</option>
            <option>Paris</option>
        </datalist>
        <button>Submit</button>
</form>
<?php
// Consulta SQL para recuperar os dados dos quartos
$sql = "SELECT * FROM `chambres`";
if(!$connexion->query($sql)){
    echo "Pb d'accès au events";
} else {
    echo "<div id='listChambres'>";
      // Loop através dos resultados da consulta
    foreach ($connexion->query($sql) as $row) {
        echo "<div class='chambres'>";
            echo "<img src='data:image/jpeg;base64," . base64_encode($row['image']) . "' />";
            echo "<div><h2>" . $row['type'] . "</h2>";
            echo "<p>chambre: ".$row['nb_max_chambre']." persones</p>";
            echo "<p>Prix par nuit: " . $row['prix_nuit'] . " €</p></div>";
                echo "<div><a href='chambres.php?id=" . $row['id'] . "' class='button'>Voir détails</a></div>";
            echo "</div>";
    }
    echo "</div>";
}
// Incluir rodapé
include("./footer.php");
?>
</body>
</html>





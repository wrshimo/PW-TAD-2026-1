<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Olá, Mundo!</title>
</head>
<body>
    <?php
        $nome = "Mundo";
        if (isset($_GET['nome'])) {
            $nome = $_GET['nome'];
        }

        echo "<h1>Olá, $nome!</h1>";
        
        var_dump($nome);
        echo "<p>Olá, " . $nome . "</p>";
    ?>
</body>
</html>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuários</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Usuários do MySQL</h1>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Usuário</th>
                    <th>Host</th>
                </tr>
            </thead>
            <tbody>
                <?php
                require_once 'db.php';

                try {
                    $pdo = getPdo();
                    $stmt = $pdo->query("SELECT User, Host FROM user ORDER BY User, Host");

                    $users = $stmt->fetchAll();

                    if (count($users) > 0) {
                        foreach ($users as $row) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['User'] ?? '') . "</td>";
                            echo "<td>" . htmlspecialchars($row['Host'] ?? '') . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2' class='text-center'>Nenhum usuário encontrado.</td></tr>";
                    }
                } catch (PDOException $e) {
                    echo "<tr><td colspan='2' class='text-center text-danger'>A consulta ao banco de dados falhou: " . $e->getMessage() . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS (opcional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

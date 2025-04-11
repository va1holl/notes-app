<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$dbname = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];

$conn = pg_connect(
    "host=$host 
    port=$port 
    dbname=$dbname 
    user=$user 
    password=$password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['note'])) {
    $note = pg_escape_string($conn, $_POST['note']);
    $query = "INSERT INTO notes (content) VALUES ('$note')";
    $result = pg_query($conn, $query);

    if ($result) {
        echo "<p>+</p>";
    } else {
        echo "<p>ошибка: " . pg_last_error() . "</p>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id']; // Преобразуем ID в число, чтобы предотвратить SQL-инъекции
    $query_delete = "DELETE FROM notes WHERE id = $delete_id";
    $result_delete = pg_query($conn, $query_delete);

    if ($result_delete) {
        echo "<p>Заметка удалена</p>";
    } else {
        echo "<p>ошибка удаления: " . pg_last_error() . "</p>";
    }
}

$query_select_notes = "SELECT * FROM notes ORDER BY created_at DESC LIMIT 10";
$result_select_notes = pg_query($conn, $query_select_notes);

if (!$result_select_notes) {
    echo "<p>ошибка селекта" . pg_last_error() . "</p>";
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заметки</title>
</head>
<body>
<h1>Добавить заметку</h1>
<form action="" method="POST">
    <textarea name="note" rows="4" cols="50" placeholder="Введите вашу заметку"></textarea><br><br>
    <input type="submit" value="Сохранить">
</form>

<h2>Сохраненные заметки</h2>
<?php

if (pg_num_rows($result_select_notes) > 0) {
    while ($row = pg_fetch_assoc($result_select_notes)) {
        echo "<div><strong>ID: " . htmlspecialchars($row['id']) ." | " . htmlspecialchars($row['created_at']) . "</strong><br>";
        echo htmlspecialchars($row['content']) . "<br>";

        echo "<form action='' method='POST'>
                <input type='hidden' name='delete_id' value='" . htmlspecialchars($row['id']) . "'>
                <input type='submit' value='Удалить'>
              </form>";

        echo "</div><hr>";
    }
} else {
    echo "<p>Пусто</p>";
}

pg_close($conn);
?>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $specialization = $_POST['specialization'];

    // Проверка на пустые поля
    if (empty($name) || empty($specialization)) {
        echo "Пожалуйста, заполните все поля.";
    } else {
        // Подключение к базе данных
        $conn = new mysqli("localhost", "ivan", "1", "dance_studio");

        // Проверка соединения
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Вставка данных
        $sql = "INSERT INTO teachers (name, specialization) VALUES ('$name', '$specialization')";

        if ($conn->query($sql) === TRUE) {
            echo "Новый преподаватель добавлен!";
        } else {
            echo "Ошибка: " . $sql . "<br>" . $conn->error;
        }

        $conn->close();
    }
}
?>

<form method="POST">
    <label for="name">Имя преподавателя:</label>
    <input type="text" id="name" name="name" required><br>
    <label for="specialization">Специализация:</label>
    <input type="text" id="specialization" name="specialization" required><br>
    <button type="submit">Добавить преподавателя</button>
</form>

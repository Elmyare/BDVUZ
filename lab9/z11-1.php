<?php
// Имя файла
$filename = "notebook_br02.txt";

// Проверяем наличие файла
if (file_exists($filename)) {
    echo "Файл существует<br>";
} else {
    // Создаем файл
    $file = fopen($filename, "w") or die("Не удалось создать файл");
    fclose($file);
    echo "Файл создан<br>";
}

// Подключение к базе данных
include("z10-4.inc");

// Проверяем, что соединение установлено
if (!isset($mysqli)) {
    die("Соединение с базой данных не установлено");
}

// Извлекаем данные из таблицы
$query = "SELECT * FROM notebook_br02"; // Замените NN на номер вашей бригады
$result = $mysqli->query($query);

// Проверяем результат
if (!$result) {
    die("Ошибка выполнения запроса: " . $mysqli->error);
}

// Открываем файл на запись
$file = fopen($filename, "w") or die("Не удалось открыть файл на запись");

// Перебираем строки таблицы
while ($row = $result->fetch_assoc()) {
    $line = "";
    foreach ($row as $value) {
        // Проверяем, является ли значение датой
        if (preg_match('/\d{4}-\d{2}-\d{2}/', $value)) {
            // Заменяем формат даты
            $value = preg_replace('/(\d{4})-(\d{2})-(\d{2})/', '$3-$2-$1', $value);
        }
        $line .= $value . " | ";
    }
    // Удаляем последний разделитель и добавляем перенос строки
    $line = rtrim($line, " | ") . "\n";
    fwrite($file, $line);
}

// Закрываем файл и подключение
fclose($file);
$mysqli->close();

// Открываем файл на чтение и выводим построчно
$file = fopen($filename, "r") or die("Не удалось открыть файл для чтения");
while (($line = fgets($file)) !== false) {
    echo htmlspecialchars($line) . "<br>";
}
fclose($file);
?>

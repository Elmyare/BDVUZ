<?php
// Устанавливаем кодировку для работы с кириллицей
mb_internal_encoding("UTF-8");

// Имя файла
$filename = "notebook_br02.txt";

// Проверяем наличие файла
if (file_exists($filename)) {
    // Читаем файл построчно в массив
    $file_array = file($filename);
} else {
    die("Файл не найден");
}

// Выводим таблицу
echo '<table border="1" cellpadding="10">';

// Перебираем строки файла
foreach ($file_array as $line) {
    // Пропускаем пустые строки
    $line = trim($line); // Убираем лишние пробелы и символы новой строки
    if (empty($line)) {
        continue;
    }


    // Заменяем email на гиперссылку
    $line = preg_replace_callback(
        '/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{1,})/',
        function ($matches) {
            return '<a href="mailto:' . $matches[1] . '">' . $matches[1] . '</a>';
        },
        $line
    );

    // Заменяем "|" на "</td><td>" с учетом экранирования символа
    $line = str_replace(" | ", "</td><td>", $line);

    // Вывод строки таблицы
    echo "<tr><td>" . $line . "</td></tr>";
}

echo '</table>';

// Выводим дату последней модификации файла
echo "Дата последней записи: " . date("D d M Y H:i:s", filemtime($filename));
?>

<?php
// Информация о браузере
$user_agent = $_SERVER['HTTP_USER_AGENT'];

// Определение браузера
if (preg_match('/(Firefox|Chrome|Safari|Opera|MSIE|Trident)\/([\d\.]+)/', $user_agent, $browser_matches)) {
    $browser = $browser_matches[1];
    $version = $browser_matches[2];
    echo "Браузер: $browser версия $version<br>";
} else {
    echo "Браузер не определен<br>";
}

// Определение ОС
if (preg_match('/Windows|Macintosh|Linux|Ubuntu|iPhone|Android/', $user_agent, $os_matches)) {
    $os = $os_matches[0];
    echo "Операционная система: $os<br>";
} else {
    echo "ОС не определена<br>";
}
?>

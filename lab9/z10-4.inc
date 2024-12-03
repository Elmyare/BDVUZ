<?php
$user = "ivan";  // Замените на свой логин
$pass = "1";  // Замените на свой пароль
$db = "sample";  // Замените на свою БД

$mysqli = mysqli_connect("localhost", $user, $pass, $db);

if (!$mysqli) {
    die("Нет соединения с MySQL: " . mysqli_connect_error());
}
?>

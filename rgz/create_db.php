<?php
// Подключаем конфигурацию для базы данных
include 'db_config.php';

// Подключаемся к серверу базы данных
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Создаем базу данных, если она не существует
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_DATABASE;
if ($conn->query($sql) === TRUE) {
    echo "База данных " . DB_DATABASE . " успешно создана или уже существует.<br>";
} else {
    echo "Ошибка при создании базы данных: " . $conn->error;
}

// Подключаемся к базе данных dance_studio
$conn->select_db(DB_DATABASE);

// Создаем таблицу преподавателей, если она не существует
$sql_teachers = "CREATE TABLE IF NOT EXISTS teachers (
    id_teacher INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    specialization VARCHAR(255) NOT NULL
)";
$conn->query($sql_teachers);

// Создаем таблицу учеников, если она не существует
$sql_students = "CREATE TABLE IF NOT EXISTS students (
    id_student INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    age INT NOT NULL,
    teacher_id INT,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id_teacher)
)";
$conn->query($sql_students);

// Создаем таблицу курсов, если она не существует
$sql_courses = "CREATE TABLE IF NOT EXISTS courses (
    id_course INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL,
    teacher_id INT,
    duration INT NOT NULL,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id_teacher)
)";

$conn->query($sql_courses);

$sql_student_courses = "CREATE TABLE student_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id_student) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id_course) ON DELETE CASCADE
)";

$conn->query($sql_student_courses);

// Закрываем соединение
$conn->close();
?>

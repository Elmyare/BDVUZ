<?php
// Подключение конфигурации для базы данных
include 'db_config.php';

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Функции для выполнения запросов
function fetch_courses($conn) {
    $sql = "SELECT courses.id_course, courses.course_name, courses.duration, teachers.name AS teacher_name 
            FROM courses
            JOIN teachers ON courses.teacher_id = teachers.id_teacher";
    return $conn->query($sql);
}

function fetch_students_and_courses($conn) {
    $sql = "SELECT students.id_student, students.name AS student_name, 
                   COALESCE(GROUP_CONCAT(courses.course_name SEPARATOR ', '), 'Нет курсов') AS courses
            FROM student_courses
            JOIN students ON student_courses.student_id = students.id_student
            LEFT JOIN courses ON student_courses.course_id = courses.id_course
            GROUP BY students.id_student";
    return $conn->query($sql);
}

function fetch_teachers_and_course_count($conn) {
    $sql = "SELECT teachers.name AS teacher_name, 
                   COUNT(courses.id_course) AS course_count
            FROM teachers
            LEFT JOIN courses ON teachers.id_teacher = courses.teacher_id
            GROUP BY teachers.id_teacher";
    return $conn->query($sql);
}

function fetch_students_by_course($conn, $course_id) {
    $sql = "SELECT students.name AS student_name
            FROM students
            JOIN student_courses ON students.id_student = student_courses.student_id
            WHERE student_courses.course_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Обработка запросов из формы
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['query1'])) {
        $result1 = fetch_students_and_courses($conn);
    }

    if (isset($_POST['query2'])) {
        $result2 = fetch_teachers_and_course_count($conn);
    }

    if (isset($_POST['query3'])) {
        $course_id = $_POST['course_id'];
        $result3 = fetch_students_by_course($conn, $course_id);
    }
}

// Получение списка курсов для формы 3
$courses_result = fetch_courses($conn);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Запросы к базе данных</title>
</head>
<body>
    <h1>Запросы к базе данных</h1>

    <!-- Запрос 1: Список учеников и курсов -->
    <form method="post">
        <button type="submit" name="query1">Запрос 1: Список учеников и курсов</button>
    </form>
    <?php
    if (isset($result1)) {
        echo "<h2>Результаты запроса 1:</h2>";
        if ($result1->num_rows > 0) {
            while ($row = $result1->fetch_assoc()) {
                echo "Имя ученика: " . $row['student_name'] . " | Курсы: " . $row['courses'] . "<br>";
            }
        } else {
            echo "Нет данных для запроса 1.<br>";
        }
    }
    ?>

    <!-- Запрос 2: Список преподавателей и количества курсов -->
    <form method="post">
        <button type="submit" name="query2">Запрос 2: Список преподавателей и количества курсов</button>
    </form>
    <?php
    if (isset($result2)) {
        echo "<h2>Результаты запроса 2:</h2>";
        if ($result2->num_rows > 0) {
            while ($row = $result2->fetch_assoc()) {
                echo "Имя преподавателя: " . $row['teacher_name'] . " | Количество курсов: " . $row['course_count'] . "<br>";
            }
        } else {
            echo "Нет данных для запроса 2.<br>";
        }
    }
    ?>

    <!-- Запрос 3: Выбор курса и вывод учеников для выбранного курса -->
    <form method="post">
        <h3>Выберите курс для запроса 3:</h3>
        <select name="course_id">
            <?php
            if ($courses_result->num_rows > 0) {
                while ($course = $courses_result->fetch_assoc()) {
                    echo "<option value='" . $course['id_course'] . "'>" . $course['course_name'] . "</option>";
                }
            }
            ?>
        </select>
        <button type="submit" name="query3">Запрос 3: Ученики для выбранного курса</button>
    </form>

    <?php
    if (isset($result3)) {
        echo "<h2>Результаты запроса 3:</h2>";
        if ($result3->num_rows > 0) {
            while ($row = $result3->fetch_assoc()) {
                echo "Имя ученика: " . $row['student_name'] . "<br>";
            }
        } else {
            echo "Нет данных для запроса 3.<br>";
        }
    }
    ?>

</body>
</html>

<?php
// Закрытие соединения
$conn->close();
?>

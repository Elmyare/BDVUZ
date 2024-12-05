<?php
// Подключаем конфигурацию для базы данных
include 'db_config.php';

// Подключаемся к базе данных
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Добавление преподавателя
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_teacher'])) {
    $name = $_POST['name'];
    $specialization = $_POST['specialization'];
    
    if (!empty($name) && !empty($specialization)) {
        $sql = "INSERT INTO teachers (name, specialization) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $name, $specialization);
        $stmt->execute();
        $stmt->close();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Имя и специализация не могут быть пустыми.";
    }
}

// Добавление ученика
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $name = $_POST['student_name'];
    $age = $_POST['age'];
    $teacher_id = $_POST['teacher_id'];

    if (!empty($name) && !empty($age) && !empty($teacher_id)) {
        $sql = "INSERT INTO students (name, age, teacher_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $name, $age, $teacher_id);
        $stmt->execute();
        $student_id = $stmt->insert_id; // Получаем id нового студента
        $stmt->close();

        if (isset($_POST['course_ids'])) {
            foreach ($_POST['course_ids'] as $course_id) {
                $sql = "INSERT INTO student_courses (student_id, course_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $student_id, $course_id);
                $stmt->execute();
                $stmt->close();
            }
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Все поля для ученика должны быть заполнены.";
    }
}

// Добавление курса
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $course_name = $_POST['course_name'];
    $duration = $_POST['duration'];
    $teacher_id = $_POST['course_teacher_id'];
    
    if (!empty($course_name) && !empty($duration) && !empty($teacher_id)) {
        $sql = "INSERT INTO courses (course_name, duration, teacher_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $course_name, $duration, $teacher_id);
        $stmt->execute();
        $stmt->close();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Все поля для курса должны быть заполнены.";
    }
}

// Функция для выборки данных
function fetch_all_data($conn, $query) {
    $result = $conn->query($query);
    if ($result === false) {
        echo "Ошибка при выполнении запроса: " . $conn->error;
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Получение данных для таблиц
$teachers_table = fetch_all_data($conn, "SELECT * FROM teachers");
$students_table = fetch_all_data($conn, "SELECT students.id_student, students.name AS student_name, 
    students.age, teachers.name AS teacher_name, 
    COALESCE(GROUP_CONCAT(courses.course_name SEPARATOR '<br>'), 'Нет курсов') AS courses
    FROM students
    JOIN teachers ON students.teacher_id = teachers.id_teacher
    LEFT JOIN student_courses ON students.id_student = student_courses.student_id
    LEFT JOIN courses ON student_courses.course_id = courses.id_course
    GROUP BY students.id_student");
$courses_table = fetch_all_data($conn, "SELECT c.id_course, c.course_name, c.duration, t.name AS teacher_name
    FROM courses c LEFT JOIN teachers t ON c.teacher_id = t.id_teacher");

// Получение данных для форм
$teachers_form = $teachers_table;
$courses_form = $courses_table;

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Студия танцев</title>
</head>
<body>
    <h1>Добавление преподавателя</h1>
    <form method="POST">
        <label for="name">Имя преподавателя:</label>
        <input type="text" name="name" required><br><br>
        <label for="specialization">Специализация:</label>
        <input type="text" name="specialization" required><br><br>
        <input type="submit" name="add_teacher" value="Добавить преподавателя">
    </form>

    <h2>Список преподавателей</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Специализация</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($teachers_table)): ?>
                <?php foreach ($teachers_table as $row): ?>
                    <tr>
                        <td><?= $row['id_teacher'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['specialization'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="3">Нет преподавателей</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h1>Добавление ученика</h1>
    <form method="POST">
        <label for="student_name">Имя ученика:</label>
        <input type="text" name="student_name" required><br><br>
        <label for="age">Возраст:</label>
        <input type="number" name="age" required><br><br>
        <label for="teacher_id">Преподаватель:</label>
        <select name="teacher_id" required>
            <?php if (!empty($teachers_form)): ?>
                <?php foreach ($teachers_form as $row): ?>
                    <option value="<?= $row['id_teacher'] ?>"><?= $row['name'] ?></option>
                <?php endforeach; ?>
            <?php else: ?>
                <option disabled>Нет доступных преподавателей</option>
            <?php endif; ?>
        </select><br><br>

        <label for="courses">Выберите курсы:</label><br>
        <?php if (!empty($courses_form)): ?>
            <?php foreach ($courses_form as $row): ?>
                <input type="checkbox" name="course_ids[]" value="<?= $row['id_course'] ?>"> <?= $row['course_name'] ?><br>
            <?php endforeach; ?>
        <?php else: ?>
            Нет доступных курсов.
        <?php endif; ?>
        <br><br>
        <input type="submit" name="add_student" value="Добавить ученика">
    </form>

    <h2>Список учеников</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Имя ученика</th>
                <th>Возраст</th>
                <th>Преподаватель</th>
                <th>Курсы</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($students_table)): ?>
                <?php foreach ($students_table as $row): ?>
                    <tr>
                        <td><?= $row['id_student'] ?></td>
                        <td><?= $row['student_name'] ?></td>
                        <td><?= $row['age'] ?></td>
                        <td><?= $row['teacher_name'] ?></td>
                        <td><?= $row['courses'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5">Нет учеников</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h1>Добавление курса</h1>
    <form method="POST">
        <label for="course_name">Название курса:</label>
        <input type="text" name="course_name" required><br><br>
        <label for="duration">Длительность (в часах):</label>
        <input type="number" name="duration" required><br><br>
        <label for="course_teacher_id">Преподаватель:</label>
        <select name="course_teacher_id" required>
            <?php if (!empty($teachers_form)): ?>
                <?php foreach ($teachers_form as $row): ?>
                    <option value="<?= $row['id_teacher'] ?>"><?= $row['name'] ?></option>
                <?php endforeach; ?>
            <?php else: ?>
                <option disabled>Нет доступных преподавателей</option>
            <?php endif; ?>
        </select><br><br>
        <input type="submit" name="add_course" value="Добавить курс">
    </form>

    <h2>Список курсов</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Название курса</th>
                <th>Длительность (часы)</th>
                <th>Преподаватель</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($courses_table)): ?>
                <?php foreach ($courses_table as $row): ?>
                    <tr>
                        <td><?= $row['id_course'] ?></td>
                        <td><?= $row['course_name'] ?></td>
                        <td><?= $row['duration'] ?></td>
                        <td><?= $row['teacher_name'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">Нет курсов</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

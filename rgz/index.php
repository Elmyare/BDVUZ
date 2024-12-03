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

// Функции выборки данных
function fetch_teachers($conn) {
    $sql = "SELECT * FROM teachers";
    $result = $conn->query($sql);
    
    if ($result === false) {
        echo "Ошибка при выполнении запроса: " . $conn->error . "<br>"; // Выводим ошибку
        return null;
    }

    return $result;
}

function fetch_students($conn) {
    $sql = "SELECT students.id_student, students.name AS student_name, students.age, teachers.name AS teacher_name 
            FROM students
            JOIN teachers ON students.teacher_id = teachers.id_teacher";
    $result = $conn->query($sql);
    if ($result === false) {
        echo "Ошибка при выполнении запроса: " . $conn->error;
        return null;
    }
    return $result;
}

function fetch_courses($conn) {
    $sql = "SELECT c.id_course, c.course_name, c.duration, t.name AS teacher_name
            FROM courses c
            LEFT JOIN teachers t ON c.teacher_id = t.id_teacher";

    $result = $conn->query($sql);

    if ($result === false) {
        echo "Ошибка при выполнении запроса: " . $conn->error . "<br>";
        return null;  // Возвращаем null в случае ошибки
    }

    // Проверка перед возвратом
    if ($result instanceof mysqli_result) {
        return $result;
    } else {
        return null; // Если результат не mysqli_result, то вернуть null
    }
}


// Функция выборки курсов для учеников
function fetch_student_courses($conn) {
    $sql = "SELECT students.id_student, students.name AS student_name, 
                   teachers.name AS teacher_name,
                   COALESCE(GROUP_CONCAT(courses.course_name SEPARATOR '<br>'), 'Нет курсов') AS courses
            FROM student_courses
            JOIN students ON student_courses.student_id = students.id_student
            JOIN teachers ON students.teacher_id = teachers.id_teacher
            LEFT JOIN courses ON student_courses.course_id = courses.id_course
            GROUP BY students.id_student";
    $result = $conn->query($sql);
    if ($result === false) {
        echo "Ошибка при выполнении запроса: " . $conn->error;
        return null;
    }
    return $result;
}

// Получение данных
$teachers = fetch_teachers($conn);
$students = fetch_student_courses($conn);
// Получение данных
$courses = fetch_courses($conn);



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

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
            <?php
            if ($teachers && $teachers->num_rows > 0) {
                while ($row = $teachers->fetch_assoc()) {
                    echo "<tr><td>{$row['id_teacher']}</td><td>{$row['name']}</td><td>{$row['specialization']}</td></tr>";
                }
            } else {
                echo "<tr><td colspan='3'>Нет преподавателей</td></tr>";
            }
            ?>
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
            <?php
            if ($teachers && $teachers->num_rows > 0) {
                while ($row = $teachers->fetch_assoc()) {
                    echo "<option value='{$row['id_teacher']}'>{$row['name']}</option>";
                }
            } else {
                echo "<option disabled>Нет доступных преподавателей</option>";
            }
            ?>
        </select><br><br>

        <label for="courses">Выберите курсы:</label><br>
        <?php
        if ($courses && $courses->num_rows > 0) {
            while ($row = $courses->fetch_assoc()) {
                echo "<input type='checkbox' name='course_ids[]' value='{$row['id_course']}'> {$row['course_name']}<br>";
            }
        } else {
            echo "Нет доступных курсов.";
        }
        ?>
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
            <?php
            if ($students && $students->num_rows > 0) {
                while ($row = $students->fetch_assoc()) {
                    $courses = isset($row['courses']) && !empty($row['courses']) ? nl2br($row['courses']) : 'Нет курсов';

                    $age = isset($row['age']) ? $row['age'] : 'Не указан';
                    echo "<tr>
                            <td>{$row['id_student']}</td>
                            <td>{$row['student_name']}</td>
                            <td>{$age}</td>
                            <td>{$row['teacher_name']}</td>
                            <td>{$courses}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>Нет учеников</td></tr>";
            }
            ?>
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
            <?php
            if ($teachers && $teachers->num_rows > 0) {
                while ($row = $teachers->fetch_assoc()) {
                    echo "<option value='{$row['id_teacher']}'>{$row['name']}</option>";
                }
            } else {
                echo "<option disabled>Нет доступных преподавателей</option>";
            }
            ?>
        </select><br><br>
        <input type="submit" name="add_course" value="Добавить курс">
    </form>

    <h2>Список курсов</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Название курса</th>
                <th>Длительность</th>
                <th>Преподаватель</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Выводим содержимое переменной $courses перед строкой 291
            $courses = fetch_courses($conn);

           // Если $courses — это объект mysqli_result и он содержит строки
            if ($courses instanceof mysqli_result && $courses->num_rows > 0) {
                while ($row = $courses->fetch_assoc()) {
                    // Выводим данные курсов
                    echo "<tr>
                            <td>{$row['id_course']}</td>
                            <td>{$row['course_name']}</td>
                            <td>{$row['duration']}</td>
                            <td>" . (empty($row['teacher_name']) ? 'Не указан' : $row['teacher_name']) . "</td>
                        </tr>";
                }
            } else {
                // Если курсов нет
                echo "<tr><td colspan='4'>Нет курсов</td></tr>";
            }

            $conn->close();
            ?>
        </tbody>
    </table>
</body>
</html>

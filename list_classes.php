<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <title>List All Classes</title>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-md">
    <a class="navbar-brand" href="#">School Management</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <!-- Navbar item with each directs to different pages -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="index.php">Students</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="list_parents.php">Parents</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="list_classes.php">Classes</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="list_activities.php">Activities</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="list_teachers.php">Teachers</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
            <!-- Show title, and button for add class -->
            <h1>All Classes</h1>
            <button class="btn btn-success" onclick="showAddClassForm()">Add Class</button>
        </div>
        <!-- Hidden form for adding new class that will show once button clicked -->
        <form action="" method="post" id="addClassForm" style="display:none;">
            <div class="form-group mb-3">
                <label for="newClassName">Class Name:</label>
                <input type="text" class="form-control" name="newClassName" id="newClassName" placeholder="Class Name" required>
            </div>
            <div class="form-group mb-3">
                <label for="newCapacity">Capacity:</label>
                <input type="number" class="form-control" name="newCapacity" id="newCapacity" placeholder="Capacity" required min="1">
            </div>
            <div class="form-group mb-3">
                <label for="newTeacherID">Teacher Name:</label>
                <select class="form-select" name="newTeacherID" id="newTeacherID" required>
                    <option value="">Select Teacher</option>
                    <?php
                    $link = mysqli_connect("localhost", "root", "password", "myschool");

                    // Check connection
                    if ($link == false) {
                        die("Connection failed: " . mysqli_connect_error());
                    }

                    // SQL to get available teachers
                    $sql_teachers = "SELECT TeacherID, TeacherName FROM Teachers WHERE TeacherID NOT IN (SELECT TeacherID FROM Classes)";
                    $result_teachers = $link->query($sql_teachers);
                    $teachers_available = false;
                    if ($result_teachers->num_rows > 0) {
                        $teachers_available = true;
                        while ($teacher = $result_teachers->fetch_assoc()) {
                            echo "<option value='" . $teacher['TeacherID'] . "'>" . $teacher['TeacherName'] . "</option>";
                        }
                    } else {
                        echo "<option value=''>No available teachers</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary mb-3" <?php if (!$teachers_available) echo 'disabled'; ?>>Submit</button>
        </form>

        <div class="accordion" id="classesAccordion">
            <?php
            // Function to get class and student names
            function getClassAndStudentNames($link, $studentID, $classID) {
                $names = array();

                $sql_student_name = "SELECT StudentName FROM Students WHERE StudentID = ?";
                $stmt_student_name = $link->prepare($sql_student_name);
                $stmt_student_name->bind_param("i", $studentID);
                $stmt_student_name->execute();
                $stmt_student_name->bind_result($names['studentName']);
                $stmt_student_name->fetch();
                $stmt_student_name->close();

                $sql_class_name = "SELECT ClassName FROM Classes WHERE ClassID = ?";
                $stmt_class_name = $link->prepare($sql_class_name);
                $stmt_class_name->bind_param("i", $classID);
                $stmt_class_name->execute();
                $stmt_class_name->bind_result($names['className']);
                $stmt_class_name->fetch();
                $stmt_class_name->close();

                return $names;
            }

            // Handle class update
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_class_id'])) {
                $classID = $_POST['update_class_id'];
                $updatedClassName = $_POST['updatedClassName'];
                $updatedCapacity = $_POST['updatedCapacity'];

                // Get the number of students in the class
                $sql_students_count = "SELECT COUNT(*) FROM Students WHERE ClassID = ?";
                $stmt_students_count = $link->prepare($sql_students_count);
                $stmt_students_count->bind_param("i", $classID);
                $stmt_students_count->execute();
                $stmt_students_count->bind_result($students_count);
                $stmt_students_count->fetch();
                $stmt_students_count->close();

                // When edit, check whether the number of capacity is less than the enrolled students in the class
                if ($updatedCapacity < $students_count) {
                    echo "<div class='alert alert-danger'>Error: Capacity cannot be less than the number of enrolled students ($students_count).</div>";
                } else {
                    $update_sql = "UPDATE Classes SET ClassName = ?, Capacity = ? WHERE ClassID = ?";
                    $stmt = $link->prepare($update_sql);
                    $stmt->bind_param("sii", $updatedClassName, $updatedCapacity, $classID);
                    if ($stmt->execute()) {
                        echo "<div class='alert alert-success'>Class updated successfully.</div>";
                    } else {
                        echo "<div class='alert alert-danger'>Error updating class: " . $link->error . "</div>";
                    }
                    $stmt->close();
                }
            }

            // Handle adding new class
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newClassName'])) {
                $newClassName = $_POST['newClassName'];
                $newCapacity = $_POST['newCapacity'];
                $newTeacherID = $_POST['newTeacherID'];

                if ($newCapacity <= 0) {
                    echo "<div class='alert alert-danger'>Error: Capacity must be greater than 0.</div>";
                } else {
                    $add_class_sql = "INSERT INTO Classes (ClassName, Capacity, TeacherID) VALUES (?, ?, ?)";
                    $stmt = $link->prepare($add_class_sql);
                    $stmt->bind_param("sii", $newClassName, $newCapacity, $newTeacherID);
                    if ($stmt->execute()) {
                        echo "<div class='alert alert-success'>New class added successfully.</div>";
                    } else {
                        echo "<div class='alert alert-danger'>Error adding new class: " . $link->error . "</div>";
                    }
                    $stmt->close();
                }
            }

            // Get classes details and student name
            $sql = "SELECT
                        c.ClassID,
                        c.ClassName,
                        c.Capacity,
                        t.TeacherName,
                        s.StudentID,
                        s.StudentName
                    FROM
                        Classes c
                    LEFT JOIN
                        Teachers t ON c.TeacherID = t.TeacherID
                    LEFT JOIN
                        Students s ON c.ClassID = s.ClassID";
            $result = $link->query($sql);

            if (!$result) {
                echo "Error: " . $link->error;
            } elseif ($result->num_rows > 0) {
                $classes = [];
                while ($row = $result->fetch_assoc()) {
                    $classID = $row['ClassID'];
                    if (!isset($classes[$classID])) {
                        $classes[$classID] = [
                            'ClassID' => $row['ClassID'],
                            'ClassName' => $row['ClassName'],
                            'Capacity' => $row['Capacity'],
                            'TeacherName' => $row['TeacherName'],
                            'Students' => []
                        ];
                    }
                    if ($row['StudentID']) {
                        $classes[$classID]['Students'][] = [
                            'StudentID' => $row['StudentID'],
                            'StudentName' => $row['StudentName']
                        ];
                    }
                }
                foreach ($classes as $class) {
                    $classID = $class['ClassID'];
                    $className = $class['ClassName'];
                    $capacity = $class['Capacity'];
                    $teacherName = $class['TeacherName'];
                    $students = $class['Students'];
                    $remaining_spaces = $capacity - count($students);
                ?>

                    <!-- Use accordion to show details of each class, like student names, and teacher name -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?php echo $classID; ?>">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $classID; ?>" aria-expanded="true" aria-controls="collapse<?php echo $classID; ?>">
                                <?php echo "$className | Capacity: $capacity | Remaining Spaces: $remaining_spaces"; ?>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $classID; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $classID; ?>" data-bs-parent="#classesAccordion">
                            <div class="accordion-body">
                                <h5>Teacher: <?php echo $teacherName; ?></h5>
                                <h5>Students in this Class:</h5>
                                <?php if (count($students) > 0) { ?>
                                    <ul class="list-group mb-3">
                                        <?php foreach ($students as $student) { ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?php echo $student['StudentName']; ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                <?php } else { ?>
                                    <p>No students in this class.</p>
                                <?php } ?>
                                <!-- Button and form for editing the class -->
                                <button class="btn btn-warning mb-3" onclick="showEditForm(<?php echo $classID; ?>)">Edit Class</button>
                                <form action="" method="post" id="editForm<?php echo $classID; ?>" style="display:none;">
                                    <input type="hidden" name="update_class_id" value="<?php echo $classID; ?>">
                                    <div class="form-group d-flex mb-3">
                                        <input type="text" class="form-control me-2" name="updatedClassName" value="<?php echo $className; ?>" placeholder="Class Name">
                                        <input type="number" class="form-control" name="updatedCapacity" value="<?php echo $capacity; ?>" placeholder="Capacity" min="1">
                                    </div>
                                    <button type="submit" class="btn btn-success mb-3">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php
                }
            } else {
                echo "<p>No classes found.</p>";
            }

            $link->close();
            ?>
        </div>
    </div>
    <script>
        // Functions to show form once button clicked
        function showEditForm(classID) {
            var form = document.getElementById('editForm' + classID);
            form.style.display = 'block';
        }

        function showAddClassForm() {
            var form = document.getElementById('addClassForm');
            form.style.display = 'block';
        }
    </script>
</body>
</html>

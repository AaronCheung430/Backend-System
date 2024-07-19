<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <title>List All Activities</title>
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
          <a class="nav-link" href="list_classes.php">Classes</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="list_activities.php">Activities</a>
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
            <!-- Show title, and button for add activity -->
            <h1>All Activities</h1>
            <button class="btn btn-success" onclick="showAddActivityForm()">Add Activity</button>
        </div>
        <!-- Hidden form for new activity, will show once button clicked -->
        <form action="" method="post" id="addActivityForm" style="display:none;">
            <div class="form-group d-flex mb-3">
                <input type="text" class="form-control me-2" name="newActivityName" placeholder="Activity Name" required>
                <input type="number" class="form-control" name="newCapacity" placeholder="Capacity" required>
            </div>
            <button type="submit" class="btn btn-primary mb-3">Submit</button>
        </form>

        <div class="accordion" id="activitiesAccordion">
            <?php
            $link = mysqli_connect("localhost", "root", "password", "myschool");

            // Check connection
            if ($link == false) {
                die("Connection failed: " . mysqli_connect_error());
            }

            // Function to get student and activity names
            function getStudentAndActivityNames($link, $studentID, $activityID) {
                $names = array();

                $sql_student_name = "SELECT StudentName FROM Students WHERE StudentID = ?";
                $stmt_student_name = $link->prepare($sql_student_name);
                $stmt_student_name->bind_param("i", $studentID);
                $stmt_student_name->execute();
                $stmt_student_name->bind_result($names['studentName']);
                $stmt_student_name->fetch();
                $stmt_student_name->close();

                $sql_activity_name = "SELECT ActivityName FROM Activities WHERE ActivityID = ?";
                $stmt_activity_name = $link->prepare($sql_activity_name);
                $stmt_activity_name->bind_param("i", $activityID);
                $stmt_activity_name->execute();
                $stmt_activity_name->bind_result($names['activityName']);
                $stmt_activity_name->fetch();
                $stmt_activity_name->close();

                return $names;
            }

            // Handle student removal
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_student_id'])) {
                $studentID = $_POST['remove_student_id'];
                $activityID = $_POST['activity_id'];

                $names = getStudentAndActivityNames($link, $studentID, $activityID);

                // Remove student from activity
                $delete_sql = "DELETE FROM Student_Activity_Relationship WHERE StudentID = ? AND ActivityID = ?";
                $stmt = $link->prepare($delete_sql);
                $stmt->bind_param("ii", $studentID, $activityID);
                if ($stmt->execute()) {
                    echo "<div class='alert alert-success'>{$names['studentName']} has been removed from {$names['activityName']} successfully.</div>";
                } else {
                    echo "<div class='alert alert-danger'>Error removing student: " . $link->error . "</div>";
                }
                $stmt->close();
            }

            // Handle activity update
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_activity_id'])) {
                $activityID = $_POST['update_activity_id'];
                $updatedActivityName = $_POST['updatedActivityName'];
                $updatedCapacity = $_POST['updatedCapacity'];

                // Get the number of students in the activity
                $sql_students_count = "SELECT COUNT(*) FROM Student_Activity_Relationship WHERE ActivityID = ?";
                $stmt_students_count = $link->prepare($sql_students_count);
                $stmt_students_count->bind_param("i", $activityID);
                $stmt_students_count->execute();
                $stmt_students_count->bind_result($students_count);
                $stmt_students_count->fetch();
                $stmt_students_count->close();

                if ($updatedCapacity < $students_count) {
                    echo "<div class='alert alert-danger'>Error: Capacity cannot be less than the number of enrolled students ($students_count).</div>";
                } else {
                    $update_sql = "UPDATE Activities SET ActivityName = ?, Capacity = ? WHERE ActivityID = ?";
                    $stmt = $link->prepare($update_sql);
                    $stmt->bind_param("sii", $updatedActivityName, $updatedCapacity, $activityID);
                    if ($stmt->execute()) {
                        echo "<div class='alert alert-success'>Activity updated successfully.</div>";
                    } else {
                        echo "<div class='alert alert-danger'>Error updating activity: " . $link->error . "</div>";
                    }
                    $stmt->close();
                }
            }

            // Handle adding student to activity
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student_id'])) {
                $studentID = $_POST['add_student_id'];
                $activityID = $_POST['activity_id'];

                $names = getStudentAndActivityNames($link, $studentID, $activityID);

                $add_sql = "INSERT INTO Student_Activity_Relationship (StudentID, ActivityID) VALUES (?, ?)";
                $stmt = $link->prepare($add_sql);
                $stmt->bind_param("ii", $studentID, $activityID);
                if ($stmt->execute()) {
                    echo "<div class='alert alert-success'>{$names['studentName']} has been added to {$names['activityName']} successfully.</div>";
                } else {
                    echo "<div class='alert alert-danger'>Error adding student: " . $link->error . "</div>";
                }
                $stmt->close();
            }

            // Handle adding new activity
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newActivityName'])) {
                $newActivityName = $_POST['newActivityName'];
                $newCapacity = $_POST['newCapacity'];

                $add_activity_sql = "INSERT INTO Activities (ActivityName, Capacity) VALUES (?, ?)";
                $stmt = $link->prepare($add_activity_sql);
                $stmt->bind_param("si", $newActivityName, $newCapacity);
                if ($stmt->execute()) {
                    echo "<div class='alert alert-success'>New activity added successfully.</div>";
                } else {
                    echo "<div class='alert alert-danger'>Error adding new activity: " . $link->error . "</div>";
                }
                $stmt->close();
            }

            // Get activities
            $sql = "SELECT * FROM Activities";
            $result = $link->query($sql);

            if ($result->num_rows > 0) {
                while ($activity = $result->fetch_assoc()) {
                    $activityID = $activity['ActivityID'];
                    $activityName = $activity['ActivityName'];
                    $capacity = $activity['Capacity'];

                    // Get students for each activity
                    $sql_students = "SELECT Students.StudentID, Students.StudentName FROM Student_Activity_Relationship
                                     JOIN Students ON Student_Activity_Relationship.StudentID = Students.StudentID
                                     WHERE Student_Activity_Relationship.ActivityID = ?";
                    $stmt_students = $link->prepare($sql_students);
                    $stmt_students->bind_param("i", $activityID);
                    $stmt_students->execute();
                    $students_result = $stmt_students->get_result();
                    $students = $students_result->fetch_all(MYSQLI_ASSOC);
                    $stmt_students->close();

                    // Calculate remaining spaces
                    $remaining_spaces = $capacity - count($students);

                    // Get all students not in this activity
                    $sql_all_students = "SELECT StudentID, StudentName FROM Students
                                         WHERE StudentID NOT IN (SELECT StudentID FROM Student_Activity_Relationship WHERE ActivityID = ?)";
                    $stmt_all_students = $link->prepare($sql_all_students);
                    $stmt_all_students->bind_param("i", $activityID);
                    $stmt_all_students->execute();
                    $all_students_result = $stmt_all_students->get_result();
                    $all_students = $all_students_result->fetch_all(MYSQLI_ASSOC);
                    $stmt_all_students->close();
            ?>
                    <!-- Use accordion to show details of each activity -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?php echo $activityID; ?>">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $activityID; ?>" aria-expanded="true" aria-controls="collapse<?php echo $activityID; ?>">
                                <?php echo "$activityName | Capacity: $capacity | Remaining Spaces: $remaining_spaces"; ?>
                            </button>
                        </h2>
                        <!-- Show students name in this activity -->
                        <div id="collapse<?php echo $activityID; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $activityID; ?>" data-bs-parent="#activitiesAccordion">
                            <div class="accordion-body">
                                <h5>Students in this Activity:</h5>
                                <?php if (count($students) > 0) { ?>
                                    <ul class="list-group mb-3">
                                        <?php foreach ($students as $student) { ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?php echo $student['StudentName']; ?>
                                                <!-- Button to remove student from activity -->
                                                <form action="" method="post" style="display:inline;">
                                                    <input type="hidden" name="remove_student_id" value="<?php echo $student['StudentID']; ?>">
                                                    <input type="hidden" name="activity_id" value="<?php echo $activityID; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                                </form>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                <?php } else { ?>
                                    <p>No students in this activity.</p>
                                <?php } ?>
                                <!-- Buttons for add student and edit activity -->
                                <button class="btn btn-primary mb-3" onclick="showAddStudentForm(<?php echo $activityID; ?>)" <?php echo $remaining_spaces <= 0 ? 'disabled' : ''; ?>>Add Student to Activity</button>
                                <button class="btn btn-warning mb-3" onclick="showEditForm(<?php echo $activityID; ?>)">Edit Activity</button>
                                <!-- Add student form and edit activity form are hidden -->
                                <form action="" method="post" id="addStudentForm<?php echo $activityID; ?>" style="display:none;">
                                    <input type="hidden" name="activity_id" value="<?php echo $activityID; ?>">
                                    <div class="form-group mb-3">
                                        <select class="form-select" name="add_student_id" required>
                                            <option value="">Select Student</option>
                                            <?php foreach ($all_students as $student) { ?>
                                                <option value="<?php echo $student['StudentID']; ?>"><?php echo $student['StudentName']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-success mb-3">Add Student</button>
                                </form>
                                <form action="" method="post" id="editForm<?php echo $activityID; ?>" style="display:none;">
                                    <input type="hidden" name="update_activity_id" value="<?php echo $activityID; ?>">
                                    <div class="form-group d-flex mb-3">
                                        <input type="text" class="form-control me-2" name="updatedActivityName" value="<?php echo $activityName; ?>" placeholder="Activity Name">
                                        <input type="number" class="form-control" name="updatedCapacity" value="<?php echo $capacity; ?>" placeholder="Capacity">
                                    </div>
                                    <button type="submit" class="btn btn-success mb-3">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo "<p>No activities found.</p>";
            }

            $link->close();
            ?>
        </div>
    </div>
    <script>
        // Functions to show form once button clicked
        function showEditForm(activityID) {
            var form = document.getElementById('editForm' + activityID);
            form.style.display = 'block';
        }

        function showAddStudentForm(activityID) {
            var form = document.getElementById('addStudentForm' + activityID);
            form.style.display = 'block';
        }

        function showAddActivityForm() {
            var form = document.getElementById('addActivityForm');
            form.style.display = 'block';
        }
    </script>
</body>
</html>

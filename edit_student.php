<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <title>Edit Student</title>
</head>
<body>

<div class="container">
    <h1 class="mt-4">Edit Student</h1>
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $link = mysqli_connect("localhost", "root", "password", "myschool");

    // Check connection
    if ($link === false) {
        die("Connection failed: " . mysqli_connect_error());
    }

    if (isset($_GET['id'])) {
        $studentID = $_GET['id'];

        // Fetch student details
        $sql = "SELECT * FROM Students WHERE StudentID = ?";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("i", $studentID);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $stmt->close();

        // Fetch student parents
        $sql = "SELECT ParentID FROM Student_Parent_Relationship WHERE StudentID = ?";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("i", $studentID);
        $stmt->execute();
        $result = $stmt->get_result();
        $studentParents = [];
        while ($row = $result->fetch_assoc()) {
            $studentParents[] = $row['ParentID'];
        }
        $stmt->close();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $studentID = $_POST['studentID'];
        $studentName = $_POST['studentName'];
        $gender = $_POST['gender'];
        $age = $_POST['age'];
        $medicalInfo = !empty($_POST['medicalInfo']) ? $_POST['medicalInfo'] : 'None';
        $classID = $_POST['classID'];
        $parents = $_POST['parents'];

        // Update student
        $update_student_sql = "UPDATE Students SET StudentName = ?, Gender = ?, Age = ?, MedicalInformation = ?, ClassID = ? WHERE StudentID = ?";
        $stmt = $link->prepare($update_student_sql);
        $stmt->bind_param("ssisii", $studentName, $gender, $age, $medicalInfo, $classID, $studentID);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success mt-4'>Student updated successfully.</div>";
        } else {
            echo "<div class='alert alert-danger mt-4'>Error updating student: " . $stmt->error . "</div>";
        }
        $stmt->close();

        // Update Student_Parent_Relationship
        // First delete existing relationships
        $delete_relationships_sql = "DELETE FROM Student_Parent_Relationship WHERE StudentID = ?";
        $stmt = $link->prepare($delete_relationships_sql);
        $stmt->bind_param("i", $studentID);
        $stmt->execute();
        $stmt->close();

        // Then insert new relationships
        foreach ($parents as $parentID) {
            $add_relationship_sql = "INSERT INTO Student_Parent_Relationship (StudentID, ParentID) VALUES (?, ?)";
            $stmt_relationship = $link->prepare($add_relationship_sql);
            $stmt_relationship->bind_param("ii", $studentID, $parentID);
            if (!$stmt_relationship->execute()) {
                die("Execute failed: " . $stmt_relationship->error);
            }
            $stmt_relationship->close();
        }

        echo "<div class='alert alert-success mt-4'>Student-Parent relationships updated successfully.</div>";

        // Add a delay before redirecting to allow the messages to be displayed
        echo "<script>
                setTimeout(function() {
                    window.location.href = 'edit_student.php?id=$studentID';
                }, 2000);
              </script>";
    }
    ?>
    <form action="edit_student.php?id=<?php echo $studentID; ?>" method="post">
        <input type="hidden" name="studentID" value="<?php echo $student['StudentID']; ?>">
        <div class="form-group mb-3">
            <label for="studentName">Student Name:</label>
            <input type="text" class="form-control" name="studentName" id="studentName" value="<?php echo $student['StudentName']; ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="gender">Gender:</label>
            <select class="form-control" name="gender" id="gender" required>
                <option value="M" <?php if ($student['Gender'] == 'M') echo 'selected'; ?>>M</option>
                <option value="F" <?php if ($student['Gender'] == 'F') echo 'selected'; ?>>F</option>
            </select>
        </div>
        <div class="form-group mb-3">
            <label for="age">Age:</label>
            <input type="number" class="form-control" name="age" id="age" value="<?php echo $student['Age']; ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="medicalInfo">Medical Information:</label>
            <textarea class="form-control" name="medicalInfo" id="medicalInfo"><?php echo $student['MedicalInformation']; ?></textarea>
        </div>
        <div class="form-group mb-3">
            <label for="classID">Class:</label>
            <select class="form-control" name="classID" id="classID" required>
                <option value="">Select Class</option>
                <?php
                $sql = "SELECT ClassID, ClassName FROM Classes";
                $result = $link->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $selected = ($row['ClassID'] == $student['ClassID']) ? 'selected' : '';
                        echo "<option value='" . $row['ClassID'] . "' $selected>" . $row['ClassName'] . "</option>";
                    }
                } else {
                    echo "<option value=''>No classes available</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group mb-3">
            <label for="parents">Parents:</label>
            <select class="form-control" name="parents[]" id="parents" multiple required>
                <?php
                $sql = "SELECT ParentID, ParentName FROM Parents";
                $result = $link->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $selected = in_array($row['ParentID'], $studentParents) ? 'selected' : '';
                        echo "<option value='" . $row['ParentID'] . "' $selected>" . $row['ParentName'] . "</option>";
                    }
                } else {
                    echo "<option value=''>No parents available</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
        <a href="list_students.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
    document.getElementById('parents').addEventListener('change', function() {
        const selectedOptions = Array.from(this.selectedOptions);
        if (selectedOptions.length > 2) {
            selectedOptions.slice(2).forEach(option => option.selected = false);
        }
    });
</script>

</body>
</html>

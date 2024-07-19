<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <title>Add Student</title>
</head>
<body>

<div class="container">
    <h1 class="mt-4">Add New Student</h1>
    <?php
    // Show PHP error message
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $link = mysqli_connect("localhost", "root", "password", "myschool");

        // Check connection
        if ($link === false) {
            die("Connection failed: " . mysqli_connect_error());
        }

        $studentName = $_POST['studentName'];
        $gender = $_POST['gender'];
        $age = $_POST['age'];
        $medicalInfo = !empty($_POST['medicalInfo']) ? $_POST['medicalInfo'] : 'None';
        $classID = $_POST['classID'];
        $parents = $_POST['parents'];

        // Add student
        $add_student_sql = "INSERT INTO Students (StudentName, Gender, Age, MedicalInformation, ClassID) VALUES (?, ?, ?, ?, ?)";
        $stmt = $link->prepare($add_student_sql);
        if ($stmt === false) {
            die("Prepare failed: " . $link->error);
        }
        $stmt->bind_param("ssisi", $studentName, $gender, $age, $medicalInfo, $classID);
        if ($stmt->execute()) {
            $studentID = $stmt->insert_id;
            echo "<div class='alert alert-success mt-4'>New student added successfully with ID $studentID.</div>";

            // Add into Student_Parent_Relationship
            foreach ($parents as $parentID) {
                $add_relationship_sql = "INSERT INTO Student_Parent_Relationship (StudentID, ParentID) VALUES (?, ?)";
                $stmt_relationship = $link->prepare($add_relationship_sql);
                if ($stmt_relationship === false) {
                    die("Prepare failed: " . $link->error);
                }
                $stmt_relationship->bind_param("ii", $studentID, $parentID);
                if (!$stmt_relationship->execute()) {
                    die("Execute failed: " . $stmt_relationship->error);
                }
                $stmt_relationship->close();
            }

            echo "<div class='alert alert-success mt-4'>Student-Parent relationships added successfully.</div>";
        } else {
            echo "<div class='alert alert-danger mt-4'>Error adding new student: " . $stmt->error . "</div>";
        }
        $stmt->close();
        $link->close();
    }
    ?>
    <!-- Form to add new student -->
    <form action="" method="post">
        <div class="form-group mb-3">
            <label for="studentName">Student Name:</label>
            <input type="text" class="form-control" name="studentName" id="studentName" placeholder="Student Name" required>
        </div>
        <div class="form-group mb-3">
            <label for="gender">Gender:</label>
            <select class="form-control" name="gender" id="gender" required>
                <option value="">Select Gender</option>
                <option value="M">M</option>
                <option value="F">F</option>
            </select>
        </div>
        <div class="form-group mb-3">
            <label for="age">Age:</label>
            <input type="number" class="form-control" name="age" id="age" placeholder="Age" required>
        </div>
        <div class="form-group mb-3">
            <label for="medicalInfo">Medical Information:</label>
            <textarea class="form-control" name="medicalInfo" id="medicalInfo" placeholder="Medical Information"></textarea>
        </div>
        <div class="form-group mb-3">
            <label for="classID">Class:</label>
            <!-- Get classes' names from database -->
            <select class="form-control" name="classID" id="classID" required>
                <option value="">Select Class</option>
                <?php
                $link = mysqli_connect("localhost", "root", "password", "myschool");
                if ($link === false) {
                    die("Connection failed: " . mysqli_connect_error());
                }
                $sql = "SELECT ClassID, ClassName FROM Classes";
                $result = $link->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['ClassID'] . "'>" . $row['ClassName'] . "</option>";
                    }
                } else {
                    echo "<option value=''>No classes available</option>";
                }
                $link->close();
                ?>
            </select>
        </div>
        <div class="form-group mb-3">
            <label for="parents">Parents: (Maximum of 2 parents)</label>
            <!-- Get parents' names from database -->
            <select class="form-control" name="parents[]" id="parents" multiple required>
                <?php
                $link = mysqli_connect("localhost", "root", "password", "myschool");
                if ($link === false) {
                    die("Connection failed: " . mysqli_connect_error());
                }
                $sql = "SELECT ParentID, ParentName FROM Parents";
                $result = $link->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['ParentID'] . "'>" . $row['ParentName'] . "</option>";
                    }
                } else {
                    echo "<option value=''>No parents available</option>";
                }
                $link->close();
                ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
        <a href="list_students.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
    // Check did the user choose more than 2 parents
    document.getElementById('parents').addEventListener('change', function() {
        const selectedOptions = Array.from(this.selectedOptions);
        if (selectedOptions.length > 2) {
            selectedOptions.slice(2).forEach(option => option.selected = false);
        }
    });
</script>

</body>
</html>

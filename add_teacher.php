<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <title>Add Teacher</title>
</head>
<body>
    <div class="container">
        <h1 class="mt-4">Add New Teacher</h1>
        <?php
        // Show PHP error message
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $link = mysqli_connect("localhost", "root", "password", "myschool");

            // Check connection
            if ($link == false) {
                die("Connection failed: " . mysqli_connect_error());
            }

            $teacherName = $_POST['teacherName'];
            $address = $_POST['address'];
            $phoneNumber = $_POST['phoneNumber'];
            $annualSalary = $_POST['annualSalary'];
            $backgroundChecked = isset($_POST['backgroundChecked']) ? 1 : 0;

            // SQL to add new teacher data to database
            $sql = "INSERT INTO Teachers (TeacherName, Address, PhoneNumber, AnnualSalary, BackgroundChecked) VALUES (?, ?, ?, ?, ?)";
            $stmt = $link->prepare($sql);
            $stmt->bind_param("sssid", $teacherName, $address, $phoneNumber, $annualSalary, $backgroundChecked);

            if ($stmt->execute()) {
                echo "<div class='alert alert-success'>New teacher added successfully.</div>";
            } else {
                echo "<div class='alert alert-danger'>Error adding teacher: " . $link->error . "</div>";
            }

            $stmt->close();
            $link->close();
        }
        ?>
        <!-- Form to add new teacher -->
        <form action="" method="post">
            <div class="form-group">
                <label for="teacherName">Teacher Name</label>
                <input type="text" class="form-control" id="teacherName" name="teacherName" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" class="form-control" id="address" name="address" required>
            </div>
            <div class="form-group">
                <label for="phoneNumber">Phone Number</label>
                <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" required>
            </div>
            <div class="form-group">
                <label for="annualSalary">Annual Salary</label>
                <input type="number" step="0.01" class="form-control" id="annualSalary" name="annualSalary" required>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="backgroundChecked" name="backgroundChecked">
                <label class="form-check-label" for="backgroundChecked">Background Checked</label>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Add Teacher</button>
        </form>
        <a href="list_teachers.php" class="btn btn-secondary mt-3">Back to Teachers List</a>
    </div>
</body>
</html>

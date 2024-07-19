<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <title>Edit Teacher</title>
</head>
<body>

<div class="container">
    <h1 class="mt-4">Edit Teacher</h1>
    <?php
    $link = mysqli_connect("localhost", "root", "password", "myschool");

    // Check connection
    if ($link == false) {
        die("Connection failed: " . mysqli_connect_error());
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        $teacherID = $_GET['id'];

        // Fetch teacher details
        $sql = "SELECT TeacherID, TeacherName, Address, PhoneNumber, AnnualSalary, BackgroundChecked FROM Teachers WHERE TeacherID = ?";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("i", $teacherID);
        $stmt->execute();
        $stmt->bind_result($teacherID, $teacherName, $address, $phone, $salary, $backgroundChecked);
        $stmt->fetch();
        $stmt->close();

        // Check if teacher is assigned to any class
        $sql_class = "SELECT COUNT(*) FROM Classes WHERE TeacherID = ?";
        $stmt_class = $link->prepare($sql_class);
        $stmt_class->bind_param("i", $teacherID);
        $stmt_class->execute();
        $stmt_class->bind_result($classCount);
        $stmt_class->fetch();
        $stmt_class->close();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_teacher_id'])) {
        $teacherID = $_POST['update_teacher_id'];
        $updatedTeacherName = $_POST['updatedTeacherName'];
        $updatedAddress = $_POST['updatedAddress'];
        $updatedPhone = $_POST['updatedPhone'];
        $updatedSalary = $_POST['updatedSalary'];
        $updatedBackgroundChecked = isset($_POST['updatedBackgroundChecked']) ? 1 : 0;

        $update_sql = "UPDATE Teachers SET TeacherName = ?, Address = ?, PhoneNumber = ?, AnnualSalary = ?, BackgroundChecked = ? WHERE TeacherID = ?";
        $stmt = $link->prepare($update_sql);
        $stmt->bind_param("sssisi", $updatedTeacherName, $updatedAddress, $updatedPhone, $updatedSalary, $updatedBackgroundChecked, $teacherID);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success mt-4'>Teacher updated successfully.</div>";
        } else {
            echo "<div class='alert alert-danger mt-4'>Error updating teacher: " . $link->error . "</div>";
        }
        $stmt->close();

        // Refresh data
        $sql = "SELECT TeacherID, TeacherName, Address, PhoneNumber, AnnualSalary, BackgroundChecked FROM Teachers WHERE TeacherID = ?";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("i", $teacherID);
        $stmt->execute();
        $stmt->bind_result($teacherID, $teacherName, $address, $phone, $salary, $backgroundChecked);
        $stmt->fetch();
        $stmt->close();

        $sql_class = "SELECT COUNT(*) FROM Classes WHERE TeacherID = ?";
        $stmt_class = $link->prepare($sql_class);
        $stmt_class->bind_param("i", $teacherID);
        $stmt_class->execute();
        $stmt_class->bind_result($classCount);
        $stmt_class->fetch();
        $stmt_class->close();
    }

    // Handle delete request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_teacher_id'])) {
        $teacherID = $_POST['delete_teacher_id'];

        $delete_sql = "DELETE FROM Teachers WHERE TeacherID = ?";
        $stmt = $link->prepare($delete_sql);
        $stmt->bind_param("i", $teacherID);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success mt-4'>Teacher deleted successfully.</div>";
        } else {
            echo "<div class='alert alert-danger mt-4'>Error deleting teacher: " . $link->error . "</div>";
        }
        $stmt->close();
    }

    $link->close();
    ?>

    <form action="" method="post">
        <input type="hidden" name="update_teacher_id" value="<?php echo $teacherID; ?>">
        <div class="form-group mb-3">
            <label for="updatedTeacherName">Teacher Name:</label>
            <input type="text" class="form-control" name="updatedTeacherName" id="updatedTeacherName" value="<?php echo $teacherName; ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="updatedAddress">Address:</label>
            <input type="text" class="form-control" name="updatedAddress" id="updatedAddress" value="<?php echo $address; ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="updatedPhone">Phone Number:</label>
            <input type="text" class="form-control" name="updatedPhone" id="updatedPhone" value="<?php echo $phone; ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="updatedSalary">Annual Salary:</label>
            <input type="number" class="form-control" name="updatedSalary" id="updatedSalary" value="<?php echo $salary; ?>" required>
        </div>
        <div class="form-group form-check mb-3">
            <input type="checkbox" class="form-check-input" name="updatedBackgroundChecked" id="updatedBackgroundChecked" <?php echo $backgroundChecked ? 'checked' : ''; ?>>
            <label class="form-check-label" for="updatedBackgroundChecked">Background Checked</label>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="list_teachers.php" class="btn btn-secondary">Cancel</a>
    </form>

    <?php if ($classCount == 0) { ?>
        <form action="" method="post" style="margin-top: 20px;">
            <input type="hidden" name="delete_teacher_id" value="<?php echo $teacherID; ?>">
            <button type="submit" class="btn btn-danger">Delete Teacher</button>
        </form>
    <?php } ?>
</div>

</body>
</html>

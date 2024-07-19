<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <title>Edit Parent</title>
</head>
<body>

<div class="container">
    <h1 class="mt-4">Edit Parent</h1>
    <?php
    $link = mysqli_connect("localhost", "root", "password", "myschool");

    // Check connection
    if ($link == false) {
        die("Connection failed: " . mysqli_connect_error());
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        $parentID = $_GET['id'];

        // Get parent details
        $sql = "SELECT ParentID, ParentName, Address, Email, PhoneNumber FROM Parents WHERE ParentID = ?";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("i", $parentID);
        $stmt->execute();
        $stmt->bind_result($parentID, $parentName, $address, $email, $phone);
        $stmt->fetch();
        $stmt->close();

        // Get associated students
        $sql_students = "SELECT s.StudentName FROM Students s
                         JOIN Student_Parent_Relationship spr ON s.StudentID = spr.StudentID
                         WHERE spr.ParentID = ?";
        $stmt_students = $link->prepare($sql_students);
        $stmt_students->bind_param("i", $parentID);
        $stmt_students->execute();
        $result_students = $stmt_students->get_result();
        $students = [];
        while ($row = $result_students->fetch_assoc()) {
            $students[] = $row['StudentName'];
        }
        $stmt_students->close();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_parent_id'])) {
        $parentID = $_POST['update_parent_id'];
        $updatedParentName = $_POST['updatedParentName'];
        $updatedAddress = $_POST['updatedAddress'];
        $updatedEmail = $_POST['updatedEmail'];
        $updatedPhone = $_POST['updatedPhone'];

        $update_sql = "UPDATE Parents SET ParentName = ?, Address = ?, Email = ?, PhoneNumber = ? WHERE ParentID = ?";
        $stmt = $link->prepare($update_sql);
        $stmt->bind_param("ssssi", $updatedParentName, $updatedAddress, $updatedEmail, $updatedPhone, $parentID);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success mt-4'>Parent updated successfully.</div>";
        } else {
            echo "<div class='alert alert-danger mt-4'>Error updating parent: " . $link->error . "</div>";
        }
        $stmt->close();

        // Refresh data
        $sql = "SELECT ParentID, ParentName, Address, Email, PhoneNumber FROM Parents WHERE ParentID = ?";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("i", $parentID);
        $stmt->execute();
        $stmt->bind_result($parentID, $parentName, $address, $email, $phone);
        $stmt->fetch();
        $stmt->close();

        $sql_students = "SELECT s.StudentName FROM Students s
                         JOIN Student_Parent_Relationship spr ON s.StudentID = spr.StudentID
                         WHERE spr.ParentID = ?";
        $stmt_students = $link->prepare($sql_students);
        $stmt_students->bind_param("i", $parentID);
        $stmt_students->execute();
        $result_students = $stmt_students->get_result();
        $students = [];
        while ($row = $result_students->fetch_assoc()) {
            $students[] = $row['StudentName'];
        }
        $stmt_students->close();
    }

    // Handle delete request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_parent_id'])) {
        $parentID = $_POST['delete_parent_id'];

        $delete_sql = "DELETE FROM Parents WHERE ParentID = ?";
        $stmt = $link->prepare($delete_sql);
        $stmt->bind_param("i", $parentID);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success mt-4'>Parent deleted successfully.</div>";
        } else {
            echo "<div class='alert alert-danger mt-4'>Error deleting parent: " . $link->error . "</div>";
        }
        $stmt->close();
    }

    $link->close();
    ?>

    <form action="" method="post">
        <input type="hidden" name="update_parent_id" value="<?php echo $parentID; ?>">
        <div class="form-group mb-3">
            <label for="updatedParentName">Parent Name:</label>
            <input type="text" class="form-control" name="updatedParentName" id="updatedParentName" value="<?php echo $parentName; ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="updatedAddress">Address:</label>
            <input type="text" class="form-control" name="updatedAddress" id="updatedAddress" value="<?php echo $address; ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="updatedEmail">Email:</label>
            <input type="email" class="form-control" name="updatedEmail" id="updatedEmail" value="<?php echo $email; ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="updatedPhone">Phone Number:</label>
            <input type="text" class="form-control" name="updatedPhone" id="updatedPhone" value="<?php echo $phone; ?>" required>
        </div>
        <div class="form-group mb-3">
            <label>Associated Students:</label>
            <ul class="list-group">
                <?php foreach ($students as $student) { ?>
                    <li class="list-group-item"><?php echo $student; ?></li>
                <?php } ?>
            </ul>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="list_parents.php" class="btn btn-secondary">Cancel</a>
    </form>

    <?php if (count($students) == 0) { ?>
        <form action="" method="post" style="margin-top: 20px;">
            <input type="hidden" name="delete_parent_id" value="<?php echo $parentID; ?>">
            <button type="submit" class="btn btn-danger">Delete Parent</button>
        </form>
    <?php } ?>
</div>

</body>
</html>

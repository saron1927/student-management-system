<?php
include 'config.php';

if(isset($_GET['id'])){
    $id = $_GET['id'];
    $sql = "SELECT * FROM students WHERE id=$id";
    $result = $conn->query($sql);
    $student = $result->fetch_assoc();
}

if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $id = $_POST['id'];
    $sql = "UPDATE students SET name='$name' WHERE id=$id";
    if($conn->query($sql) === TRUE){
        echo "Student updated successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<h2>Edit Student</h2>
<form method="POST">
    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
    <input type="text" name="name" value="<?php echo $student['name']; ?>" required>
    <input type="submit" name="submit" value="Update Student">
</form>
<a href="list_students.php">Back to List</a>
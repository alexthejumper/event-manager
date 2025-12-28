<?php
include('db.php');

$id = $_POST['id'];
$title = $_POST['title'];
$description = $_POST['description'];
$start = $_POST['start_datetime'];
$end = $_POST['end_datetime'];

$sql = "UPDATE events 
        SET title='$title', description='$description',
            start_datetime='$start', end_datetime='$end'
        WHERE id=$id";

if ($conn->query($sql) === TRUE) {
  header("Location: index.php");
  exit();
} else {
  echo "Error updating record: " . $conn->error;
}
?>

<?php
include('db.php');

$title = $_POST['title'];
$description = $_POST['description'];
$start = $_POST['start_datetime'];
$end = $_POST['end_datetime'];

$sql = "INSERT INTO events (title, description, start_datetime, end_datetime)
        VALUES ('$title', '$description', '$start', '$end')";

if ($conn->query($sql) === TRUE) {
  header("Location: index.php");
  exit();
} else {
  echo "Error: " . $conn->error;
}
?>

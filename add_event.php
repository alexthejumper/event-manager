<?php
// add_event.php
include 'db.php';

$errors = [];
$old = [
    'title' => '',
    'description' => '',
    'start_datetime' => '',
    'end_datetime' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect & sanitize
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $start_raw = trim($_POST['start_datetime'] ?? '');
    $end_raw = trim($_POST['end_datetime'] ?? '');

    $old['title'] = $title;
    $old['description'] = $description;
    $old['start_datetime'] = $start_raw;
    $old['end_datetime'] = $end_raw;

    // Basic validations
    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if ($start_raw === '') {
        $errors[] = 'Start date & time is required.';
    }
    if ($end_raw === '') {
        $errors[] = 'End date & time is required.';
    }

    // Parse datetimes (datetime-local format: "YYYY-MM-DDTHH:MM")
    $start_dt = null;
    $end_dt = null;
    if ($start_raw !== '') {
        $start_dt = DateTime::createFromFormat('Y-m-d\TH:i', $start_raw);
        if (!$start_dt) $errors[] = 'Invalid start date/time format.';
    }
    if ($end_raw !== '') {
        $end_dt = DateTime::createFromFormat('Y-m-d\TH:i', $end_raw);
        if (!$end_dt) $errors[] = 'Invalid end date/time format.';
    }

    // Further checks if parsed
    if ($start_dt && $end_dt) {
        $now = new DateTime();

        if ($start_dt < $now) {
            $errors[] = 'Start date/time cannot be in the past.';
        }
        if ($end_dt <= $start_dt) {
            $errors[] = 'End date/time must be after start date/time.';
        }
    }

    // If no errors -> insert
    if (empty($errors)) {
        $start_for_db = $start_dt->format('Y-m-d H:i:s');
        $end_for_db = $end_dt->format('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO events (title, description, start_datetime, end_datetime) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $title, $description, $start_for_db, $end_for_db);
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: index.php'); // assumes you have index.php listing events
            exit();
        } else {
            $errors[] = "Database error: " . $stmt->error;
            $stmt->close();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add Event</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-4">
<div class="container">
  <h1 class="mb-4">Add Event</h1>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <form id="eventForm" method="POST" novalidate class="needs-validation">
        <div class="mb-3">
          <label class="form-label">Title <span class="text-danger">*</span></label>
          <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($old['title']) ?>">
          <div class="invalid-feedback">Please provide a title.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control"><?= htmlspecialchars($old['description']) ?></textarea>
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Start Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" name="start_datetime" id="start_datetime" class="form-control" required
                   value="<?= htmlspecialchars($old['start_datetime']) ?>">
            <div class="invalid-feedback">Please choose a valid start date & time.</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">End Date & Time <span class="text-danger">*</span></label>
            <input type="datetime-local" name="end_datetime" id="end_datetime" class="form-control" required
                   value="<?= htmlspecialchars($old['end_datetime']) ?>">
            <div class="invalid-feedback">Please choose a valid end date & time (after start).</div>
          </div>
        </div>

        <div class="mt-4 d-flex gap-2">
          <button class="btn btn-primary">Save Event</button>
          <a href="index.php" class="btn btn-secondary">Back</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Client-side validation + date checks
(function () {
  'use strict';
  const form = document.getElementById('eventForm');

  form.addEventListener('submit', function (evt) {
    // Bootstrap built-in validation
    if (!form.checkValidity()) {
      evt.preventDefault();
      evt.stopPropagation();
      form.classList.add('was-validated');
      return;
    }

    // Additional JS checks for datetime-local
    const startVal = document.getElementById('start_datetime').value;
    const endVal = document.getElementById('end_datetime').value;
    if (!startVal || !endVal) {
      evt.preventDefault();
      evt.stopPropagation();
      form.classList.add('was-validated');
      return;
    }

    const start = new Date(startVal);
    const end = new Date(endVal);
    const now = new Date();

    if (isNaN(start.getTime()) || isNaN(end.getTime())) {
      alert('Invalid date/time format.');
      evt.preventDefault();
      evt.stopPropagation();
      return;
    }

    if (start < now) {
      alert('Start date/time cannot be in the past.');
      evt.preventDefault();
      evt.stopPropagation();
      return;
    }

    if (end <= start) {
      alert('End date/time must be after start date/time.');
      evt.preventDefault();
      evt.stopPropagation();
      return;
    }

    // Allow submit
    form.classList.add('was-validated');
  }, false);
})();
</script>
</body>
</html>

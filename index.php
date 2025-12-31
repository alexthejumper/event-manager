<?php include('db.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Event Manager</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4 bg-light">

<div class="container">
  <h2 class="mb-4">Event Manager</h2>
  <a href="add_event.php" class="btn btn-success mb-3">Add New Event</a>

  <!-- Countdown Display Section -->
  <div class="alert alert-primary text-center" id="countdownArea" style="font-size: 1.5rem;">
    Select an event to show its countdown
  </div>

  <div class="row">
    <?php
    $result = $conn->query("SELECT * FROM events ORDER BY start_datetime ASC");
    while ($event = $result->fetch_assoc()):
    ?>
      <div class="col-md-4 mb-4">
        <div class="card p-3 shadow-sm">
          <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
          <p class="card-text"><?= htmlspecialchars($event['description']) ?></p>
          <p><b>Starts:</b> <?= $event['start_datetime'] ?></p>
          <p><b>Ends:</b> <?= $event['end_datetime'] ?></p>

          <button 
            class="btn btn-primary w-100 mb-2"
            onclick="showCountdown('<?= $event['title'] ?>', '<?= $event['start_datetime'] ?>', '<?= $event['end_datetime'] ?>')">
            Show Countdown
          </button>

          <button 
  class="btn btn-outline-secondary btn-sm w-100 mb-2"
  onclick="startFixedCountdown(
    '<?= $event['title'] ?>',
    '<?= $event['start_datetime'] ?>',
    '<?= $event['end_datetime'] ?>'
  )">
  Start Duration Countdown
</button>


          <div class="d-flex gap-2">
            <a href="edit_event.php?id=<?= $event['id'] ?>" class="btn btn-warning w-50">Update</a>
            <a href="delete_event.php?id=<?= $event['id'] ?>" 
               class="btn btn-danger w-50"
               onclick="return confirm('Are you sure you want to delete this event?');">
               Delete
            </a>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<script>
  let countdownTimer;

  function showCountdown(title, start, end) {
    clearInterval(countdownTimer);

    const countdownArea = document.getElementById("countdownArea");
    const startTime = new Date(start).getTime();
    const endTime = new Date(end).getTime();

    countdownArea.innerHTML = `<strong>${title}</strong><br><span id="countdownText">Loading...</span>`;

    countdownTimer = setInterval(() => {
      const now = new Date().getTime();
      let distance;
      let message;

      if (now < startTime) {
        distance = startTime - now;
        message = "Starts in";
      } else if (now >= startTime && now <= endTime) {
        distance = endTime - now;
        message = "Ends in";
      } else {
        countdownArea.innerHTML = `<strong>${title}</strong><br>Event has ended.`;
        clearInterval(countdownTimer);
        return;
      }

      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      document.getElementById("countdownText").innerText =
        `${message}: ${days}d ${hours}h ${minutes}m ${seconds}s`;
    }, 1000);
  }
</script>


<script>
  function startFixedCountdown(title, start, end) {
    clearInterval(countdownTimer);

    const countdownArea = document.getElementById("countdownArea");

    const startTime = new Date(start).getTime();
    const endTime = new Date(end).getTime();

    let remainingSeconds = Math.floor((endTime - startTime) / 1000);

    if (remainingSeconds <= 0) {
      countdownArea.innerHTML =
        `<strong>${title}</strong><br>Invalid duration.`;
      return;
    }

    countdownArea.innerHTML =
      `<strong>${title}</strong><br><span id="countdownText"></span>`;

    countdownTimer = setInterval(() => {
      if (remainingSeconds <= 0) {
        document.getElementById("countdownText").innerText = "00:00";
        clearInterval(countdownTimer);
        return;
      }

      const minutes = Math.floor(remainingSeconds / 60);
      const seconds = remainingSeconds % 60;

      document.getElementById("countdownText").innerText =
        `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

      remainingSeconds--;
    }, 1000);
  }
</script>



</body>
</html>

<?php
session_start();

include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $to = $_POST['to'];
  $subject = $_POST['subject'];
  $msg = $_POST['msg'];

  $sql = "INSERT INTO gmail (recipient_email, email_subject, message_text) VALUES (?, ?, ?)";

  if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("sss", $to, $subject, $msg);

    if ($stmt->execute()) {
      echo "Inserción exitosa";
    } else {
      echo "Error al insertar: " . $stmt->error;
    }
    $stmt->close();
  } else {
    echo "Error en la preparación de la consulta: " . $conn->error;
  }

  $conn->close();

  header("Location: ./send_gmail.php");
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gmail-Composer</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css" />
  <script src="https://kit.fontawesome.com/a41d3240c2.js" crossorigin="anonymous"></script>
  <link rel="icon" href="./assets/paper-plane.png" type="image/png">
  <link rel="stylesheet" href="./assets/font.css">
</head>

<body>
  <main class="container" style="text-align: center; display: flex; align-items: center; justify-content: center;">
    <article style="margin: 0px; width: 100%;">
      <header>
        <hgroup>
          <h1>Gmail-Composer</h1>
          <a href="https://www.github.com/xrimsonn/">José Antonio Rosales</a>
        </hgroup>
        <i class="fa-solid fa-paper-plane fa-beat-fade fa-2xl"></i>
      </header>
      <form action="./index.php" method="POST">
        <label for="destinatario">To:</label>
        <input type="email" name="to" id="to" placeholder="example@gmail.com" required><br />

        <label for="asunto">Subject:</label>
        <input type="text" name="subject" id="subject" placeholder="School..." required><br />

        <label for="mensaje">Message:</label>
        <textarea name="msg" id="msg" placeholder="Today I woke up this mornig..." required></textarea><br />

        <button type="submit" class="contrast">Send</button>
      </form>
      <p><button role="button" id="themeButton" class="contrast" data-tooltip="Toggle theme" style="margin-bottom: 0px;"><i class="fa-solid fa-circle-half-stroke"></i></button></p>
      <footer style="text-align: center;">
        &copy;2023 José Antonio Rosales <br>
        <a class="secondary" target="_blank" href="https://www.instagram.com/antonnn_o/"><i class="fa-brands fa-instagram fa-lg"></i></a>
        <a class="secondary" target="_blank" href="https://github.com/xrimsonn"><i class="fa-brands fa-github fa-lg"></i></a>
        <a class="secondary" target="_blank" href="https://www.linkedin.com/in/antonio-rosales-207793263/"><i class="fa-brands fa-linkedin fa-lg"></i></a>
      </footer>
    </article>
  </main>
  <script src="./assets/toggle_theme.js"></script>
</body>

</html>
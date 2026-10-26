<?php
require 'config.php';

/* Only accept data sent from the form */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: join.html');
    exit;
}

/* Clean the data so nobody can sneak in bad code */
function clean($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

$fullname  = clean($_POST['fullname']  ?? '');
$regnumber = strtoupper(clean($_POST['regnumber'] ?? ''));
$email     = clean($_POST['email']     ?? '');
$phone     = clean($_POST['phone']     ?? '');
$programme = clean($_POST['programme'] ?? '');
$purpose   = clean($_POST['purpose']   ?? '');
$message   = clean($_POST['message']   ?? '');

/* Check again on the server (JavaScript can be switched off) */
$errors = [];
if (strlen($fullname) < 3)                              $errors[] = 'Name is too short.';
if (!preg_match('/^[A-Za-z][0-9]{6}[A-Za-z]$/', $regnumber)) $errors[] = 'Registration number is not valid.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))         $errors[] = 'Email is not valid.';
if (!preg_match('/^0[0-9]{9}$/', $phone))               $errors[] = 'Phone number is not valid.';
if ($programme === '')                                  $errors[] = 'Please choose a programme.';
if ($purpose === '')                                    $errors[] = 'Please choose join or contact.';
if (strlen($message) < 10)                              $errors[] = 'Message is too short.';

$saved = false;
$saveNote = '';

if (count($errors) === 0) {
    if (USE_DATABASE) {
        /* ---- Save in MySQL ---- */
        try {
            $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("CREATE TABLE IF NOT EXISTS submissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                fullname VARCHAR(100), regnumber VARCHAR(20), email VARCHAR(100),
                phone VARCHAR(20), programme VARCHAR(60), purpose VARCHAR(30),
                message TEXT, submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
            $stmt = $pdo->prepare("INSERT INTO submissions
                (fullname, regnumber, email, phone, programme, purpose, message)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$fullname, $regnumber, $email, $phone, $programme, $purpose, $message]);
            $saved = true;
            $saveNote = 'Your details were saved in our database.';
        } catch (PDOException $e) {
            $errors[] = 'Sorry, we could not save your details right now.';
        }
    } else {
        /* ---- Save in a text file (no database needed) ---- */
        $line = implode(' | ', [date('Y-m-d H:i'), $fullname, $regnumber, $email, $phone, $programme, $purpose, str_replace(["\r", "\n"], ' ', $message)]) . "\n";
        if (file_put_contents('submissions.txt', $line, FILE_APPEND | LOCK_EX) !== false) {
            $saved = true;
            $saveNote = 'Your details were saved.';
        } else {
            $errors[] = 'Sorry, we could not save your details right now.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Form Result | Tech Innovators Club</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body onload="pageLoaded()">
  <header>
    <div class="brand"><img src="images/logo.svg" alt="Club logo" width="50" height="50"><h1>Tech Innovators Club</h1></div>
    <nav>
      <ul>
        <li><a href="index.html">Home</a></li>
        <li><a href="about.html">About Us</a></li>
        <li><a href="events.html">Activities &amp; Events</a></li>
        <li><a href="gallery.html">Gallery</a></li>
        <li><a href="join.html" class="active">Join / Contact</a></li>
      </ul>
    </nav>
  </header>
  <main>
    <section class="card">
<?php if ($saved): ?>
      <h2>Thank you, <?php echo $fullname; ?>!</h2>
      <p><?php echo $saveNote; ?> We will contact you soon.</p>
      <table>
        <tr><th>Registration number</th><td><?php echo $regnumber; ?></td></tr>
        <tr><th>Email</th><td><?php echo $email; ?></td></tr>
        <tr><th>Phone</th><td><?php echo $phone; ?></td></tr>
        <tr><th>Programme</th><td><?php echo $programme; ?></td></tr>
        <tr><th>Request</th><td><?php echo $purpose; ?></td></tr>
        <tr><th>Message</th><td><?php echo nl2br($message); ?></td></tr>
      </table>
      <p><a class="btn" href="index.html">Back to Home</a></p>
<?php else: ?>
      <h2>Oops! Something went wrong</h2>
      <ul>
<?php foreach ($errors as $e): ?>
        <li class="error"><?php echo $e; ?></li>
<?php endforeach; ?>
      </ul>
      <p><a class="btn" href="join.html">Go back and fix the form</a></p>
<?php endif; ?>
    </section>
  </main>
  <footer>
    <p>&copy; 2026 Tech Innovators Club | Harare Institute of Technology</p>
  </footer>
  <script src="js/script.js"></script>
</body>
</html>

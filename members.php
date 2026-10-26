<?php
require 'config.php';

/* Simple password: open members.php?key=club2026 */
if (($_GET['key'] ?? '') !== ADMIN_KEY) {
    http_response_code(403);
    echo 'Access denied. Add ?key=YOUR_PASSWORD to the address.';
    exit;
}

$rows = [];
if (USE_DATABASE) {
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $rows = $pdo->query("SELECT submitted_at, fullname, regnumber, email, phone, programme, purpose, message FROM submissions ORDER BY id DESC")->fetchAll(PDO::FETCH_NUM);
    } catch (PDOException $e) {
        echo 'Could not read the database.';
        exit;
    }
} elseif (file_exists('submissions.txt')) {
    foreach (array_reverse(file('submissions.txt', FILE_IGNORE_NEW_LINES)) as $line) {
        $rows[] = explode(' | ', $line);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Submitted Forms | Tech Innovators Club</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <main>
    <section class="card">
      <h2>Submitted Forms (<?php echo count($rows); ?>)</h2>
      <table>
        <tr><th>Date</th><th>Name</th><th>Reg No.</th><th>Email</th><th>Phone</th><th>Programme</th><th>Request</th><th>Message</th></tr>
<?php foreach ($rows as $r): ?>
        <tr><?php foreach ($r as $cell): ?><td><?php echo htmlspecialchars($cell); ?></td><?php endforeach; ?></tr>
<?php endforeach; ?>
      </table>
      <p><a href="index.html">Back to website</a></p>
    </section>
  </main>
</body>
</html>

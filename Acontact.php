<?php
require_once "includes/auth.php";
require_admin();
include "includes/connect_db.php";

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'], $_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        $errorMessage = 'Invalid message ID.';
    } elseif ($_GET['action'] === 'read') {
        $stmt = $db->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $successMessage = 'Message marked as read.';
    } elseif ($_GET['action'] === 'delete') {
        $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $successMessage = $stmt->rowCount() === 1 ? 'Message deleted successfully.' : 'Message not found.';
    }
}

$messages = $db->query("SELECT * FROM contact_messages ORDER BY is_read ASC, id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Market Books - Contact Messages</title>
  <link rel="stylesheet" href="css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/all.min.css" />
  <link rel="stylesheet" href="css/boot.css" />
</head>
<body>
<?php include "includes/adminHeader.php"; ?>

<?php if ($successMessage !== ''): ?><div class="alert alert-success" role="alert"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
<?php if ($errorMessage !== ''): ?><div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>

<div class="project text-center pt-5 pb-5">
  <h4 class="text-black fw-bold">Contact Messages</h4>
  <i class="fa-solid fa-envelope fs-1"></i>
</div>

<div class="container-fluid mt-4">
  <div class="table-responsive">
    <table class="table table-bordered text-center align-middle">
      <thead class="table-info">
        <tr>
          <th>Id</th>
          <th>Status</th>
          <th>Name</th>
          <th>Phone</th>
          <th>Email</th>
          <th>Subject</th>
          <th>Message</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($messages as $msg): ?>
        <tr>
          <td><?php echo (int)$msg['id']; ?></td>
          <td>
            <?php if ((int)$msg['is_read'] === 1): ?>
              <span class="badge bg-success">Read</span>
            <?php else: ?>
              <span class="badge bg-warning text-dark">New</span>
            <?php endif; ?>
          </td>
          <td><?php echo htmlspecialchars($msg['name'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars($msg['phone'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars($msg['email'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars($msg['subject'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td class="text-start" style="min-width:250px; white-space:pre-wrap;"><?php echo htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars($msg['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td>
            <?php if ((int)$msg['is_read'] === 0): ?>
              <a href="Acontact.php?action=read&id=<?php echo (int)$msg['id']; ?>" class="btn btn-success btn-sm">Mark Read</a>
            <?php endif; ?>
            <a href="Acontact.php?action=delete&id=<?php echo (int)$msg['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this message?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($messages)): ?><tr><td colspan="9">No contact messages yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/all.min.js"></script>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html>

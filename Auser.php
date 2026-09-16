<?php
require_once "includes/auth.php";
require_admin();
include "includes/connect_db.php";

$errors = [];
$id = 0;
$name = '';
$email = '';
$birthdate = '';
$username = '';
$password = '';
$targetFile = '';
$role = 'User';
$status = 'active';
$successMessage = '';

function uploadUserImage(string $oldFile = ''): string
{
    if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        return $oldFile;
    }

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Unable to upload the selected image.');
    }

    if ($_FILES['file']['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('The image must be 5 MB or smaller.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
    finfo_close($finfo);

    $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif'
    ];

    if (!isset($allowedMime[$mime])) {
        throw new RuntimeException('Only JPG, JPEG, PNG and GIF images are allowed.');
    }

    $targetDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
        throw new RuntimeException('Upload directory is not available.');
    }

    $fileName = bin2hex(random_bytes(16)) . '.' . $allowedMime[$mime];
    $absolutePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
    $relativePath = 'uploads/' . $fileName;

    if (!move_uploaded_file($_FILES['file']['tmp_name'], $absolutePath)) {
        throw new RuntimeException('Error uploading file.');
    }

    if ($oldFile !== '' && strpos($oldFile, 'uploads/') === 0) {
        $oldAbsolute = __DIR__ . DIRECTORY_SEPARATOR . $oldFile;
        if (is_file($oldAbsolute)) {
            @unlink($oldAbsolute);
        }
    }

    return $relativePath;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'], $_GET['id'])) {
    $action = $_GET['action'];
    $targetId = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($targetId === false) {
        $errors['general'] = 'Invalid user ID.';
    } elseif ($action === 'activate' || $action === 'deactivate') {
        if ($action === 'deactivate' && $targetId === (int)($_SESSION['id'] ?? 0)) {
            $errors['general'] = 'You cannot deactivate your own admin account.';
        } else {
            $newStatus = $action === 'activate' ? 'active' : 'pending';
            $stmt = $db->prepare("UPDATE user SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $newStatus, ':id' => $targetId]);
            $successMessage = $newStatus === 'active' ? 'Account activated successfully.' : 'Account deactivated successfully.';
        }
    } elseif ($action === 'edit') {
        $stmt = $db->prepare("SELECT * FROM user WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $targetId]);
        $user = $stmt->fetch();
        if ($user) {
            $id = (int)$user['id'];
            $name = $user['name'];
            $email = $user['email'];
            $birthdate = $user['birthdate'];
            $username = $user['userName'];
            $targetFile = $user['img'];
            $role = $user['role'];
            $status = $user['status'] ?? 'active';
        } else {
            $errors['general'] = 'User not found.';
        }
    } elseif ($action === 'delete') {
        $stmt = $db->prepare("SELECT img FROM user WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $targetId]);
        $user = $stmt->fetch();

        if (!$user) {
            $errors['general'] = 'User not found.';
        } elseif ($targetId === (int)($_SESSION['id'] ?? 0)) {
            $errors['general'] = 'You cannot delete your own admin account.';
        } else {
            $stmt = $db->prepare("DELETE FROM user WHERE id = :id");
            try {
                $stmt->execute([':id' => $targetId]);
                if ($stmt->rowCount() > 0) {
                    if (!empty($user['img']) && strpos($user['img'], 'uploads/') === 0) {
                        $imagePath = __DIR__ . DIRECTORY_SEPARATOR . $user['img'];
                        if (is_file($imagePath)) {
                            @unlink($imagePath);
                        }
                    }
                    $successMessage = 'User deleted successfully.';
                } else {
                    $errors['general'] = 'User could not be deleted.';
                }
            } catch (PDOException $e) {
                $errors['general'] = 'This user cannot be deleted because related records exist.';
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['my_form'])) {
    $postedId = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
    $id = $postedId && $postedId > 0 ? $postedId : 0;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $birthdate = trim($_POST['birth_date'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = ($_POST['role'] ?? 'User') === 'admin' ? 'admin' : 'User';
    $status = ($_POST['status'] ?? 'active') === 'pending' ? 'pending' : 'active';

    $oldImage = '';
    $oldPassword = '';

    if ($id > 0) {
        $stmt = $db->prepare("SELECT password, img FROM user WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $existing = $stmt->fetch();
        if (!$existing) {
            $errors['general'] = 'User not found.';
        } else {
            $oldPassword = $existing['password'];
            $oldImage = $existing['img'];
            $targetFile = $oldImage;
        }
    }

    if ($name === '') {
        $errors['name_err'] = 'Name is Required';
    } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
        $errors['name_err'] = 'Only characters and spaces are allowed.';
    }

    if ($email === '') {
        $errors['email_err'] = 'Email is Required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email_err'] = 'Invalid Email Format';
    }

    if ($birthdate === '') {
        $errors['birthdate_err'] = 'Birth Date is Required';
    } elseif ($birthdate > date('Y-m-d')) {
        $errors['birthdate_err'] = 'Birth date cannot be in the future.';
    }

    if ($username === '') {
        $errors['username_err'] = 'Username is Required';
    }

    if ($id === 0 && trim($password) === '') {
        $errors['password_err'] = 'Password is Required';
    }

    if ($username !== '') {
        $stmt = $db->prepare("SELECT id FROM user WHERE userName = :username AND id <> :id LIMIT 1");
        $stmt->execute([':username' => $username, ':id' => $id]);
        if ($stmt->fetch()) {
            $errors['username_err'] = 'Username is already in use.';
        }
    }

    try {
        if (empty($errors)) {
            if ($id > 0) {
                if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $targetFile = uploadUserImage($oldImage);
                }

                $passwordHash = $oldPassword;
                if (trim($password) !== '') {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                }

                // Do not allow changing the currently logged-in admin into an ordinary user.
                if ($id === (int)$_SESSION['id']) {
                    $role = 'admin';
                    $status = 'active';
                }

                $stmt = $db->prepare(
                    "UPDATE user SET name=:name, email=:email, birthdate=:birthdate, userName=:username,
                     password=:password, img=:img, role=:role, status=:status WHERE id=:id"
                );
                $stmt->execute([
                    ':id' => $id,
                    ':name' => $name,
                    ':email' => $email,
                    ':birthdate' => $birthdate,
                    ':username' => $username,
                    ':password' => $passwordHash,
                    ':img' => $targetFile,
                    ':role' => $role,
                    ':status' => $status
                ]);

                if ($id === (int)$_SESSION['id']) {
                    $_SESSION['name'] = $name;
                    $_SESSION['email'] = $email;
                    $_SESSION['birthdate'] = $birthdate;
                    $_SESSION['userName'] = $username;
                    $_SESSION['img'] = $targetFile;
                    $_SESSION['role'] = 'admin';
                    $_SESSION['status'] = 'active';
                }

                $successMessage = 'User updated successfully.';
            } else {
                if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
                    $errors['file_err'] = 'File is Required';
                } else {
                    $targetFile = uploadUserImage('');
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = $db->prepare(
                        "INSERT INTO user (name, email, birthdate, userName, password, img, role, status)
                         VALUES (:name, :email, :birthdate, :username, :password, :img, :role, :status)"
                    );
                    $stmt->execute([
                        ':name' => $name,
                        ':email' => $email,
                        ':birthdate' => $birthdate,
                        ':username' => $username,
                        ':password' => $passwordHash,
                        ':img' => $targetFile,
                        ':role' => $role,
                        ':status' => $status
                    ]);
                    $successMessage = 'User created successfully.';
                    $id = 0;
                    $name = '';
                    $email = '';
                    $birthdate = '';
                    $username = '';
                    $password = '';
                    $targetFile = '';
                    $role = 'User';
                    $status = 'active';
                }
            }
        }
    } catch (Throwable $e) {
        if ($targetFile !== '' && $id === 0 && strpos($targetFile, 'uploads/') === 0) {
            @unlink(__DIR__ . DIRECTORY_SEPARATOR . $targetFile);
        }
        $errors['general'] = 'Unable to save the user right now.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Market Books - Users</title>
  <link rel="stylesheet" href="css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/all.min.css" />
  <link rel="stylesheet" href="css/boot.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,500;1,100&display=swap" rel="stylesheet" />
</head>
<body>
<?php include "includes/adminHeader.php"; ?>

<?php if (!empty($successMessage)): ?>
  <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>
<?php if (isset($errors['general'])): ?>
  <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<div class="project text-center pt-5 pb-5">
  <h4 class="text-black fw-bold">Management of Users</h4>
  <i class="fa-solid fa-users fs-1"></i>
</div>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="multipart/form-data">
  <fieldset class="border p-3 project">
    <div class="row">
      <div class="col mb-2">
        <label>Name:</label>
        <input class="form-control" type="hidden" name="id" value="<?php echo (int)$id; ?>">
        <input class="form-control" type="text" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
        <span class="text-danger"><?php echo htmlspecialchars($errors['name_err'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
      <div class="col mb-2">
        <label>E-mail:</label>
        <input class="form-control" type="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
        <span class="text-danger"><?php echo htmlspecialchars($errors['email_err'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
      <div class="col mb-2">
        <label>Birth date:</label>
        <input class="form-control" type="date" name="birth_date" value="<?php echo htmlspecialchars($birthdate, ENT_QUOTES, 'UTF-8'); ?>">
        <span class="text-danger"><?php echo htmlspecialchars($errors['birthdate_err'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
    </div>

    <div class="row mb-2">
      <div class="col">
        <label>Username:</label>
        <input class="form-control" type="text" name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
        <span class="text-danger"><?php echo htmlspecialchars($errors['username_err'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
      <div class="col">
        <label>Password <?php echo $id > 0 ? '(leave blank to keep current)' : ''; ?>:</label>
        <input class="form-control" type="password" name="password" value="">
        <span class="text-danger"><?php echo htmlspecialchars($errors['password_err'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col">
        <label>Photo:</label>
        <input class="form-control" type="file" name="file" accept="image/jpeg,image/png,image/gif">
        <span class="text-danger"><?php echo htmlspecialchars($errors['file_err'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
      <div class="col">
        <label>Role:</label>
        <select class="form-control" name="role">
          <option value="User" <?php echo $role === 'User' ? 'selected' : ''; ?>>User</option>
          <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
        </select>
      </div>
      <div class="col">
        <label>Status:</label>
        <select class="form-control" name="status">
          <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
          <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
        </select>
      </div>
    </div>

    <div class="row position-relative">
      <div class="col">
        <input type="submit" value="Save" class="btn btn-primary w-25 position-absolute bottom-0 start-50 translate-middle-x" name="my_form">
      </div>
    </div>
  </fieldset>
</form>

<div class="container-fluid mt-5">
  <hr>
  <div class="table-responsive">
    <table class="table table-bordered text-center align-middle">
      <thead class="table-info">
        <tr>
          <th>Id</th>
          <th>Photo</th>
          <th>Name</th>
          <th>Email</th>
          <th>Birth date</th>
          <th>UserName</th>
          <th>Role</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $stmt = $db->query("SELECT * FROM user ORDER BY id DESC");
      $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($users as $user):
          $userStatus = $user['status'] ?? 'active';
      ?>
        <tr>
          <td><?php echo (int)$user['id']; ?></td>
          <td><img width="60" height="60" style="object-fit:cover" src="<?php echo htmlspecialchars($user['img'], ENT_QUOTES, 'UTF-8'); ?>" alt="User photo"></td>
          <td><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars($user['birthdate'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars($user['userName'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td>
            <?php if ($userStatus === 'active'): ?>
              <span class="badge bg-success">Active</span>
            <?php else: ?>
              <span class="badge bg-warning text-dark">Pending</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="Auser.php?action=edit&id=<?php echo (int)$user['id']; ?>" class="btn btn-info btn-sm">Edit</a>
            <?php if ($userStatus === 'pending'): ?>
              <a href="Auser.php?action=activate&id=<?php echo (int)$user['id']; ?>" class="btn btn-success btn-sm">Activate</a>
            <?php elseif ((int)$user['id'] !== (int)$_SESSION['id']): ?>
              <a href="Auser.php?action=deactivate&id=<?php echo (int)$user['id']; ?>" class="btn btn-warning btn-sm">Deactivate</a>
            <?php endif; ?>
            <?php if ((int)$user['id'] !== (int)$_SESSION['id']): ?>
              <a href="Auser.php?action=delete&id=<?php echo (int)$user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?');">Delete</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/all.min.js"></script>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html>

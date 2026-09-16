<?php
session_start();
include "includes/connect_db.php";

$errors = [];
$id = '';
$name = '';
$email = '';
$birthdate = '';
$userName = '';
$targetFile = '';
$isEdit = false;
$successMessage = '';

function clean(string $value): string
{
    return trim($value);
}

function uploadProfileImage(string $oldFile = ''): string
{
    if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        return $oldFile;
    }

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Unable to upload the selected image.');
    }

    $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowedMime[$mime])) {
        throw new RuntimeException('Only JPG, JPEG, PNG and GIF images are allowed.');
    }

    if ($_FILES['file']['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('The image must be 5 MB or smaller.');
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

    if ($oldFile !== '' && strpos($oldFile, 'uploads/') === 0 && is_file(__DIR__ . DIRECTORY_SEPARATOR . $oldFile)) {
        @unlink(__DIR__ . DIRECTORY_SEPARATOR . $oldFile);
    }

    return $relativePath;
}

// Load the logged-in user's profile for the Edit Profile action.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_profile'])) {
    if (empty($_SESSION['id'])) {
        header('Location: index.php');
        exit;
    }

    $id = (int)$_SESSION['id'];
    $stmt = $db->prepare("SELECT * FROM user WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch();

    if (!$user) {
        $errors['general'] = 'User not found.';
    } else {
        $isEdit = true;
        $name = $user['name'];
        $email = $user['email'];
        $birthdate = $user['birthdate'];
        $userName = $user['userName'];
        $targetFile = $user['img'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup_form'])) {
    $postedId = (int)($_POST['id'] ?? 0);
    $isEdit = $postedId > 0;

    if ($isEdit) {
        if (empty($_SESSION['id']) || $postedId !== (int)$_SESSION['id']) {
            http_response_code(403);
            exit('Access denied.');
        }
        $id = $postedId;
    }

    $name = clean($_POST['name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $birthdate = clean($_POST['birthdate'] ?? '');
    $userName = clean($_POST['userName'] ?? '');
    $plainPassword = $_POST['password'] ?? '';
    $targetFile = '';

    $oldPasswordHash = '';
    $oldImage = '';
    $currentRole = 'User';
    $currentStatus = 'pending';

    if ($isEdit) {
        $stmt = $db->prepare("SELECT * FROM user WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $existingUser = $stmt->fetch();
        if (!$existingUser) {
            $errors['general'] = 'User not found.';
        } else {
            $oldPasswordHash = $existingUser['password'];
            $oldImage = $existingUser['img'];
            $targetFile = $oldImage;
            $currentRole = $existingUser['role'];
            $currentStatus = $existingUser['status'] ?? 'active';
        }
    }

    if ($name === '') {
        $errors['name_err'] = 'Name is Required';
    } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
        $errors['name_err'] = 'only char and white space allowed';
    }

    if ($email === '') {
        $errors['email_err'] = 'Email is Required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email_err'] = 'invaild format';
    }

    if ($birthdate === '') {
        $errors['birthdate_err'] = 'birthdate is Required';
    } elseif ($birthdate > date('Y-m-d')) {
        $errors['birthdate_err'] = 'Invaild Format Birth Date';
    }

    if ($userName === '') {
        $errors['userName_err'] = 'userName is Required';
    }

    if (!$isEdit && trim($plainPassword) === '') {
        $errors['password_err'] = 'password is Required';
    }

    if (!isset($errors['general']) && $userName !== '') {
        $stmt = $db->prepare("SELECT id FROM user WHERE userName = :userName AND id <> :id LIMIT 1");
        $stmt->execute([
            ':userName' => $userName,
            ':id' => $isEdit ? $id : 0
        ]);
        if ($stmt->fetch()) {
            $errors['userName_err'] = 'The username is already in use. Change it and try again.';
        }
    }

    try {
        if (empty($errors)) {
            if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $targetFile = uploadProfileImage($isEdit ? $oldImage : '');
            } elseif (!$isEdit) {
                $errors['file_err'] = 'file is Required';
            }

            if (empty($errors)) {
                if ($isEdit) {
                    $passwordHash = $oldPasswordHash;
                    if (trim($plainPassword) !== '') {
                        $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);
                    }

                    $stmt = $db->prepare(
                        "UPDATE user SET name=:name, email=:email, birthdate=:birthdate, userName=:userName,
                         password=:password, img=:img WHERE id=:id"
                    );
                    $stmt->execute([
                        ':id' => $id,
                        ':name' => $name,
                        ':email' => $email,
                        ':birthdate' => $birthdate,
                        ':userName' => $userName,
                        ':password' => $passwordHash,
                        ':img' => $targetFile
                    ]);

                    $_SESSION['name'] = $name;
                    $_SESSION['email'] = $email;
                    $_SESSION['birthdate'] = $birthdate;
                    $_SESSION['userName'] = $userName;
                    $_SESSION['img'] = $targetFile;

                    $successMessage = 'Updated successfully!';
                    $isEdit = true;
                } else {
                    $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);

                    $stmt = $db->prepare(
                        "INSERT INTO user (name, email, birthdate, userName, password, img, role, status)
                         VALUES (:name, :email, :birthdate, :userName, :password, :img, 'User', 'pending')"
                    );
                    $stmt->execute([
                        ':name' => $name,
                        ':email' => $email,
                        ':birthdate' => $birthdate,
                        ':userName' => $userName,
                        ':password' => $passwordHash,
                        ':img' => $targetFile
                    ]);

                    $successMessage = 'Registration submitted successfully! Your account is waiting for admin approval.';
                    $id = '';
                    $name = '';
                    $email = '';
                    $birthdate = '';
                    $userName = '';
                    $targetFile = '';
                }
            }
        }
    } catch (Throwable $e) {
        if ($targetFile !== '' && !$isEdit && strpos($targetFile, 'uploads/') === 0) {
            @unlink(__DIR__ . DIRECTORY_SEPARATOR . $targetFile);
        }
        $errors['general'] = 'Unable to save the account right now.';
    }
}
?>

<html>

<head>
<title>Market Books</title>
  <link rel="stylesheet" href="css/bank.css" />
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<?php if ($successMessage !== ''): ?>
  <div class="alert alert-success" role="alert">
    <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
    <?php if (!$isEdit): ?>
      <a href="index.php">Back to sign in</a>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php if (isset($errors['general'])): ?>
  <div class="alert alert-danger" role="alert">
    <?php echo htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?>
  </div>
<?php endif; ?>

  <div class="container">
    <div class="col-lg-7">
    <form class="form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="post"
      enctype="multipart/form-data">
      <p class="title"><?php echo $isEdit ? 'Edit Profile' : 'Sign up'; ?></p>
      <p class="message"><?php echo $isEdit ? 'Update your account information.' : 'Signup now and get full access to our app.'; ?></p>

      <label>
        <span>Name</span>
        <input class="form-control text-center" type="text" hidden name="id"
          value="<?php echo (int)$id; ?>">
        <input class="form-control text-center" type="text" name="name"
          value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
      </label>
      <span class="text-danger">
        <?php echo isset($errors['name_err']) ? htmlspecialchars($errors['name_err'], ENT_QUOTES, 'UTF-8') : ''; ?>
      </span>

      <label>
        <span>Email</span>
        <input class="form-control text-center" type="text" name="email"
          value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
      </label>
      <span class="text-danger">
        <?php echo isset($errors['email_err']) ? htmlspecialchars($errors['email_err'], ENT_QUOTES, 'UTF-8') : ''; ?>
      </span>

      <label>
        <span>Birth Date</span>
        <input class="form-control text-center" type="date" name="birthdate"
          value="<?php echo htmlspecialchars($birthdate, ENT_QUOTES, 'UTF-8'); ?>">
      </label>
      <span class="text-danger">
        <?php echo isset($errors['birthdate_err']) ? htmlspecialchars($errors['birthdate_err'], ENT_QUOTES, 'UTF-8') : ''; ?>
      </span>

      <label>
        <span>userName</span>
        <input class="form-control text-center" id="userName" type="text" name="userName"
          value="<?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?>">
      </label>
      <span class="text-danger">
        <?php echo isset($errors['userName_err']) ? htmlspecialchars($errors['userName_err'], ENT_QUOTES, 'UTF-8') : ''; ?>
      </span>

      <label>
        <span>Password <?php echo $isEdit ? '(leave blank to keep current password)' : ''; ?></span>
        <input id="password" class="form-control text-center" type="password" name="password" value="">
      </label>
      <span class="text-danger">
        <?php echo isset($errors['password_err']) ? htmlspecialchars($errors['password_err'], ENT_QUOTES, 'UTF-8') : ''; ?>
      </span>

      <label>
        <span>Photo</span>
        <input class="form-control" type="file" name="file" accept="image/jpeg,image/png,image/gif" />
      </label>
      <span class="text-danger">
        <?php echo isset($errors['file_err']) ? htmlspecialchars($errors['file_err'], ENT_QUOTES, 'UTF-8') : ''; ?>
      </span>

      <input class="w-50 position-absolute top-100 start-50 translate-middle-x" id="button" type="submit"
        value="Save" name="signup_form">

      <p class="mt-5 position-absolute top-100 start-50 translate-middle-x">
        <?php echo $isEdit ? '<a href="home.php">Back to Home</a>' : 'Already have an acount ? <a href="index.php">Signin</a>'; ?>
      </p>

    </form>
    </div>
  </div>

</body>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>

</html>

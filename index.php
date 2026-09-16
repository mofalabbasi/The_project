<?php
session_start();
include "includes/connect_db.php";
$errors = [];
$loginMessage = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["login_form"])) {
    $userName = trim($_POST['userName'] ?? '');
    $plainPassword = $_POST['password'] ?? '';

    if ($userName === '') {
        $errors["userName_err"] = "userName is Required";
    }
    if ($plainPassword === '') {
        $errors["password_err"] = "Password is Required";
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("SELECT * FROM user WHERE userName = :userName LIMIT 1");
            $stmt->execute([':userName' => $userName]);
            $row = $stmt->fetch();

            if (!$row) {
                $loginMessage = "The login information is incorrect. Please try again.";
            } else {
                $status = $row['status'] ?? 'active';

                if ($status !== 'active') {
                    $loginMessage = "Your account is waiting for admin approval.";
                } else {
                    $validPassword = password_verify($plainPassword, $row['password']);

                    // Upgrade existing SHA1 passwords the first time the user logs in.
                    if (!$validPassword && hash_equals($row['password'], sha1($plainPassword))) {
                        $newHash = password_hash($plainPassword, PASSWORD_DEFAULT);
                        $update = $db->prepare("UPDATE user SET password = :password WHERE id = :id");
                        $update->execute([
                            ':password' => $newHash,
                            ':id' => $row['id']
                        ]);
                        $validPassword = true;
                    }

                    if ($validPassword) {
                        session_regenerate_id(true);

                        $_SESSION['id'] = $row['id'];
                        $_SESSION['name'] = $row['name'];
                        $_SESSION['email'] = $row['email'];
                        $_SESSION['birthdate'] = $row['birthdate'];
                        $_SESSION['userName'] = $row['userName'];
                        $_SESSION['img'] = $row['img'];
                        $_SESSION['role'] = $row['role'];
                        $_SESSION['status'] = $status;

                        if ($row['role'] === 'admin') {
                            header('Location: Abook.php');
                        } else {
                            header('Location: home.php');
                        }
                        exit;
                    }

                    $loginMessage = "The login information is incorrect. Please try again.";
                }
            }
        } catch (PDOException $e) {
            $loginMessage = "Unable to process login right now.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>login</title>
  <link rel="stylesheet" href="css/bank.css" />
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <?php if ($loginMessage !== ''): ?>
    <div class="alert alert-danger" role="alert">
      <?php echo htmlspecialchars($loginMessage, ENT_QUOTES, 'UTF-8'); ?>
    </div>
  <?php endif; ?>

  <div class="container">
    <div class="row">
      <div class="col-lg-7">
        <div class="row mt-5">
          <img class="imgg img-fluid w-100 " src="img/111.jpg" alt="" />
        </div>
        <div class="row mt-2 mb-3">
          <img class="imgg img-fluid w-100 " src="img/2.jpg" alt="" />
        </div>
      </div>

      <div class="col-lg-5 d-flex justify-content-center align-items-center">
        <form class="form_main" action="" method="post">
          <p class="heading">Login</p>
          <div class="inputContainer">
            <svg viewBox="0 0 16 16" fill="#2e2e2e" height="16" width="16" xmlns="http://www.w3.org/2000/svg"
              class="inputIcon">
              <path
                d="M13.106 7.222c0-2.967-2.249-5.032-5.482-5.032-3.35 0-5.646 2.318-5.646 5.702 0 3.493 2.235 5.708 5.762 5.708.862 0 1.689-.123 2.304-.335v-.862c-.43.199-1.354.328-2.29.328-2.926 0-4.813-1.88-4.813-4.798 0-2.844 1.921-4.881 4.594-4.881 2.735 0 4.608 1.688 4.608 4.156 0 1.682-.554 2.769-1.416 2.769-.492 0-.772-.28-.772-.76V5.206H8.923v.834h-.11c-.266-.595-.881-.964-1.6-.964-1.4 0-2.378 1.162-2.378 2.823 0 1.737.957 2.906 2.379 2.906.8 0 1.415-.39 1.709-1.087h.11c.081.67.703 1.148 1.503 1.148 1.572 0 2.57-1.415 2.57-3.643zm-7.177.704c0-1.197.54-1.907 1.456-1.907.93 0 1.524.738 1.524 1.907S8.308 9.84 7.371 9.84c-.895 0-1.442-.725-1.442-1.914z">
              </path>
            </svg>
            <input class="inputField" placeholder="userName" id="userName" type="text" name="userName"
              value="<?php echo htmlspecialchars($userName ?? '', ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <span class="text-danger">
            <?php echo isset($errors["userName_err"]) ? htmlspecialchars($errors["userName_err"], ENT_QUOTES, 'UTF-8') : ""; ?>
          </span>

          <div class="inputContainer">
            <svg viewBox="0 0 16 16" fill="#2e2e2e" height="16" width="16" xmlns="http://www.w3.org/2000/svg"
              class="inputIcon">
              <path
                d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z">
              </path>
            </svg>
            <input placeholder="Password" id="password" class="inputField" type="password" name="password" value="">
          </div>
          <span class="text-danger">
            <?php echo isset($errors["password_err"]) ? htmlspecialchars($errors["password_err"], ENT_QUOTES, 'UTF-8') : ""; ?>
          </span>

          <input id="button" type="submit" value="Login" name="login_form">
          <div class="signupContainer">
            <p>Don't have any account?</p>
            <a href="sign up.php">Sign up</a>
          </div>
        </form>
      </div>
    </div>
  </div>

</body>

<script src="bootstrap/dist/js/bootstrap.min.js"></script>

</html>

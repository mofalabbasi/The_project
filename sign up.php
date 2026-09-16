<?php
session_start();
?>
<html>

<head>
<title>Market Books</title>
  <link rel="stylesheet" href="css/bank.css" />
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- <link rel="stylesheet" href="css/boot.css" /> -->
</head>

<body>


  <?php
  include "includes/connect_db.php";
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["signup_form"])) {
      $name = $_POST["name"];
      $email = $_POST["email"];
      $birthdate = $_POST['birthdate'];
      $userName = $_POST["userName"];
      $password = $_POST['password'];
      $targetFile = $_POST['per_file'];
      $role = $_POST["select"];



      if (empty(trim($name))) {
        $errors["name_err"] = "Name is Required";
      } else {
        if (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
          $errors["name_err"] = "only char and white space allowed";
        }
      }
      if (empty(trim($email))) {
        $errors["email_err"] = "Email is Required";
      } else {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $errors["email_err"] = "invaild format";
        }
      }
      if (empty(trim($birthdate))) {
        $errors["birthdate_err"] = "birthdate is Required";
      }else {
        if ( $birthdate > date("Y-m-d")) {
          $errors["birthdate_err"] = "Invaild Format Birth Date";
        }
      }
      if (empty(trim($userName))) {
        $errors["userName_err"] = "userName is Required";
      }
      if (empty(trim($password))) {
        $errors["password_err"] = "password is Required";
      }
      if (isset($_FILES['file']) && (!empty($_FILES['file']["name"]) || isset($targetFile))) {
        if (!isset($targetFile)) {
          $targetDir = 'uploads/';
          $targetFile = $targetDir . basename($_FILES['file']['name']); // Path of the target file // Check if file is a valid upload 
          $fileExtension = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
          $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
          if (!in_array($fileExtension, $allowedExtensions)) {
            $errors["file_err"] = "'jpg', 'jpeg', 'png', 'gif' the allowed extentions";
          }
        }
      } else {
        $errors["file_err"] = "file is Required";
      }

      if (!isset($errors)) {
        if (!empty($_FILES['file']["name"])) {

          if (isset($targetFile)) {
            if (file_exists($targetFile)) {
              if (!unlink($targetFile)) {
                echo "<div class='alert alert-danger'role='alert'>Image not deleted.</div>";
              }
            }
          }
          $targetDir = 'uploads/'; // Directory where the file will be uploaded 
          $targetFile = $targetDir . basename($_FILES['file']['name']); // Path of the target file // Check if file is a valid upload 
          $fileExtension = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
          // Define allowed file extensions 
          $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
          if (in_array($fileExtension, $allowedExtensions)) {
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
              echo "Error uploading file.";
            }
          } else {
            echo "'jpg', 'jpeg', 'png', 'gif' the allowed extentions";
          }
        }


        $stmt = $db->prepare("SELECT  userName FROM user WHERE userName = :userName");
        $stmt->bindParam(':userName', $userName);
        $stmt->execute();
        if ($stmt->rowCount() == 1) {
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
          echo "<div class='alert alert-danger' role='alert'>The username (" . $userName . ") is invalid, change it and try again.</div>";
        } else {
          $password = sha1($_POST['password']);

          if (is_numeric($_POST["id"]) && $_POST["id"] > 0) {
            $stmt = $db->prepare("UPDATE user SET name = :name, email = :email, birthdate = :birthdate, userName = :userName, password = :password, img = :img , role = :role WHERE id = :id");
            $stmt->bindParam(':id', $_POST['id']);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':birthdate', $birthdate);
            $stmt->bindParam(':userName', $userName);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':img', $targetFile);
            $stmt->bindParam(':role', $role);
            $stmt->execute();
            $rowCount = $stmt->rowCount();
            if ($rowCount > 0) {
              echo "<div class='alert alert-success'role='alert'>Updated successfully !</div>";
              $id = "";
              $name = "";
              $email = "";
              $birthdate = "";
              $userName = "";
              $password = "";
            } else {
              echo "<div class='alert alert-danger'role='alert'>User not found or could not be updated.</div>";
            }

          } else {
            $stmt = $db->prepare("INSERT INTO user (name, email, birthdate, userName, password, img,role) VALUES (:name, :email, :birthdate, :userName, :password, :img, :role)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':birthdate', $birthdate);
            $stmt->bindParam(':userName', $userName);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':img', $targetFile);
            $stmt->bindParam(':role', $role);
            $stmt->execute();
            echo "<div class='alert alert-success' role='alert'>Registration successful! You can now <a href='index.php'>sign in</a>.</div>";
            $id = "";
            $name = "";
            $email = "";
            $birthdate = "";
            $userName = "";
            $password = "";
          }
        }
      }
    } else {
      if (isset($_POST["edit_profile"])) {
        $id = $_POST["id"];
        $stmt = $db->prepare("SELECT * FROM user WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
          $id = $user['id'];
          $name = $user['name'];
          $email = $user['email'];
          $birthdate = $user['birthdate'];
          $userName = $user['userName'];
          $targetFile = $user['img'];

        } else {
          echo "<div class='alert alert-danger'role='alert'>User not found.</div>";
        }
      }
    }
  }

  ?>





  <div class="container">
    <div class="col-lg-7">
    <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
      enctype="multipart/form-data">
      <p class="title">Sign up</p>
      <p class="message">Signup now and get full access to our app.</p>

      <label>
        <span>Name</span>
        <input class="form-control text-center" type="text" hidden name="id"
          value="<?php echo isset($id) ? $id : ""; ?>">
        <input class="form-control text-center"  type="text" name="name"
          value="<?php echo isset($name) ? $name : ""; ?>">
      </label>
      <span class="text-danger">
        <?php echo isset($errors["name_err"]) ? $errors["name_err"] : ""; ?>
      </span>

      <label>
        <span>Email</span>
        <input class="form-control text-center"  type="text" name="email"
          value="<?php echo isset($email) ? $email : ""; ?>">
      </label>
      <span class="text-danger">
        <?php echo isset($errors["email_err"]) ? $errors["email_err"] : ""; ?>
      </span>

      <label>
        <span>Birth Date</span>
        <input class="form-control text-center"  type="date" name="birthdate"
          value="<?php echo isset($birthdate) ? $birthdate : ""; ?>">
      </label>
      <span class="text-danger">
        <?php echo isset($errors["birthdate_err"]) ? $errors["birthdate_err"] : ""; ?>
      </span>

      <label>
        <span>userName</span>
        <input class="form-control text-center"  id="userName" type="text" name="userName"
          value="<?php echo isset($userName) ? $userName : ""; ?>">
      </label>
      <span class="text-danger">
        <?php echo isset($errors["userName_err"]) ? $errors["userName_err"] : ""; ?>
      </span>

      <label>
        <span>Password</span>
        <input  id="password" class="form-control text-center" type="password" name="password"
          value="<?php echo isset($password) ? $password : ""; ?>">
      </label>
      <span class="text-danger">
        <?php echo isset($errors["password_err"]) ? $errors["password_err"] : ""; ?>
      </span>

      <label>
        <span>Photo</span>
        <input class="form-control" hidden name="per_file"
          value="<?php echo isset($targetFile) ? $targetFile : ""; ?>" />
        <input class="form-control" type="file" name="file" />
      </label>
      <span class="text-danger">
        <?php echo isset($errors["file_err"]) ? $errors["file_err"] : ""; ?>
      </span>


      <label>
        <select name="select" hidden>
          <option value="User">User</option>
        </select>
        <span hidden>Role</span>
      </label>

      <input class="w-50 position-absolute top-100 start-50 translate-middle-x" id="button" type="submit"
        value="Save" name="signup_form">
        
      <p class="mt-5 position-absolute top-100 start-50 translate-middle-x">
        Already have an acount ? <a href="index.php">Signin</a>
      </p>

    </form>
    </div>
  </div>

</body>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>

</html>
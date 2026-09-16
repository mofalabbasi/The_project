<?php
session_start();
if(!isset($_SESSION['id']))
{
  header('refresh:0;index.php');
}
?>
<html>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Market Books</title>
  <link rel="stylesheet" href="css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/all.min.css" />
  <link rel="stylesheet" href="css/boot.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,500;1,100&display=swap"
    rel="stylesheet" />
</head>


<body>


  <?php
  include "includes/connect_db.php";
  include "includes/adminHeader.php";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["my_form"])) {
      $name = $_POST["name"];
      $email = $_POST["email"];
      $birthdate = $_POST["birth_date"];
      $username = $_POST['username'];
      $password = $_POST['password'];
      $targetFile = $_POST["per_file"];
      $role = $_POST['role'];

      if (empty(trim($name))) {
        $errors["name_err"] = "Name is Required";
      } else {
        if (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
          $errors["name_err"] = "Only Char And White Space Allowed";
        }
      }
      if (empty(trim($email))) {
        $errors["email_err"] = "Email is Required";
      } else {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $errors["email_err"] = "Invaild Format Email";
        }
      }
      if (empty(trim($birthdate))) {
        $errors["birthdate_err"] = "Birth Date is Required";
      }else {
        if ( $birthdate > date("Y-m-d")) {
          $errors["birthdate_err"] = "Invaild Format Birth Date";
        }
      }

      if (empty(trim($username))) {
        $errors["username_err"] = "Username is Required";
      }


      if (empty(trim($password))) {
        $errors["password_err"] = "Password is Required";
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
        $errors["file_err"] = "File is Required";
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
        $password = sha1($_POST['password']);
        if (is_numeric($_POST["id"]) && $_POST["id"] > 0) {
          $stmt = $db->prepare("Update user set name=:name , email=:email, birthdate=:birthdate, username=:userName, password=:password, img=:img, role=:role where id =:id");
          $stmt->bindParam(':id', $_POST['id']);
          $stmt->bindParam(':name', $name);
          $stmt->bindParam(':email', $email);
          $stmt->bindParam(':birthdate', $birthdate);
          $stmt->bindParam(':userName', $username);
          $stmt->bindParam(':password', $password);
          $stmt->bindParam(':img', $targetFile);
          $stmt->bindParam(':role', $role);
          $stmt->execute();
          $rowCount = $stmt->rowCount();

          if ($rowCount > 0) {
            echo "<div class='alert alert-success'role='alert'>Updated successfully!</div>";
            $id = "";
            $name = "";
            $email = "";
            $birthdate = "";
            $username = "";
            $password = "";
            $img = "";

          } else {
            echo "<div class='alert alert-danger'role='alert'>User not found or could not be updated.</div>";
          }

        } else {

          $stmt = $db->prepare("INSERT INTO user(name, email, birthdate, username, password, img, role 	) VALUES (:name,:email,:birthdate,:userName,:password,:img,:role)");
          $stmt->bindParam(':name', $name);
          $stmt->bindParam(':email', $email);
          $stmt->bindParam(':birthdate', $birthdate);
          $stmt->bindParam(':userName', $username);
          $stmt->bindParam(':password', $password);
          $stmt->bindParam(':img', $targetFile);
          $stmt->bindParam(':role', $role);
          $stmt->execute();
          echo "<div class='alert alert-success'role='alert'>Registration successful!</div>";
          $name = "";
          $email = "";
          $birthdate = "";
          $username = "";
          $password = "";
          $targetFile = "";
        }
      }
    }
  } else {
    if (isset($_GET["id"]) && isset($_GET['action'])) {
      $id = $_GET['id'];
      if ($_GET['action'] == "edit") {
        $stmt = $db->prepare("select * FROM user WHERE id =:id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
          $id = $user['id'];
          $name = $user["name"];
          $email = $user["email"];
          $birthdate = $user["birthdate"];
          $username = $user["userName"];
          $targetFile = $user["img"];
          $role = $user["role"];
        } else {
          echo "<div class='alert alert-danger'role='alert'>User not found.</div>";
        }
      } else {
        $stmt = $db->prepare("SELECT * FROM user WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
          echo "<div class='alert alert-danger'role='alert'>User not found or could not be deleted.</div>";
        } else {
          $targetFile = $user['img'];
          if (isset($targetFile)) {
            if (file_exists($targetFile)) {
              if (unlink($targetFile)) {
                $stmt = $db->prepare("DELETE FROM user WHERE id = :id");
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                $rowCount = $stmt->rowCount();
                if ($rowCount > 0) {
                  echo "<div class='alert alert-success'role='alert'>User deleted successfully.</div>";
                } else {
                  echo "<div class='alert alert-danger'role='alert'>User not found or could not be deleted.</div>";
                }
              } else {
                echo "<div class='alert alert-danger'role='alert'>Image not deleted.</div>";
              }
            }
          }
        }
      }
    }
  }
  ?>

  <div class="project text-center pt-5 pb-5">
    <h4 class="text-black fw-bold"> Managment of Users </h4>
    <i class="fa-solid fa-users fs-1"></i>

  </div>
  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
    <fieldset class="border p-3 project">
      <div class="row">
        <div class="col m">
          <div class="form-group">
            <label for="name"> Name:</label>
            <input class="form-control" type="text" hidden name="id" value="<?php echo isset($id) ? $id : ""; ?>">
            <input class="form-control" type="text" name="name" value="<?php echo isset($name) ? $name : ""; ?>">
            <span class="text-danger">
              <?php echo isset($errors["name_err"]) ? $errors["name_err"] : ""; ?>
            </span>
          </div>
        </div>
        <div class="col mb-2">
          <div class="form-group">
            <label for="email"> E-mail:</label>
            <input class="form-control" type="email" name="email" value="<?php echo isset($email) ? $email : ""; ?>">
            <span class="text-danger">
              <?php echo isset($errors["email_err"]) ? $errors["email_err"] : ""; ?>
            </span>
          </div>
        </div>
        <div class="col mb-2">
          <div class="form-group">
            <label for="birth_date"> Birth date:</label>
            <input class="form-control" type="date" name="birth_date"
              value="<?php echo isset($birthdate) ? $birthdate : ""; ?>">
            <span class="text-danger">
              <?php echo isset($errors["birthdate_err"]) ? $errors["birthdate_err"] : ""; ?>
            </span>
          </div>
        </div>
        <div class="row mb-2">
          <div class="col">
            <div class="form-group">
              <label for="username">userName</label>
              <input class="form-control" type="text" name="username"
                value="<?php echo isset($username) ? $username : ""; ?>">
              <span class="text-danger">
                <?php echo isset($errors["username_err"]) ? $errors["username_err"] : ""; ?>
              </span>
            </div>
          </div>

          <div class="col">
            <div class="form-group">
              <label for="password">Password:</label>
              <input class="form-control" type="password" name="password"
                value="<?php echo isset($password) ? $password : ""; ?>">
              <span class="text-danger">
                <?php echo isset($errors["password_err"]) ? $errors["password_err"] : ""; ?>
              </span>
            </div>
          </div>
        </div>
        <div class="row mb-5">
          <div class="col">
            <div class="form-group">
              <label for="file">Photo:</label>
              <input class="form-control" hidden name="per_file"
                value="<?php echo isset($targetFile) ? $targetFile : ""; ?>" />
              <input class="form-control" type="file" name="file" />
              <span class="text-danger">
                <?php echo isset($errors["file_err"]) ? $errors["file_err"] : ""; ?>
              </span>
            </div>
          </div>

          <div class="col">
            <div class="form-group">
              <label for="role">Role</label>
              <select class="form-control" name="role">
                <option value="User">User</option>
                <option value="admin">Admin</option>
              </select>
            </div>
          </div>
        </div>
        <div class="row position-relative">
          <div class="col">
            <div class="pt-4 w-100">
              <input type="submit" value="Save"
                class="btn btn-primary w-25 position-absolute bottom-0 start-50 translate-middle-x" name="my_form">
            </div>
          </div>
        </div>
    </fieldset>
  </form>
  <div class="container-fluid">

    <hr>
    <table class="table table-bordered text-center">
      <thead class="table-info">
        <tr>
          <th>Id</th>
          <th>Photo</th>
          <th>Name</th>
          <th>Email</th>
          <th>Birth date</th>
          <th>UserName</th>
          <th>Role</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $stmt = $db->prepare("SELECT * FROM user");

        $stmt->execute();

        $Users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($Users as $user) {
          echo "<tr>";
          echo "<td>" . $user['id'] . "</td>";
          echo "<td> <img class='' width=60px src='" . $user['img'] . "' /> </td>";
          echo "<td>" . $user['name'] . "</td>";
          echo "<td>" . $user['email'] . "</td>";
          echo "<td>" . $user['birthdate'] . "</td>";
          echo "<td>" . $user['userName'] . "</td>";
          echo "<td>" . $user['role'] . "</td>";
          echo "<td> <a href='Auser.php?action=edit&&id=" . $user['id'] . "'class='btn btn-info'>Edit</a>";
          echo " <a href='Auser.php?action=delete&&id=" . $user['id'] . "'class='btn btn-danger ms-5'>delete</a></td>";
          echo "</tr>";
        }

        ?>
      </tbody>
    </table>
  </div>

  <script src="js/bootstrap.bundle.min.js"></script>
  <script src="js/all.min.js"></script>
  <script src="bootstrap/dist/js/bootstrap.min.js"></script>

</body>

</html>
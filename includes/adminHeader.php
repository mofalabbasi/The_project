<?php
require_once __DIR__ . '/auth.php';
require_admin();
?>

<nav class="navbar navbar-expand-lg">
  <div class="container">
    <button class="navbar-toggler mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span><i class="fa-solid fa-bars"></i></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-5 mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link active p-2 p-lg-3" href="Abook.php">Books</a></li>
        <li class="nav-item"><a class="nav-link p-2 p-lg-3" href="Auser.php">Users</a></li>
        <li class="nav-item"><a class="nav-link p-2 p-lg-3" href="Aorder.php">Orders</a></li>
        <li class="nav-item"><a class="nav-link p-2 p-lg-3" href="Acontact.php">Messages</a></li>
        <li class="nav-item"><a class="nav-link p-2 p-lg-3" href="about.php">About</a></li>
      </ul>
      <div class="search ps-3 pe-3 d-none d-lg-block me-5">
        <i class="fa-solid fa-magnifying-glass"></i>
      </div>
      <a class="btn btn-primary rounded-pill mb-4 mb-lg-0" href="logout.php" role="button">Logout</a>
    </div>

    <div>
      <?php echo "<img class='rounded-circle position-absolute bg-light' width='40px' height='40px' src='" . htmlspecialchars($_SESSION['img'] ?? '', ENT_QUOTES, 'UTF-8') . "' alt='Profile' />"; ?>
      <button type="button" data-bs-toggle="collapse" data-bs-target="#vv" aria-controls="vv" aria-expanded="false"
        aria-label="Toggle navigation" class="rounded-pill b">
        <?php echo "<span> " . htmlspecialchars($_SESSION['name'] ?? '', ENT_QUOTES, 'UTF-8') . "</span>"; ?>
      </button>
      <div class="collapse position-absolute rounded-bottom text-center mt-2 f" id="vv">
        <?php
        echo "<img class='rounded-circle bg-light' width='90px' height='90px' src='" . htmlspecialchars($_SESSION['img'] ?? '', ENT_QUOTES, 'UTF-8') . "' alt='Profile' />";
        echo "<p class='mt-3'>" . htmlspecialchars($_SESSION['name'] ?? '', ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<p>" . htmlspecialchars($_SESSION['birthdate'] ?? '', ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<p>" . htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<p>" . htmlspecialchars($_SESSION['role'] ?? '', ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<form action='sign up.php' method='post'>";
        echo "<input type='hidden' name='id' value='" . (int)($_SESSION['id'] ?? 0) . "'/>";
        echo "<input type='submit' value='Edit Profile' class='btn btn-primary rounded-pill w-50 mt-2' name='edit_profile' />";
        echo "</form>";
        ?>
      </div>
    </div>
  </div>
</nav>

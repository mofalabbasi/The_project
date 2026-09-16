<?php
session_start();
if(!isset($_SESSION['id']))
{
  header('refresh:0;index.php');
}
?>
<!DOCTYPE html>
<html lang="en">

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
  include "includes/header.php";
  ?>
  <div class="landing d-flex justify-content-center align-items-center">
    <div class="text-center text-light">
      <h1> Books Market </h1>
      <p class="fs-6 text-white-50 mb-5">
        Cultural - scientific books - human development novels - self-development - texts of thoughts - poetry -
        religious
      </p>
      <a class="btn btn-primary rounded-pill" href="books.php" role="button">Browse Books</a>
    </div>
  </div>

  <div class="stuff pt-5">
    <div class="container">

      <div class="row align-items-center">
        <div class="col-lg-6 text-center mb-4 text-md-start">
          <div class="text">
            <h4>Retina Design</h4>
            <p class="text-black-50 fs-6">
              Vestibulum ac diam sit amet quam vehicula elementum sed sit amet
              dui. Mauris blandit aliquet elit, eget tincidunt nibh pulvinar a.
            </p>
            <p class="text-black-50 fs-6">
              Donec rutrum congue leo eget malesuada. Mauris blandit aliquet
              elit, eget tincidunt nibh pulvinar a. Pellentesque in ipsum id
              orci porta dapibus. Proin eget tortor risus. Donec sollicitudin
              molestie malesuada.
            </p>

          </div>
        </div>
        <div class="col-lg-6">
          <img class="img-fluid w-75 h-75 ms-5" src="imgg/1.jpg" alt="">
        </div>
      </div>
    </div>
  </div>
  </div>
  <div class="team text-center pt-5 pb-5">
    <div class="container">
      <h2 class="fw-bold">Some Of Our Book Categories</h2>
      <p class="text-black-50 fs-6 mb-5">Donec rutrum congue leo eget malesuada. Mauris blandit aliquet elit, eget
        tincidunt nibh pulvinar a. Pellentesque in ipsum id orci porta dapibus. Proin eget tortor risus. Donec
        sollicitudin molestie malesuada.</p>
      <div class="row">
        <div class="col-md-6 col-lg-3">
          <div class="box bg-white">
            <img class="img-fluid" src="img/34.jpg" alt="">
            <h4 class="p-3 text-light">Luke Skywalker</h4>
            <blockquote class="text-black-50 p-3">“I don't understand how we got by those troops. I thought we were
              dead.“</blockquote>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="box bg-white">
            <img class="img-fluid" src="img/33.jpg" alt="">
            <h4 class="p-3 text-light">Obi-Wan Kenobi</h4>
            <blockquote class="text-black-50 p-3">“Your clones are very impressive. You must be very proud”
            </blockquote>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="box bg-white">
            <img class="img-fluid" src="img/36.jpg" alt="">
            <h4 class="p-3 text-light">Princess Leia</h4>
            <blockquote class="text-black-50 p-3"> “I don't know who you are or where you came from, but from now on
              you'll do as I tell you, okay?” </blockquote>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="box bg-white">
            <img class="img-fluid" src="img/8.jpg" alt="">
            <h4 class="p-3 text-light">Yoda</h4>
            <blockquote class="text-black-50 p-3">“Do not assume anything Obi-Wan. Clear your mind must be if you are
              to
              discover the real villains behind this plot.”
            </blockquote>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include "includes/footer.php"; ?>

  <script src="js/bootstrap.bundle.min.js"></script>
  <script src="js/all.min.js"></script>
</body>

</html>
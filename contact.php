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
    if ($_SESSION['role'] == 'admin') {
        include "includes/adminHeader.php";
    } else {
        include "includes/header.php";
    }
    ?>
    <div class=" text-center pt-5 pb-5 mt-5">
        <h4 class="text-black fw-bold"> Contact For Any Queries </h4>
        <p class="text-black-50"> Get in touch with us via mail phone.We are waiting for your call or message </p>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-6 text-center mb-4 text-md-start">
                <form action="">
                    <div>
                        <input placeholder="Your Name" class="w-100 rounded-1 mt-5 ps-2 border border-dark-subtle"
                            name="name" type="text" />
                    </div>
                    <div>
                        <input placeholder="Your Phone" class="w-100 rounded-1 mt-3 ps-2 border border-dark-subtle"
                            name="phone" type="number" />
                    </div>
                    <div>
                        <input placeholder="Your Email" class="w-100 rounded-1 mt-3 ps-2 border border-dark-subtle"
                            name="email" type="text" />
                    </div>
                    <div>
                        <input placeholder="Subject" class="w-100 rounded-1 mt-3 ps-2 border border-dark-subtle"
                            name="subject" type="text" />
                    </div>


                    <textarea placeholder="Message" class="w-100 rounded-1 mt-3 ps-2 border border-dark-subtle"
                        style="height: 170px;"></textarea>

                    <input type="submit" value="Send Message" class="btn btn-primary w-25" name="contact_form">
                </form>
            </div>
            <div class="col-lg-6 text-center mb-4 text-md-start ps-5">
                <div class="mt-5">
                    <i class="fa-solid fa-location-dot"></i>
                    <span class="text-black-50 ps-2"> Yemen - sana'a </span>
                </div>
                <div class="mt-3">
                    <i class="fa-solid fa-phone"></i>
                    <span class="text-black-50 ps-2"> 780122441 </span>
                </div>
                <div class="mt-3">
                    <i class="fa-solid fa-envelope"></i>
                    <span class="text-black-50 ps-2"> mohammed8alabbasi8@gmail.com </span>
                </div>
            </div>
        </div>
    </div>

    <?php include "includes/footer.php"; ?>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/all.min.js"></script>
    <script src="bootstrap/dist/js/bootstrap.min.js"></script>
</body>

</html>
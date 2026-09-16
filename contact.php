<?php
require_once "includes/auth.php";
require_login();
include "includes/connect_db.php";

$errors = [];
$successMessage = '';
$name = '';
$phone = '';
$email = '';
$subject = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_form'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '') {
        $errors['name'] = 'Name is required.';
    }
    if ($phone === '' || !preg_match('/^[0-9+\- ]{7,20}$/', $phone)) {
        $errors['phone'] = 'Please enter a valid phone number.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if ($subject === '') {
        $errors['subject'] = 'Subject is required.';
    }
    if ($message === '') {
        $errors['message'] = 'Message is required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare(
                "INSERT INTO contact_messages (name, phone, email, subject, message)
                 VALUES (:name, :phone, :email, :subject, :message)"
            );
            $stmt->execute([
                ':name' => $name,
                ':phone' => $phone,
                ':email' => $email,
                ':subject' => $subject,
                ':message' => $message
            ]);

            $successMessage = 'Your message has been sent successfully.';
            $name = '';
            $phone = '';
            $email = '';
            $subject = '';
            $message = '';
        } catch (Throwable $e) {
            $errors['general'] = 'Unable to send your message right now.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Market Books - Contact</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/all.min.css" />
    <link rel="stylesheet" href="css/boot.css" />
</head>
<body>
<?php
if (($_SESSION['role'] ?? '') === 'admin') {
    include "includes/adminHeader.php";
} else {
    include "includes/header.php";
}
?>

<?php if ($successMessage !== ''): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>
<?php if (isset($errors['general'])): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<div class="text-center pt-5 pb-5 mt-5">
    <h4 class="text-black fw-bold">Contact For Any Queries</h4>
    <p class="text-black-50">Get in touch with us by phone or email. We are waiting for your message.</p>
</div>

<div class="container">
    <div class="row">
        <div class="col-lg-6 text-center mb-4 text-md-start">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="post">
                <div>
                    <input placeholder="Your Name" class="w-100 rounded-1 mt-3 ps-2 border border-dark-subtle" name="name" type="text" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" />
                    <p class="text-danger"><?php echo htmlspecialchars($errors['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div>
                    <input placeholder="Your Phone" class="w-100 rounded-1 mt-2 ps-2 border border-dark-subtle" name="phone" type="tel" value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>" />
                    <p class="text-danger"><?php echo htmlspecialchars($errors['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div>
                    <input placeholder="Your Email" class="w-100 rounded-1 mt-2 ps-2 border border-dark-subtle" name="email" type="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" />
                    <p class="text-danger"><?php echo htmlspecialchars($errors['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div>
                    <input placeholder="Subject" class="w-100 rounded-1 mt-2 ps-2 border border-dark-subtle" name="subject" type="text" value="<?php echo htmlspecialchars($subject, ENT_QUOTES, 'UTF-8'); ?>" />
                    <p class="text-danger"><?php echo htmlspecialchars($errors['subject'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div>
                    <textarea placeholder="Message" class="w-100 rounded-1 mt-2 ps-2 border border-dark-subtle" style="height:170px;" name="message"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <p class="text-danger"><?php echo htmlspecialchars($errors['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <input type="submit" value="Send Message" class="btn btn-primary w-50" name="contact_form">
            </form>
        </div>

        <div class="col-lg-6 text-center mb-4 text-md-start ps-5">
            <div class="mt-5"><i class="fa-solid fa-location-dot"></i><span class="text-black-50 ps-2">Yemen - Sana'a</span></div>
            <div class="mt-3"><i class="fa-solid fa-phone"></i><span class="text-black-50 ps-2">780122441</span></div>
            <div class="mt-3"><i class="fa-solid fa-envelope"></i><span class="text-black-50 ps-2">mohammed8alabbasi8@gmail.com</span></div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/all.min.js"></script>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html>

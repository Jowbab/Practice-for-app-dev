<?php
session_start();

$error = '';
$registered = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Please complete all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $_SESSION['player_name'] = $name;
        $_SESSION['registered_email'] = $email;
        $registered = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Math Quiz Game</title>
    <link rel="stylesheet" href="../css/login.css?v=3">
    <link rel="stylesheet" href="../css/theme.css">
    <script src="../js/theme.js" defer></script>
</head>
<main class="box registration-box">
    <?php if ($registered): ?>
        <h1>Registration Complete</h1>
        <p class="success">Welcome, <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>.</p>
        <p class="info">Your name is ready to use on the quiz scoreboard.</p>
        <a class="button-link" href="../process/Index.php">Start the Quiz</a>
    <?php else: ?>
        <h1>Student Login</h1>
        <p class="info">Please login to continue to the math quiz.</p>
        <div class="welcome-world">
            <span>WELCOME TO MY WORLD. ようこそ</span>
            <img src="../Extra/rinnegan.jpg" alt="Rinnegan">
        </div>
        <img class="corner-image" src="../Extra/The f goat.jpg" alt="The f goat.">

        <?php if ($error !== ''): ?>
            <p class="feedback wrong"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form action="Index.php" method="post">
            <label for="name">Name(名前):</label>
            <input type="text" id="name" name="name" maxlength="30" required>

            <label for="email">Email(電子メール):</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password(パスワード):</label>
            <input type="password" id="password" name="password" minlength="6" required>

            <button type="submit">Login</button>
        </form>
        <a class="button-link" href="../register/Registration.php">Register</a>
        <a class="text-link" href="../process/Index.php">Continue without registration</a>
    <?php endif; ?>
</main>
      

<?php
    session_start();

    $errors = [
        'login' => $_SESSION ['LOGIN_ERROR'] ?? '',
        'register' => $_SESSION ['REGISTER_ERROR'] ?? ''
    ];
    $activeForm = $_SESSION['active_form'] ?? 'login-form';

    session_unset();
    function showError($error) {
        return !empty($error) ? "<p class='error'>$error</p>" : '';
    }
    function isActive($formName, $activeForm) {
       return $formName === $activeForm ? 'active' : '';
    }
?>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="../style/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>
<body> 
    <div class="container">
        <div class="form-box <?=isActive('login-form', $activeForm)?>" id="login-form">
            <form action="login-signup.php" method ="post" autocomplete="off">
                <h2>Login</h2>
                <?=showError($errors['login'])?>
                <input type="email" name="email" placeholder="Email" required autocomplete="off">
                <input type="hidden" name="fake_username" autocomplete="username">
                <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
                <button type="submit" name="login">Login</button>
                <p>Dont have an account? <a href="#" onclick="showForm('signup-form')">Sign Up</a></p>
            </form>
        </div>
    </div>

    <div class="form-box <?=isActive('signup-form', $activeForm)?>" id="signup-form">
            <form action="login-signup.php" method ="post" autocomplete="off">
                <h2>Sign Up</h2>
                <?=showError($errors['register'])?>
                <input type="text" name="username" placeholder="Nama mahasiswa" required autocomplete="off">
                <input type="text" name="npm" placeholder="NPM" required autocomplete="off">
                <input type="email" name="email" placeholder="Email" required autocomplete="off">
                <input type="hidden" name="fake_password" autocomplete="current-password">
                <input type="password" name="password" placeholder="Password" required autocomplete="new-password">
                
                <button type="submit" name="register">Sign Up</button>
                <p>Already have an account? <a href="#" onclick="showForm('login-form')">Login</a></p>
            </form>
        </div>
    <script src="script.js">
    </script>
</body>
</html>
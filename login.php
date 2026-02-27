<?php 
session_start(); 
$conn = mysqli_connect("localhost", "root", "", "gestion_commerciale"); 

$msg = ""; 

if(isset($_POST['submit'])){ 
    $email = mysqli_real_escape_string($conn, $_POST['email']); 
    $password = mysqli_real_escape_string($conn, $_POST['password']); 

    $sql = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'"); 
    $num = mysqli_num_rows($sql);     

    if($num > 0){ 
        $row = mysqli_fetch_assoc($sql); 
        // تحقق من كلمة المرور
        if($row['password'] === $password){ // إذا كان غير مشفر، أو استبدل بـ password_verify() إذا مشفر
$_SESSION['USER_ID'] = $row['id'];
$_SESSION['username'] = $row['username'];
$_SESSION['role'] = $row['role']; // هادي ضرورية
            // توجيه حسب الدور
            if($row['role'] === 'admin'){
                header("location:index.php"); // Admin dashboard
            } else {
                header("location:index.php"); // User dashboard
            }
            exit;
        } else {
            $msg = "Email ou mot de passe invalide !"; 
        }
    } else { 
        $msg = "Email ou mot de passe invalide !"; 
    } 
} 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion Commerciale</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .login-card h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #667eea;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #667eea;
        }
        .btn-login {
            background: #667eea;
            color: #fff;
            width: 100%;
            padding: 0.5rem;
            font-weight: bold;
        }
        .btn-login:hover {
            background: #5a67d8;
        }
        .error-msg {
            background: #ff4d4f;
            color: white;
            padding: 0.5rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }
        @media(max-width: 576px){
            .login-card { padding: 1.5rem; }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>Connexion</h2>

        <?php if(!empty($msg)): ?>
            <div class="error-msg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email :</label>
                <input type="email" name="email" placeholder="Votre email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe :</label>
                <input type="password" name="password" placeholder="Votre mot de passe" class="form-control" required>
            </div>
            <div class="d-grid">
                <button type="submit" name="submit" class="btn btn-login">Se connecter</button>
            </div>
        </form>
    </div>

</body>
</html>

<?php
session_start();
require_once "config/db.php";

// Agar admin already logged in hai to redirect kar do
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, email, password, name FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_name'] = $admin['name'];
            header("Location: index.php");
            exit;
        } else {
            $error = "❌ Incorrect password!";
        }
    } else {
        $error = "❌ No admin found with that email!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | SDJobs</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #74b9ff, #a29bfe);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }

        .login-wrapper {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            display: flex;
            max-width: 900px;
            width: 100%;
        }

        .login-left {
            background: linear-gradient(135deg, #6c63ff, #5b9bd5);
            color: #fff;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .login-left img {
            width: 80%;
            max-width: 320px;
            margin-bottom: 30px;
        }

        .login-left h3 {
            font-weight: 700;
            margin-bottom: 10px;
        }

        .login-right {
            flex: 1;
            padding: 50px 40px;
        }

        .login-right h4 {
            font-weight: 700;
            margin-bottom: 30px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
        }

        .input-group-text {
            background: #fff;
            border: none;
        }

        .btn-login {
            background: #6c63ff;
            color: #fff;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            border: none;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: #574bff;
        }

        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
            }
            .login-left {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <!-- LEFT SIDE IMAGE -->
    <div class="login-left">
        <img src="https://toppng.com/uploads/preview/job-search-remotive-home-find-a-job-cartoo-11563059516oo2nwsqwn9.png" alt="Admin Illustration">
        <h3>Welcome</h3>
        <p>Login to access your admin dashboard</p>
    </div>

    <!-- RIGHT SIDE FORM -->
    <div class="login-right">
        <h4>Admin Login</h4>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Email address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-envelope text-secondary"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock text-secondary"></i></span>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                    <button type="button" class="btn btn-light border" id="togglePassword">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-login w-100">Login</button>
        </form>
    </div>
</div>

<script>
    // Password show/hide toggle
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
</script>

</body>
</html>

<?php

session_start();

if (
    isset($_SESSION["admin_logged_in"]) &&
    $_SESSION["admin_logged_in"] === true
) {
    header("Location: admin-dashboard.php");
    exit;
}

$host = "localhost";
$username = "root";
$password = "";
$database = "prograte_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed.");
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username_input = trim($_POST["username"] ?? "");
    $password_input = $_POST["password"] ?? "";

    if ($username_input === "" || $password_input === "") {

        $error = "Please enter username and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, username, password FROM admins WHERE username = ? LIMIT 1"
        );

        $stmt->bind_param("s", $username_input);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $admin = $result->fetch_assoc();

            if (password_verify($password_input, $admin["password"])) {

                session_regenerate_id(true);

                $_SESSION["admin_logged_in"] = true;
                $_SESSION["admin_id"] = $admin["id"];
                $_SESSION["admin_username"] = $admin["username"];

                header("Location: admin-dashboard.php");
                exit;

            } else {

                $error = "Invalid username or password.";

            }

        } else {

            $error = "Invalid username or password.";

        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Login | Prograte</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {

            min-height: 100vh;

            display: flex;

            align-items: center;
            justify-content: center;

            padding: 20px;

            background: #070707;

            color: #f2f2f2;

            font-family: "Inter", sans-serif;
        }

        .login-box {

            width: 100%;
            max-width: 430px;

            padding: 40px 35px;

            background: #121212;

            border: 1px solid
                rgba(255,255,255,.08);

            border-radius: 16px;

            box-shadow:
                0 25px 70px
                rgba(0,0,0,.5);
        }

        .logo {

            text-align: center;

            margin-bottom: 35px;
        }

        .logo h1 {

            font-size: 28px;

            font-weight: 900;

            letter-spacing: 2px;
        }

        .logo span {

            color: #d4af37;
        }

        .logo p {

            margin-top: 6px;

            color: #777;

            font-size: 11px;

            letter-spacing: 2px;
        }

        .login-icon {

            width: 65px;
            height: 65px;

            display: flex;

            align-items: center;
            justify-content: center;

            margin: 0 auto 25px;

            border-radius: 50%;

            background:
                rgba(212,175,55,.1);

            border: 1px solid #d4af37;

            color: #d4af37;

            font-size: 25px;
        }

        .login-heading {

            text-align: center;

            margin-bottom: 30px;
        }

        .login-heading h2 {

            font-size: 24px;

            margin-bottom: 8px;
        }

        .login-heading p {

            color: #777;

            font-size: 13px;
        }

        .form-group {

            margin-bottom: 20px;
        }

        .form-group label {

            display: block;

            margin-bottom: 8px;

            color: #bbb;

            font-size: 13px;

            font-weight: 600;
        }

        .input-box {

            position: relative;
        }

        .input-box i {

            position: absolute;

            left: 15px;
            top: 50%;

            transform: translateY(-50%);

            color: #777;
        }

        .input-box input {

            width: 100%;

            padding: 14px 15px 14px 45px;

            color: #fff;

            background: #0d0d0d;

            border: 1px solid
                rgba(255,255,255,.1);

            border-radius: 8px;

            outline: none;

            font-size: 14px;

            transition: .3s;
        }

        .input-box input:focus {

            border-color: #d4af37;

            box-shadow:
                0 0 0 3px
                rgba(212,175,55,.08);
        }

        .error {

            margin-bottom: 20px;

            padding: 12px 14px;

            color: #ff8a8a;

            background:
                rgba(255,80,80,.08);

            border: 1px solid
                rgba(255,80,80,.2);

            border-radius: 7px;

            font-size: 13px;
        }

        .login-btn {

            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 8px;

            background: #d4af37;

            color: #070707;

            font-size: 14px;

            font-weight: 800;

            cursor: pointer;

            transition: .3s;
        }

        .login-btn:hover {

            background: #f0d477;

            transform: translateY(-2px);
        }

        .back-link {

            display: block;

            margin-top: 22px;

            text-align: center;

            color: #777;

            text-decoration: none;

            font-size: 12px;
        }

        .back-link:hover {

            color: #d4af37;
        }

        @media (max-width: 450px) {

            .login-box {

                padding: 35px 22px;
            }

        }

    </style>

</head>

<body>

    <div class="login-box">

        <div class="logo">

            <h1>
                PROGRATE <span>ADMIN</span>
            </h1>

            <p>
                DIGITAL SOLUTIONS
            </p>

        </div>

        <div class="login-icon">

            <i class="fa-solid fa-lock"></i>

        </div>

        <div class="login-heading">

            <h2>
                Admin Login
            </h2>

            <p>
                Login to manage project inquiries
            </p>

        </div>

        <?php if ($error): ?>

            <div class="error">

                <i class="fa-solid fa-circle-exclamation"></i>

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label for="username">
                    Username
                </label>

                <div class="input-box">

                    <i class="fa-solid fa-user"></i>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter username"
                        required
                    >

                </div>

            </div>

            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <div class="input-box">

                    <i class="fa-solid fa-lock"></i>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter password"
                        required
                    >

                </div>

            </div>

            <button
                type="submit"
                class="login-btn"
            >

                <i class="fa-solid fa-right-to-bracket"></i>

                Login to Dashboard

            </button>

        </form>

        <a
            href="index.php"
            class="back-link"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back to Website

        </a>

    </div>

</body>

</html>
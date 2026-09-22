<?php

// Database Connection
$host = "localhost";
$username = "root";
$password = "";
$database = "prograte_db";

$conn = new mysqli($host, $username, $password, $database);

// Check Connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Only allow POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: contact.php");
    exit;
}

// Get Form Data
$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$service = trim($_POST["service"] ?? "");
$budget = trim($_POST["budget"] ?? "");
$message = trim($_POST["message"] ?? "");

// Basic Validation
if ($name === "" || $email === "" || $service === "" || $message === "") {
    die("Please fill in all required fields.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Please enter a valid email address.");
}

// Insert Data
$sql = "INSERT INTO messages 
        (name, email, phone, service, budget, message)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Something went wrong. Please try again.");
}

$stmt->bind_param(
    "ssssss",
    $name,
    $email,
    $phone,
    $service,
    $budget,
    $message
);

// Execute
if ($stmt->execute()) {
    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Message Sent | Prograte Digital Solutions</title>

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

            .success-box {
                width: 100%;
                max-width: 600px;
                padding: 50px 35px;
                text-align: center;
                background: #121212;
                border: 1px solid rgba(255, 255, 255, 0.09);
                border-radius: 18px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            }

            .success-icon {
                width: 80px;
                height: 80px;
                margin: 0 auto 25px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background: rgba(212, 175, 55, 0.12);
                border: 1px solid #d4af37;
                color: #d4af37;
                font-size: 34px;
            }

            h1 {
                margin-bottom: 15px;
                font-size: 32px;
            }

            h1 span {
                color: #d4af37;
            }

            p {
                margin-bottom: 30px;
                color: #bdbdbd;
                line-height: 1.7;
            }

            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                padding: 14px 25px;
                background: #d4af37;
                color: #070707;
                text-decoration: none;
                font-weight: 700;
                border-radius: 8px;
                transition: 0.3s;
            }

            .back-btn:hover {
                background: #f0d477;
                transform: translateY(-2px);
            }

            @media (max-width: 500px) {
                .success-box {
                    padding: 40px 20px;
                }

                h1 {
                    font-size: 26px;
                }
            }
        </style>
    </head>

    <body>

        <div class="success-box">

            <div class="success-icon">
                <i class="fa-solid fa-check"></i>
            </div>

            <h1>
                Message <span>Sent!</span>
            </h1>

            <p>
                Thank you for contacting Prograte Digital Solutions.
                Your project inquiry has been received successfully.
                Our team will get back to you soon.
            </p>

            <a href="contact.php" class="back-btn">
                <i class="fa-solid fa-arrow-left"></i>
                Back to Contact
            </a>

        </div>

    </body>

    </html>

    <?php

} else {
    die("Unable to send your message. Please try again.");
}

$stmt->close();
$conn->close();
?>
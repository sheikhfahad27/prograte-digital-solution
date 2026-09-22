<?php

session_start();

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {
    header("Location: admin-login.php");
    exit;
}


// Database Connection
$host = "localhost";
$username = "root";
$password = "";
$database = "prograte_db";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($conn->connect_error) {
    die("Database connection failed.");
}


// Get Message ID
$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: messages.php");
    exit;
}


// Get Message
$stmt = $conn->prepare(
    "SELECT * FROM messages WHERE id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

$message = $result->fetch_assoc();

$stmt->close();


if (!$message) {
    header("Location: messages.php");
    exit;
}


// Automatically mark as Read
if ($message["status"] === "New") {

    $update = $conn->prepare(
        "UPDATE messages SET status = 'Read' WHERE id = ?"
    );

    $update->bind_param("i", $id);
    $update->execute();
    $update->close();

    $message["status"] = "Read";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Message #<?php echo $message["id"]; ?> | Prograte
    </title>

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

            background: #070707;

            color: #f2f2f2;

            font-family: "Inter", sans-serif;
        }


        .topbar {

            padding: 20px 35px;

            display: flex;

            align-items: center;
            justify-content: space-between;

            border-bottom: 1px solid
                rgba(255,255,255,.08);

            background: #0d0d0d;
        }


        .brand {

            color: #fff;

            text-decoration: none;

            font-size: 20px;

            font-weight: 900;

            letter-spacing: 2px;
        }


        .brand span {

            color: #d4af37;
        }


        .back-btn {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding: 10px 16px;

            color: #d4af37;

            border: 1px solid #d4af37;

            border-radius: 7px;

            text-decoration: none;

            font-size: 13px;

            font-weight: 600;

            transition: .3s;
        }


        .back-btn:hover {

            background: #d4af37;

            color: #070707;
        }


        .container {

            width: min(
                100% - 40px,
                1000px
            );

            margin: 40px auto;
        }


        .page-title {

            margin-bottom: 25px;
        }


        .page-title h1 {

            font-size: 30px;

            font-weight: 800;
        }


        .page-title p {

            margin-top: 7px;

            color: #777;

            font-size: 13px;
        }


        .message-card {

            background: #121212;

            border: 1px solid
                rgba(255,255,255,.08);

            border-radius: 14px;

            overflow: hidden;
        }


        .message-header {

            padding: 25px;

            display: flex;

            align-items: center;
            justify-content: space-between;

            border-bottom: 1px solid
                rgba(255,255,255,.08);
        }


        .customer {

            display: flex;

            align-items: center;

            gap: 15px;
        }


        .customer-icon {

            width: 50px;
            height: 50px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background: rgba(212,175,55,.1);

            color: #d4af37;

            font-size: 20px;
        }


        .customer h2 {

            font-size: 18px;

            margin-bottom: 5px;
        }


        .customer p {

            color: #777;

            font-size: 12px;
        }


        .status {

            padding: 7px 13px;

            border-radius: 20px;

            color: #d4af37;

            background: rgba(212,175,55,.1);

            border: 1px solid
                rgba(212,175,55,.25);

            font-size: 11px;

            font-weight: 700;
        }


        .message-body {

            padding: 30px;
        }


        .details-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;

            margin-bottom: 30px;
        }


        .detail {

            padding: 18px;

            background: #0d0d0d;

            border: 1px solid
                rgba(255,255,255,.06);

            border-radius: 9px;
        }


        .detail-label {

            display: block;

            margin-bottom: 7px;

            color: #777;

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: .7px;
        }


        .detail-value {

            color: #eee;

            font-size: 14px;

            font-weight: 600;

            word-break: break-word;
        }


        .detail-value a {

            color: #d4af37;

            text-decoration: none;
        }


        .detail-value a:hover {

            text-decoration: underline;
        }


        .project-message {

            padding: 22px;

            background: #0d0d0d;

            border: 1px solid
                rgba(255,255,255,.06);

            border-radius: 9px;
        }


        .project-message h3 {

            margin-bottom: 15px;

            color: #d4af37;

            font-size: 14px;
        }


        .project-message p {

            color: #bbb;

            line-height: 1.8;

            font-size: 14px;

            white-space: pre-wrap;
        }


        .actions {

            display: flex;

            gap: 12px;

            margin-top: 25px;
        }


        .action-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding: 12px 18px;

            border-radius: 7px;

            text-decoration: none;

            font-size: 13px;

            font-weight: 700;

            transition: .3s;
        }


        .email-btn {

            background: #d4af37;

            color: #070707;
        }


        .email-btn:hover {

            background: #f0d477;
        }


        .whatsapp-btn {

            color: #d4af37;

            border: 1px solid #d4af37;
        }


        .whatsapp-btn:hover {

            background: #d4af37;

            color: #070707;
        }


        @media (max-width: 650px) {

            .topbar {

                padding: 18px 20px;

                gap: 15px;
            }


            .brand {

                font-size: 17px;
            }


            .container {

                width: min(
                    100% - 25px,
                    1000px
                );

                margin: 25px auto;
            }


            .page-title h1 {

                font-size: 25px;
            }


            .message-header {

                align-items: flex-start;

                flex-direction: column;

                gap: 15px;
            }


            .message-body {

                padding: 20px;
            }


            .details-grid {

                grid-template-columns: 1fr;
            }


            .actions {

                flex-direction: column;
            }


            .action-btn {

                width: 100%;
            }

        }

    </style>

</head>

<body>


<header class="topbar">

    <a href="messages.php" class="brand">

        PROGRATE
        <span>ADMIN</span>

    </a>


    <a
        href="messages.php"
        class="back-btn"
    >

        <i class="fa-solid fa-arrow-left"></i>

        Back to Messages

    </a>

</header>


<main class="container">


    <div class="page-title">

        <h1>
            Message Details
        </h1>

        <p>
            Project inquiry #<?php
            echo $message["id"];
            ?>
        </p>

    </div>


    <div class="message-card">


        <div class="message-header">

            <div class="customer">

                <div class="customer-icon">

                    <i class="fa-solid fa-user"></i>

                </div>


                <div>

                    <h2>

                        <?php
                        echo htmlspecialchars(
                            $message["name"]
                        );
                        ?>

                    </h2>

                    <p>

                        Received
                        <?php
                        echo date(
                            "d M Y, h:i A",
                            strtotime(
                                $message["created_at"]
                            )
                        );
                        ?>

                    </p>

                </div>

            </div>


            <span class="status">

                <?php
                echo htmlspecialchars(
                    $message["status"]
                );
                ?>

            </span>

        </div>


        <div class="message-body">


            <div class="details-grid">


                <div class="detail">

                    <span class="detail-label">
                        Email
                    </span>

                    <div class="detail-value">

                        <a
                            href="mailto:<?php
                            echo htmlspecialchars(
                                $message["email"]
                            );
                            ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $message["email"]
                            );
                            ?>

                        </a>

                    </div>

                </div>


                <div class="detail">

                    <span class="detail-label">
                        Phone
                    </span>

                    <div class="detail-value">

                        <?php if (!empty($message["phone"])): ?>

                            <a
                                href="tel:<?php
                                echo htmlspecialchars(
                                    $message["phone"]
                                );
                                ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $message["phone"]
                                );
                                ?>

                            </a>

                        <?php else: ?>

                            Not provided

                        <?php endif; ?>

                    </div>

                </div>


                <div class="detail">

                    <span class="detail-label">
                        Required Service
                    </span>

                    <div class="detail-value">

                        <?php
                        echo htmlspecialchars(
                            $message["service"]
                        );
                        ?>

                    </div>

                </div>


                <div class="detail">

                    <span class="detail-label">
                        Estimated Budget
                    </span>

                    <div class="detail-value">

                        <?php
                        echo htmlspecialchars(
                            $message["budget"] ?: "Not specified"
                        );
                        ?>

                    </div>

                </div>


            </div>


            <div class="project-message">

                <h3>

                    <i class="fa-solid fa-file-lines"></i>

                    Project Details

                </h3>


                <p>

                    <?php
                    echo htmlspecialchars(
                        $message["message"]
                    );
                    ?>

                </p>

            </div>


            <div class="actions">


                <a
                    href="mailto:<?php
                    echo htmlspecialchars(
                        $message["email"]
                    );
                    ?>"
                    class="action-btn email-btn"
                >

                    <i class="fa-solid fa-envelope"></i>

                    Reply via Email

                </a>


                <?php if (!empty($message["phone"])): ?>

                    <a
                        href="https://wa.me/<?php
                        echo preg_replace(
                            '/[^0-9]/',
                            '',
                            $message["phone"]
                        );
                        ?>"
                        target="_blank"
                        class="action-btn whatsapp-btn"
                    >

                        <i class="fa-brands fa-whatsapp"></i>

                        WhatsApp Customer

                    </a>

                <?php endif; ?>


            </div>


        </div>

    </div>


</main>


</body>

</html>

<?php
$conn->close();
?>
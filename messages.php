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

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}


// Delete Message
if (isset($_GET["delete"])) {

    $id = intval($_GET["delete"]);

    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: messages.php");
    exit;
}


// Update Status
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_status"])) {

    $id = intval($_POST["id"]);
    $status = $_POST["status"];

    $allowed_statuses = ["New", "Read", "Replied"];

    if (in_array($status, $allowed_statuses)) {

        $stmt = $conn->prepare(
            "UPDATE messages SET status = ? WHERE id = ?"
        );

        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: messages.php");
    exit;
}


// Statistics
$total_result = $conn->query(
    "SELECT COUNT(*) AS total FROM messages"
);

$total = $total_result->fetch_assoc()["total"];


$new_result = $conn->query(
    "SELECT COUNT(*) AS total FROM messages WHERE status = 'New'"
);

$new_messages = $new_result->fetch_assoc()["total"];


$read_result = $conn->query(
    "SELECT COUNT(*) AS total FROM messages WHERE status = 'Read'"
);

$read_messages = $read_result->fetch_assoc()["total"];


$replied_result = $conn->query(
    "SELECT COUNT(*) AS total FROM messages WHERE status = 'Replied'"
);

$replied_messages = $replied_result->fetch_assoc()["total"];


// Get Messages
$search = trim($_GET["search"] ?? "");
$status_filter = $_GET["status"] ?? "";
$service_filter = $_GET["service"] ?? "";

$sql = "SELECT * FROM messages WHERE 1=1";

$params = [];
$types = "";

if ($search !== "") {

    $sql .= " AND (
        name LIKE ?
        OR email LIKE ?
        OR phone LIKE ?
    )";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "sss";
}

if (
    in_array(
        $status_filter,
        ["New", "Read", "Replied"]
    )
) {

    $sql .= " AND status = ?";

    $params[] = $status_filter;

    $types .= "s";
}

if ($service_filter !== "") {

    $sql .= " AND service = ?";

    $params[] = $service_filter;

    $types .= "s";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$messages = $stmt->get_result();

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
        Messages | Prograte Digital Solutions
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


        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {

            position: fixed;
            left: 0;
            top: 0;

            width: 250px;
            height: 100vh;

            padding: 30px 20px;

            background: #0d0d0d;

            border-right: 1px solid
                rgba(255,255,255,.08);
        }


        .brand {

            display: block;

            margin-bottom: 50px;

            text-decoration: none;
            color: #fff;
        }


        .brand span {

            display: block;

            font-size: 24px;
            font-weight: 900;
            letter-spacing: 2px;
        }


        .brand small {

            color: #d4af37;

            font-size: 10px;

            letter-spacing: 2px;
        }


        .sidebar-menu {

            display: flex;

            flex-direction: column;

            gap: 8px;
        }


        .sidebar-menu a {

            display: flex;

            align-items: center;

            gap: 13px;

            padding: 14px 16px;

            color: #aaa;

            text-decoration: none;

            border-radius: 8px;

            transition: .3s;
        }


        .sidebar-menu a:hover,
        .sidebar-menu a.active {

            color: #d4af37;

            background: rgba(212,175,55,.08);
        }


        /* =========================
           MAIN
        ========================= */

        .main {

            margin-left: 250px;

            padding: 35px;
        }


        .topbar {

            display: flex;

            align-items: center;
            justify-content: space-between;

            margin-bottom: 35px;
        }


        .topbar h1 {

            font-size: 30px;
            font-weight: 800;
        }


        .topbar p {

            margin-top: 6px;

            color: #888;

            font-size: 14px;
        }


        .website-btn {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding: 12px 18px;

            color: #d4af37;

            border: 1px solid #d4af37;

            border-radius: 7px;

            text-decoration: none;

            font-size: 13px;

            font-weight: 600;

            transition: .3s;
        }


        .website-btn:hover {

            background: #d4af37;

            color: #070707;
        }


        /* =========================
           STAT CARDS
        ========================= */

        .stats {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

            margin-bottom: 30px;
        }


        .stat-card {

            padding: 25px;

            background: #121212;

            border: 1px solid
                rgba(255,255,255,.08);

            border-radius: 12px;
        }


        .stat-top {

            display: flex;

            align-items: center;
            justify-content: space-between;

            margin-bottom: 20px;
        }


        .stat-icon {

            width: 45px;
            height: 45px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 9px;

            background: rgba(212,175,55,.1);

            color: #d4af37;
        }


        .stat-card h2 {

            font-size: 30px;

            margin-bottom: 5px;
        }


        .stat-card p {

            color: #888;

            font-size: 13px;
        }


        /* =========================
           MESSAGES
        ========================= */

        .messages-box {

            background: #121212;

            border: 1px solid
                rgba(255,255,255,.08);

            border-radius: 12px;

            overflow: hidden;
        }


        .messages-header {

            padding: 22px 25px;

            border-bottom: 1px solid
                rgba(255,255,255,.08);
        }


        .messages-header h2 {

            font-size: 18px;
        }


        .table-wrapper {

            width: 100%;

            overflow-x: auto;
        }


        table {

            width: 100%;

            min-width: 1050px;

            border-collapse: collapse;
        }


        th {

            padding: 16px 18px;

            text-align: left;

            color: #888;

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: .7px;

            background: #0d0d0d;
        }


        td {

            padding: 18px;

            border-top: 1px solid
                rgba(255,255,255,.06);

            color: #ccc;

            font-size: 13px;

            vertical-align: middle;
        }


        tbody tr {

            transition: .2s;
        }


        tbody tr:hover {

            background: rgba(255,255,255,.025);
        }


        .customer-name {

            color: #fff;

            font-weight: 600;
        }


        .email {

            color: #999;
        }


        .service {

            color: #d4af37;

            font-weight: 600;
        }


        .message-text {

            max-width: 280px;

            color: #999;

            line-height: 1.5;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;
        }


        /* =========================
           STATUS
        ========================= */

        .status-form {

            display: inline-block;
        }


        .status-select {

            padding: 7px 10px;

            color: #ddd;

            background: #1b1b1b;

            border: 1px solid
                rgba(255,255,255,.1);

            border-radius: 6px;

            outline: none;

            cursor: pointer;

            font-size: 12px;
        }


        .status-select:focus {

            border-color: #d4af37;
        }


        /* =========================
           ACTIONS
        ========================= */

        .actions {

            display: flex;

            gap: 8px;
        }


        .action-btn {

            width: 34px;
            height: 34px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 6px;

            text-decoration: none;

            transition: .2s;
        }


        .view-btn {

            color: #d4af37;

            background: rgba(212,175,55,.1);
        }


        .delete-btn {

            color: #e57373;

            background: rgba(229,115,115,.08);
        }


        .view-btn:hover {

            background: #d4af37;

            color: #070707;
        }


        .delete-btn:hover {

            background: #e57373;

            color: #070707;
        }


        .empty {

            padding: 60px 20px;

            text-align: center;

            color: #777;
        }


        .empty i {

            display: block;

            margin-bottom: 15px;

            font-size: 40px;

            color: #444;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1100px) {

            .sidebar {
                width: 210px;
            }

            .main {
                margin-left: 210px;
                padding: 25px;
            }

            .stats {
                grid-template-columns:
                    repeat(2, 1fr);
            }
        }


        @media (max-width: 700px) {

            .sidebar {

                position: static;

                width: 100%;

                height: auto;

                border-right: none;

                border-bottom: 1px solid
                    rgba(255,255,255,.08);
            }


            .brand {
                margin-bottom: 20px;
            }


            .sidebar-menu {

                flex-direction: row;

                flex-wrap: wrap;
            }


            .main {

                margin-left: 0;

                padding: 20px;
            }


            .topbar {

                align-items: flex-start;

                flex-direction: column;

                gap: 15px;
            }


            .stats {

                grid-template-columns: 1fr;
            }
        }


        @media (max-width: 450px) {

            .topbar h1 {
                font-size: 24px;
            }

            .sidebar-menu a {
                padding: 10px 12px;
                font-size: 12px;
            }

            .stat-card {
                padding: 20px;
            }
        }

        .filters {
    padding: 20px 25px;
    background: #0d0d0d;
    border-bottom: 1px solid rgba(255,255,255,.08);
}

.filters form {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.search-box {
    position: relative;
    flex: 1;
    min-width: 240px;
}

.search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #777;
}

.search-box input,
.filters select {
    width: 100%;
    padding: 12px 14px;
    color: #ddd;
    background: #1b1b1b;
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 7px;
    outline: none;
    font-family: inherit;
    font-size: 13px;
}

.search-box input {
    padding-left: 40px;
}

.search-box input:focus,
.filters select:focus {
    border-color: #d4af37;
}

.filters select {
    width: 180px;
    cursor: pointer;
}

.filters button {
    padding: 12px 18px;
    border: none;
    border-radius: 7px;
    background: #d4af37;
    color: #070707;
    font-weight: 700;
    cursor: pointer;
}

.clear-filter {
    padding: 11px 15px;
    color: #999;
    text-decoration: none;
    font-size: 13px;
}

.clear-filter:hover {
    color: #d4af37;
}

@media (max-width: 700px) {

    .filters form {
        flex-direction: column;
        align-items: stretch;
    }

    .search-box,
    .filters select {
        width: 100%;
        min-width: 100%;
    }

    .filters button {
        width: 100%;
    }

    .clear-filter {
        text-align: center;
    }
}

    </style>

</head>


<body>


<!-- =========================
     SIDEBAR
========================= -->

<aside class="sidebar">

    <a href="index.php" class="brand">

        <span>PROGRATE</span>

        <small>DIGITAL SOLUTIONS</small>

    </a>


    <nav class="sidebar-menu">

        <a href="index.php">

            <i class="fa-solid fa-house"></i>

            Website

        </a>


        <a href="messages.php" class="active">

            <i class="fa-solid fa-envelope"></i>

            Messages

        </a>

        <a href="admin-logout.php">

    <i class="fa-solid fa-right-from-bracket"></i>

    Logout

</a>

    </nav>

</aside>


<!-- =========================
     MAIN CONTENT
========================= -->

<main class="main">


    <div class="topbar">

        <div>

            <h1>Messages</h1>

            <p>
                Manage your project inquiries
            </p>

        </div>


        <a
            href="contact.php"
            class="website-btn"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Contact Page

        </a>

    </div>

    <div class="filters">

    <form method="GET">

        <div class="search-box">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="text"
                name="search"
                placeholder="Search name, email or phone..."
                value="<?php echo htmlspecialchars($search); ?>"
            >

        </div>


        <select name="status">

            <option value="">
                All Status
            </option>

            <option
                value="New"
                <?php echo $status_filter === "New" ? "selected" : ""; ?>
            >
                New
            </option>

            <option
                value="Read"
                <?php echo $status_filter === "Read" ? "selected" : ""; ?>
            >
                Read
            </option>

            <option
                value="Replied"
                <?php echo $status_filter === "Replied" ? "selected" : ""; ?>
            >
                Replied
            </option>

        </select>


        <select name="service">

            <option value="">
                All Services
            </option>

            <option value="Web Development">
                Web Development
            </option>

            <option value="Mobile App Development">
                Mobile App Development
            </option>

            <option value="Software Solutions">
                Software Solutions
            </option>

            <option value="E-Commerce">
                E-Commerce
            </option>

            <option value="Digital Marketing">
                Digital Marketing
            </option>

            <option value="SEO">
                SEO
            </option>

        </select>


        <button type="submit">

            <i class="fa-solid fa-filter"></i>

            Filter

        </button>


        <a href="messages.php" class="clear-filter">

            Clear

        </a>

    </form>

</div>


    <!-- =========================
         STATISTICS
    ========================= -->

    <div class="stats">


        <div class="stat-card">

            <div class="stat-top">

                <div>

                    <h2>
                        <?php echo $total; ?>
                    </h2>

                    <p>Total Messages</p>

                </div>


                <div class="stat-icon">

                    <i class="fa-solid fa-envelope"></i>

                </div>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-top">

                <div>

                    <h2>
                        <?php echo $new_messages; ?>
                    </h2>

                    <p>New Messages</p>

                </div>


                <div class="stat-icon">

                    <i class="fa-solid fa-bell"></i>

                </div>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-top">

                <div>

                    <h2>
                        <?php echo $read_messages; ?>
                    </h2>

                    <p>Read Messages</p>

                </div>


                <div class="stat-icon">

                    <i class="fa-solid fa-eye"></i>

                </div>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-top">

                <div>

                    <h2>
                        <?php echo $replied_messages; ?>
                    </h2>

                    <p>Replied</p>

                </div>


                <div class="stat-icon">

                    <i class="fa-solid fa-check"></i>

                </div>

            </div>

        </div>


    </div>


    <!-- =========================
         MESSAGES TABLE
    ========================= -->

    <div class="messages-box">

        <div class="messages-header">

            <h2>
                Project Inquiries
            </h2>

        </div>


        <div class="table-wrapper">

            <?php if ($messages->num_rows > 0): ?>

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Customer</th>

                            <th>Contact</th>

                            <th>Service</th>

                            <th>Budget</th>

                            <th>Message</th>

                            <th>Status</th>

                            <th>Date</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php while ($row = $messages->fetch_assoc()): ?>

                        <tr>

                            <td>
                                #<?php echo $row["id"]; ?>
                            </td>


                            <td>

                                <div class="customer-name">

                                    <?php
                                    echo htmlspecialchars(
                                        $row["name"]
                                    );
                                    ?>

                                </div>

                            </td>


                            <td>

                                <div class="email">

                                    <?php
                                    echo htmlspecialchars(
                                        $row["email"]
                                    );
                                    ?>

                                </div>

                                <?php if (!empty($row["phone"])): ?>

                                    <small>

                                        <?php
                                        echo htmlspecialchars(
                                            $row["phone"]
                                        );
                                        ?>

                                    </small>

                                <?php endif; ?>

                            </td>


                            <td>

                                <span class="service">

                                    <?php
                                    echo htmlspecialchars(
                                        $row["service"]
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $row["budget"] ?: "-"
                                );
                                ?>

                            </td>


                            <td>

                                <div class="message-text">

                                    <?php
                                    echo htmlspecialchars(
                                        $row["message"]
                                    );
                                    ?>

                                </div>

                            </td>


                            <td>

                                <form
                                    method="POST"
                                    class="status-form"
                                >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?php
                                        echo $row["id"];
                                        ?>"
                                    >


                                    <select
                                        name="status"
                                        class="status-select"
                                        onchange="this.form.submit()"
                                    >

                                        <option
                                            value="New"
                                            <?php
                                            echo $row["status"] === "New"
                                                ? "selected"
                                                : "";
                                            ?>
                                        >
                                            New
                                        </option>

                                        <option
                                            value="Read"
                                            <?php
                                            echo $row["status"] === "Read"
                                                ? "selected"
                                                : "";
                                            ?>
                                        >
                                            Read
                                        </option>

                                        <option
                                            value="Replied"
                                            <?php
                                            echo $row["status"] === "Replied"
                                                ? "selected"
                                                : "";
                                            ?>
                                        >
                                            Replied
                                        </option>

                                    </select>


                                    <input
                                        type="hidden"
                                        name="update_status"
                                        value="1"
                                    >

                                </form>

                            </td>


                            <td>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $row["created_at"]
                                    )
                                );
                                ?>

                            </td>


                            <td>

                                <div class="actions">

                                    <a
    href="message-view.php?id=<?php echo $row["id"]; ?>"
    class="action-btn view-btn"
    title="View Message"
>
    <i class="fa-solid fa-eye"></i>
</a>


                                    <a
                                        href="messages.php?delete=<?php
                                        echo $row["id"];
                                        ?>"
                                        class="action-btn delete-btn"
                                        title="Delete"
                                        onclick="return confirm('Are you sure you want to delete this message?');"
                                    >

                                        <i class="fa-solid fa-trash"></i>

                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <div class="empty">

                    <i class="fa-regular fa-envelope-open"></i>

                    <p>
                        No messages yet.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>


</main>


</body>

</html>

<?php
$conn->close();
?>
<?php

session_start();

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {
    header("Location: admin-login.php");
    exit;
}


// =========================
// DATABASE CONNECTION
// =========================

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


/* Total Blog Views */
$totalBlogViews = 0;

$blogViewsResult = $conn->query(
    "SELECT COALESCE(SUM(views), 0) AS total_views FROM blogs"
);

if ($blogViewsResult) {
    $blogViewsData = $blogViewsResult->fetch_assoc();
    $totalBlogViews = (int) $blogViewsData["total_views"];
}

// =========================
// STATISTICS
// =========================

$total_result = $conn->query(
    "SELECT COUNT(*) AS total FROM messages"
);

$total = $total_result->fetch_assoc()["total"];


$new_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM messages
     WHERE status = 'New'"
);

$new_messages = $new_result->fetch_assoc()["total"];


$read_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM messages
     WHERE status = 'Read'"
);

$read_messages = $read_result->fetch_assoc()["total"];


$replied_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM messages
     WHERE status = 'Replied'"
);

$replied_messages = $replied_result->fetch_assoc()["total"];


// =========================
// RECENT MESSAGES
// =========================

$recent_messages = $conn->query(
    "SELECT *
     FROM messages
     ORDER BY created_at DESC
     LIMIT 5"
);


// =========================
// SERVICE STATISTICS
// =========================

$service_result = $conn->query(
    "SELECT service, COUNT(*) AS total
     FROM messages
     GROUP BY service
     ORDER BY total DESC"
);

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
        Dashboard | Prograte Digital Solutions
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

            margin-bottom: 45px;

            color: #fff;

            text-decoration: none;
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

            gap: 7px;
        }


        .sidebar-menu a {

            display: flex;

            align-items: center;

            gap: 13px;

            padding: 14px 15px;

            color: #999;

            text-decoration: none;

            border-radius: 8px;

            font-size: 13px;

            transition: .3s;
        }


        .sidebar-menu a:hover,
        .sidebar-menu a.active {

            color: #d4af37;

            background:
                rgba(212,175,55,.08);
        }


        .sidebar-menu i {

            width: 18px;

            text-align: center;
        }


        .logout {

            margin-top: 25px !important;

            color: #e57373 !important;

            border-top: 1px solid
                rgba(255,255,255,.06);

            border-radius: 0 !important;

            padding-top: 20px !important;
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

            margin-top: 7px;

            color: #777;

            font-size: 13px;
        }


        .topbar-actions {

            display: flex;

            gap: 10px;
        }


        .top-btn {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding: 11px 16px;

            color: #d4af37;

            border: 1px solid #d4af37;

            border-radius: 7px;

            text-decoration: none;

            font-size: 12px;

            font-weight: 700;

            transition: .3s;
        }


        .top-btn:hover {

            background: #d4af37;

            color: #070707;
        }


        /* =========================
           STATS
        ========================= */

        .stats {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 18px;

            margin-bottom: 28px;
        }


        .stat-card {

            padding: 23px;

            background: #121212;

            border: 1px solid
                rgba(255,255,255,.08);

            border-radius: 12px;

            transition: .3s;
        }


        .stat-card:hover {

            border-color:
                rgba(212,175,55,.35);

            transform: translateY(-2px);
        }


        .stat-card-top {

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 20px;
        }


        .stat-icon {

            width: 44px;
            height: 44px;

            display: flex;

            align-items: center;
            justify-content: center;

            background:
                rgba(212,175,55,.1);

            color: #d4af37;

            border-radius: 9px;

            font-size: 17px;
        }


        .stat-card h2 {

            font-size: 29px;

            margin-bottom: 5px;
        }


        .stat-card p {

            color: #777;

            font-size: 12px;
        }


        /* =========================
           CONTENT GRID
        ========================= */

        .dashboard-grid {

            display: grid;

            grid-template-columns:
                minmax(0, 2fr)
                minmax(280px, 1fr);

            gap: 22px;
        }


        .panel {

            background: #121212;

            border: 1px solid
                rgba(255,255,255,.08);

            border-radius: 12px;

            overflow: hidden;
        }


        .panel-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 20px 22px;

            border-bottom: 1px solid
                rgba(255,255,255,.08);
        }


        .panel-header h2 {

            font-size: 16px;
        }


        .panel-header a {

            color: #d4af37;

            text-decoration: none;

            font-size: 11px;

            font-weight: 700;
        }


        /* =========================
           RECENT MESSAGES
        ========================= */

        .message-list {

            padding: 5px 0;
        }


        .message-item {

            display: flex;

            align-items: center;

            gap: 14px;

            padding: 17px 22px;

            border-bottom: 1px solid
                rgba(255,255,255,.05);

            text-decoration: none;

            transition: .2s;
        }


        .message-item:last-child {

            border-bottom: none;
        }


        .message-item:hover {

            background:
                rgba(255,255,255,.025);
        }


        .message-avatar {

            width: 42px;
            height: 42px;

            flex-shrink: 0;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background:
                rgba(212,175,55,.1);

            color: #d4af37;
        }


        .message-info {

            min-width: 0;

            flex: 1;
        }


        .message-info h3 {

            margin-bottom: 4px;

            color: #eee;

            font-size: 13px;
        }


        .message-info p {

            color: #777;

            font-size: 11px;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;
        }


        .message-meta {

            text-align: right;

            flex-shrink: 0;
        }


        .message-meta span {

            display: block;

            margin-bottom: 5px;

            color: #d4af37;

            font-size: 10px;

            font-weight: 700;
        }


        .message-meta small {

            color: #666;

            font-size: 9px;
        }


        /* =========================
           SERVICE STATS
        ========================= */

        .service-list {

            padding: 18px 22px;
        }


        .service-row {

            margin-bottom: 19px;
        }


        .service-row:last-child {

            margin-bottom: 0;
        }


        .service-name {

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 8px;

            font-size: 11px;

            color: #bbb;
        }


        .service-count {

            color: #d4af37;

            font-weight: 700;
        }


        .progress {

            width: 100%;

            height: 5px;

            overflow: hidden;

            background: #242424;

            border-radius: 20px;
        }


        .progress-bar {

            height: 100%;

            background: #d4af37;

            border-radius: 20px;
        }


        .no-data {

            padding: 30px 20px;

            text-align: center;

            color: #666;

            font-size: 12px;
        }


        /* =========================
           QUICK ACTIONS
        ========================= */

        .quick-actions {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 10px;

            padding: 20px 22px;
        }


        .quick-action {

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            min-height: 70px;

            color: #aaa;

            background: #0d0d0d;

            border: 1px solid
                rgba(255,255,255,.06);

            border-radius: 8px;

            text-decoration: none;

            font-size: 11px;

            font-weight: 600;

            transition: .3s;
        }


        .quick-action i {

            color: #d4af37;

            font-size: 16px;
        }


        .quick-action:hover {

            color: #d4af37;

            border-color:
                rgba(212,175,55,.3);
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

            .dashboard-grid {

                grid-template-columns: 1fr;
            }
        }


        @media (max-width: 700px) {

            .sidebar {

                position: static;

                width: 100%;

                height: auto;

                padding: 20px;

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


            .logout {

                margin-top: 0 !important;

                padding-top: 14px !important;

                border-top: none;
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


            .topbar h1 {

                font-size: 25px;
            }


            .topbar-actions {

                width: 100%;
            }


            .top-btn {

                flex: 1;

                justify-content: center;
            }
        }


        @media (max-width: 450px) {

            .stats {

                grid-template-columns: 1fr;
            }


            .stat-card {

                padding: 20px;
            }


            .message-item {

                padding: 15px;
            }


            .message-meta {

                display: none;
            }


            .quick-actions {

                grid-template-columns: 1fr;
            }

        }



        .dashboard-section {
    background: var(--dark-2);
    border: 1px solid var(--border);
    padding: 30px;
    margin-top: 30px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

.section-header h2 {
    margin: 8px 0 0;
    color: var(--light);
}

.section-header h2 span {
    color: var(--gold);
}

.view-all-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--gold);
    text-decoration: none;
    font-size: 14px;
}

.blog-views-list {
    display: flex;
    flex-direction: column;
}

.blog-view-item {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 18px 0;
    border-bottom: 1px solid var(--border);
}

.blog-view-item:last-child {
    border-bottom: none;
}

.blog-view-icon {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(212, 175, 55, 0.08);
    border: 1px solid rgba(212, 175, 55, 0.25);
    color: var(--gold);
    flex-shrink: 0;
}

.blog-view-info {
    flex: 1;
    min-width: 0;
}

.blog-view-info h3 {
    color: var(--light);
    font-size: 15px;
    margin: 0 0 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.blog-view-info span {
    color: var(--silver);
    font-size: 12px;
}

.blog-view-count {
    text-align: right;
    min-width: 80px;
}

.blog-view-count strong {
    display: block;
    color: var(--gold);
    font-size: 20px;
}

.blog-view-count small {
    color: var(--silver);
    font-size: 11px;
}

.blog-view-count i {
    color: var(--gold);
}

.no-blog-views {
    text-align: center;
    padding: 40px 20px;
    color: var(--silver);
}

.no-blog-views i {
    font-size: 35px;
    color: var(--gold);
    margin-bottom: 12px;
}

.no-blog-views p {
    margin: 0;
}

@media (max-width: 600px) {

    .dashboard-section {
        padding: 20px;
    }

    .section-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .blog-view-item {
        gap: 12px;
    }

    .blog-view-icon {
        width: 38px;
        height: 38px;
    }

    .blog-view-info h3 {
        font-size: 13px;
    }

    .blog-view-count {
        min-width: 60px;
    }

    .blog-view-count strong {
        font-size: 17px;
    }
}

    </style>

</head>


<body>


<!-- =========================
     SIDEBAR
========================= -->

<aside class="sidebar">

    <a
        href="admin-dashboard.php"
        class="brand"
    >

        <span>PROGRATE</span>

        <small>
            DIGITAL SOLUTIONS
        </small>

    </a>


    <nav class="sidebar-menu">

        <a
            href="admin-dashboard.php"
            class="active"
        >

            <i class="fa-solid fa-chart-line"></i>

            Dashboard

        </a>


        <a href="messages.php">

            <i class="fa-solid fa-envelope"></i>

            Messages

        </a>


        <a href="contact.php">

            <i class="fa-solid fa-globe"></i>

            Website

        </a>


        <a
            href="admin-logout.php"
            class="logout"
        >

            <i class="fa-solid fa-right-from-bracket"></i>

            Logout

        </a>

    </nav>

</aside>


<!-- =========================
     MAIN
========================= -->

<main class="main">


    <div class="topbar">

        <div>

            <h1>
                Dashboard
            </h1>

            <p>
                Welcome back, <?php
                echo htmlspecialchars(
                    $_SESSION["admin_username"] ?? "Admin"
                );
                ?>.
                Here's what's happening with your inquiries.
            </p>

        </div>


        <div class="topbar-actions">

            <a
                href="messages.php"
                class="top-btn"
            >

                <i class="fa-solid fa-envelope"></i>

                View Messages

            </a>

        </div>

    </div>


    <!-- =========================
         STAT CARDS
    ========================= -->

    <div class="stats">


        <div class="stat-card">

            <div class="stat-card-top">

                <div>

                    <h2>
                        <?php echo $total; ?>
                    </h2>

                    <p>
                        Total Inquiries
                    </p>

                </div>


                <div class="stat-icon">

                    <i class="fa-solid fa-envelope"></i>

                </div>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-card-top">

                <div>

                    <h2>
                        <?php echo $new_messages; ?>
                    </h2>

                    <p>
                        New Inquiries
                    </p>

                </div>


                <div class="stat-icon">

                    <i class="fa-solid fa-bell"></i>

                </div>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-card-top">

                <div>

                    <h2>
                        <?php echo $read_messages; ?>
                    </h2>

                    <p>
                        Read
                    </p>

                </div>


                <div class="stat-icon">

                    <i class="fa-solid fa-eye"></i>

                </div>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-card-top">

                <div>

                    <h2>
                        <?php echo $replied_messages; ?>
                    </h2>

                    <p>
                        Replied
                    </p>

                </div>


                <div class="stat-icon">

                    <i class="fa-solid fa-check"></i>

                </div>

            </div>

        </div>


        <div class="stat-card">
    <div class="stat-icon">
        <i class="fa-solid fa-eye"></i>
    </div>

    <div class="stat-info">
        <span>Total Blog Views</span>

        <h3>
            <?php echo number_format($totalBlogViews); ?>
        </h3>
    </div>
</div>


    </div>



    <?php

$mostViewedBlogs = $conn->query("
    SELECT id, title, category, views, status
    FROM blogs
    ORDER BY views DESC
    LIMIT 5
");

?>

<div class="dashboard-section">

    <div class="section-header">
        <div>
            <span class="section-tag">BLOG ANALYTICS</span>
            <h2>Most Viewed <span>Blogs</span></h2>
        </div>

        <a href="blogs.php" class="view-all-btn">
            Manage Blogs
            <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <div class="blog-views-list">

        <?php if ($mostViewedBlogs && $mostViewedBlogs->num_rows > 0): ?>

            <?php while ($blog = $mostViewedBlogs->fetch_assoc()): ?>

                <div class="blog-view-item">

                    <div class="blog-view-icon">
                        <i class="fa-solid fa-newspaper"></i>
                    </div>

                    <div class="blog-view-info">

                        <h3>
                            <?php echo htmlspecialchars($blog["title"]); ?>
                        </h3>

                        <span>
                            <?php echo htmlspecialchars($blog["category"]); ?>
                        </span>

                    </div>

                    <div class="blog-view-count">

                        <strong>
                            <?php echo number_format((int) $blog["views"]); ?>
                        </strong>

                        <small>
                            <i class="fa-solid fa-eye"></i>
                            Views
                        </small>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="no-blog-views">
                <i class="fa-solid fa-chart-line"></i>
                <p>No blog views yet.</p>
            </div>

        <?php endif; ?>

    </div>

</div>


    <!-- =========================
         DASHBOARD GRID
    ========================= -->

    <div class="dashboard-grid">


        <!-- RECENT MESSAGES -->

        <div class="panel">

            <div class="panel-header">

                <h2>
                    Recent Inquiries
                </h2>

                <a href="messages.php">

                    View All

                    <i class="fa-solid fa-arrow-right"></i>

                </a>

            </div>


            <div class="message-list">

                <?php if ($recent_messages->num_rows > 0): ?>

                    <?php
                    while (
                        $row =
                        $recent_messages->fetch_assoc()
                    ):
                    ?>

                        <a
                            href="message-view.php?id=<?php
                            echo $row["id"];
                            ?>"
                            class="message-item"
                        >

                            <div class="message-avatar">

                                <i class="fa-solid fa-user"></i>

                            </div>


                            <div class="message-info">

                                <h3>

                                    <?php
                                    echo htmlspecialchars(
                                        $row["name"]
                                    );
                                    ?>

                                </h3>

                                <p>

                                    <?php
                                    echo htmlspecialchars(
                                        $row["message"]
                                    );
                                    ?>

                                </p>

                            </div>


                            <div class="message-meta">

                                <span>

                                    <?php
                                    echo htmlspecialchars(
                                        $row["status"]
                                    );
                                    ?>

                                </span>

                                <small>

                                    <?php
                                    echo date(
                                        "d M",
                                        strtotime(
                                            $row["created_at"]
                                        )
                                    );
                                    ?>

                                </small>

                            </div>

                        </a>

                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="no-data">

                        <i class="fa-regular fa-envelope-open"></i>

                        <br><br>

                        No inquiries yet.

                    </div>

                <?php endif; ?>

            </div>

        </div>


        <!-- RIGHT SIDE -->

        <div>


            <!-- SERVICE STATS -->

            <div class="panel">

                <div class="panel-header">

                    <h2>
                        Services
                    </h2>

                </div>


                <div class="service-list">

                    <?php

                    $service_data = [];

                    $max_service = 1;

                    while (
                        $row =
                        $service_result->fetch_assoc()
                    ) {

                        $service_data[] = $row;

                        if (
                            $row["total"] > $max_service
                        ) {
                            $max_service =
                                $row["total"];
                        }
                    }

                    ?>


                    <?php if (!empty($service_data)): ?>

                        <?php foreach (
                            $service_data
                            as $service
                        ): ?>

                            <?php

                            $percentage =
                                (
                                    $service["total"]
                                    /
                                    $max_service
                                ) * 100;

                            ?>

                            <div class="service-row">

                                <div class="service-name">

                                    <span>

                                        <?php
                                        echo htmlspecialchars(
                                            $service["service"]
                                        );
                                        ?>

                                    </span>

                                    <span class="service-count">

                                        <?php
                                        echo $service["total"];
                                        ?>

                                    </span>

                                </div>


                                <div class="progress">

                                    <div
                                        class="progress-bar"
                                        style="width: <?php
                                        echo $percentage;
                                        ?>%;"
                                    ></div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <div class="no-data">

                            No service data available.

                        </div>

                    <?php endif; ?>

                </div>

            </div>


            <!-- QUICK ACTIONS -->

            <div
                class="panel"
                style="margin-top: 22px;"
            >

                <div class="panel-header">

                    <h2>
                        Quick Actions
                    </h2>

                </div>


                <div class="quick-actions">

                    <a
                        href="messages.php"
                        class="quick-action"
                    >

                        <i class="fa-solid fa-envelope"></i>

                        Messages

                    </a>


                    <a
                        href="contact.php"
                        class="quick-action"
                    >

                        <i class="fa-solid fa-globe"></i>

                        Website

                    </a>


                    <a
                        href="index.php"
                        class="quick-action"
                    >

                        <i class="fa-solid fa-house"></i>

                        Home

                    </a>

                    


                    <a
                        href="admin-logout.php"
                        class="quick-action"
                    >

                        <i class="fa-solid fa-right-from-bracket"></i>

                        Logout

                    </a>

                   <a href="add-blog.php" class="quick-action">
    <i class="fa-solid fa-pen-to-square"></i>

    <div>
        <strong>Add New Blog</strong>
        <span>Create a new article</span>
    </div>

    <i class="fa-solid fa-arrow-right"></i>
</a>

<a href="blogs.php" class="quick-action">
    <i class="fa-solid fa-newspaper"></i>

    <div>
        <strong>Manage Blogs</strong>
        <span>Edit, delete and manage articles</span>
    </div>

    <i class="fa-solid fa-arrow-right"></i>
</a>

                </div>

            </div>


        </div>


    </div>


</main>


</body>

</html>

<?php
$conn->close();
?>
<?php

session_start();

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {
    header("Location: admin-login.php");
    exit;
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION["csrf_token"];


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


/*
|--------------------------------------------------------------------------
| Search & Filters
|--------------------------------------------------------------------------
*/

$search = trim($_GET["search"] ?? "");
$status_filter = $_GET["status"] ?? "";
$category_filter = $_GET["category"] ?? "";

$sql = "SELECT * FROM blogs WHERE 1=1";

$params = [];
$types = "";

if ($search !== "") {

    $sql .= " AND (
        title LIKE ?
        OR category LIKE ?
    )";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "ss";
}

if (
    in_array(
        $status_filter,
        ["Draft", "Published"],
        true
    )
) {

    $sql .= " AND status = ?";

    $params[] = $status_filter;

    $types .= "s";
}

if ($category_filter !== "") {

    $sql .= " AND category = ?";

    $params[] = $category_filter;

    $types .= "s";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param(
        $types,
        ...$params
    );
}

$stmt->execute();

$blogs = $stmt->get_result();


/*
|--------------------------------------------------------------------------
| Stats
|--------------------------------------------------------------------------
*/

$total_result = $conn->query(
    "SELECT COUNT(*) AS total FROM blogs"
);

$total_blogs = $total_result->fetch_assoc()["total"];


$published_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM blogs
     WHERE status = 'Published'"
);

$published_blogs =
    $published_result->fetch_assoc()["total"];


$draft_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM blogs
     WHERE status = 'Draft'"
);

$draft_blogs =
    $draft_result->fetch_assoc()["total"];


/*
|--------------------------------------------------------------------------
| Categories
|--------------------------------------------------------------------------
*/

$categories_result = $conn->query(
    "SELECT DISTINCT category
     FROM blogs
     ORDER BY category ASC"
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

    <title>Manage Blogs | Prograte Admin</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Inter", sans-serif;
            background: #070707;
            color: #f2f2f2;
        }

        .admin-page {
            min-height: 100vh;
            padding: 35px 20px;
        }

        .admin-container {
            max-width: 1250px;
            margin: auto;
        }


        /* TOPBAR */

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 30px;
        }

        .topbar h1 {
            font-size: 30px;
        }

        .topbar h1 span {
            color: #d4af37;
        }

        .top-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 18px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
            font-weight: 700;
            font-size: 13px;
        }

        .btn-primary {
            background: #d4af37;
            color: #000;
        }

        .btn-primary:hover {
            background: #f0d477;
        }

        .btn-secondary {
            background: #1b1b1b;
            color: #ddd;
            border: 1px solid rgba(255,255,255,.1);
        }


        /* STATS */

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: #111;
            border: 1px solid rgba(255,255,255,.08);
            padding: 22px;
        }

        .stat-card small {
            color: #888;
            display: block;
            margin-bottom: 8px;
        }

        .stat-card strong {
            font-size: 30px;
        }

        .stat-card strong span {
            color: #d4af37;
        }


        /* FILTERS */

        .filters {
            background: #111;
            border: 1px solid rgba(255,255,255,.08);
            padding: 20px;
            margin-bottom: 25px;

            display: grid;
            grid-template-columns: 1fr 200px 200px auto;
            gap: 12px;
        }

        .filters input,
        .filters select {
            width: 100%;
            background: #080808;
            color: #fff;
            border: 1px solid rgba(255,255,255,.12);
            padding: 13px;
            outline: none;
        }

        .filters input:focus,
        .filters select:focus {
            border-color: #d4af37;
        }


        /* TABLE */

        .table-box {
            background: #111;
            border: 1px solid rgba(255,255,255,.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th {
            text-align: left;
            padding: 16px;
            color: #888;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        td {
            padding: 16px;
            border-bottom: 1px solid rgba(255,255,255,.06);
            vertical-align: middle;
        }

        tbody tr:hover {
            background: rgba(255,255,255,.02);
        }


        /* BLOG */

        .blog-info {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .blog-thumb {
            width: 70px;
            height: 50px;
            object-fit: cover;
            background: #1b1b1b;
        }

        .blog-title {
            max-width: 320px;
        }

        .blog-title strong {
            display: block;
            margin-bottom: 5px;
        }

        .blog-title small {
            color: #777;
        }

        .category {
            color: #d4af37;
            font-size: 12px;
            font-weight: 700;
        }


        /* STATUS */

        .status {
            display: inline-block;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 700;
        }

        .status.published {
            background: rgba(80,200,120,.1);
            color: #70db8c;
        }

        .status.draft {
            background: rgba(255,190,50,.1);
            color: #e8c65c;
        }


        /* ACTIONS */

        .actions {
            display: flex;
            gap: 7px;
        }

        .action-btn {
            width: 36px;
            height: 36px;

            display: flex;
            align-items: center;
            justify-content: center;

            color: #bbb;
            background: #1b1b1b;
            border: 1px solid rgba(255,255,255,.08);

            text-decoration: none;
            cursor: pointer;
        }

        .action-btn:hover {
            color: #d4af37;
            border-color: #d4af37;
        }

        .delete-btn:hover {
            color: #ff7777;
            border-color: #ff7777;
        }


        /* EMPTY */

        .empty {
            text-align: center;
            padding: 70px 20px;
            color: #777;
        }

        .empty i {
            font-size: 40px;
            color: #d4af37;
            margin-bottom: 15px;
        }


        @media (max-width: 900px) {

            .filters {
                grid-template-columns: 1fr 1fr;
            }

        }


        @media (max-width: 650px) {

            .admin-page {
                padding: 25px 12px;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .filters {
                grid-template-columns: 1fr;
            }

            .top-actions {
                width: 100%;
            }

            .top-actions .btn {
                flex: 1;
                justify-content: center;
            }

        }

        .views-cell {
    color: var(--gold);
    font-weight: 600;
    white-space: nowrap;
}

.views-cell i {
    margin-right: 5px;
    font-size: 13px;
}

    </style>

</head>

<body>

<div class="admin-page">

<div class="admin-container">


    <!-- TOPBAR -->

    <div class="topbar">

        <h1>
            Manage <span>Blogs</span>
        </h1>

        <div class="top-actions">

            <a
                href="admin-dashboard.php"
                class="btn btn-secondary"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Dashboard
            </a>

            <a
                href="add-blog.php"
                class="btn btn-primary"
            >
                <i class="fa-solid fa-plus"></i>
                Add Blog
            </a>

        </div>

    </div>


    <!-- STATS -->

    <div class="stats">

        <div class="stat-card">

            <small>
                Total Blogs
            </small>

            <strong>
                <?php echo $total_blogs; ?>
            </strong>

        </div>


        <div class="stat-card">

            <small>
                Published
            </small>

            <strong>
                <span>
                    <?php echo $published_blogs; ?>
                </span>
            </strong>

        </div>


        <div class="stat-card">

            <small>
                Drafts
            </small>

            <strong>
                <?php echo $draft_blogs; ?>
            </strong>

        </div>

    </div>


    <!-- FILTERS -->

    <form
        method="GET"
        class="filters"
    >

        <input
            type="text"
            name="search"
            placeholder="Search blog title or category..."
            value="<?php echo htmlspecialchars($search); ?>"
        >


        <select name="status">

            <option value="">
                All Status
            </option>

            <option
                value="Published"
                <?php echo $status_filter === "Published" ? "selected" : ""; ?>
            >
                Published
            </option>

            <option
                value="Draft"
                <?php echo $status_filter === "Draft" ? "selected" : ""; ?>
            >
                Draft
            </option>

        </select>


        <select name="category">

            <option value="">
                All Categories
            </option>

            <?php while ($cat = $categories_result->fetch_assoc()): ?>

                <option
                    value="<?php echo htmlspecialchars($cat["category"]); ?>"
                    <?php
                    echo $category_filter === $cat["category"]
                        ? "selected"
                        : "";
                    ?>
                >
                    <?php echo htmlspecialchars($cat["category"]); ?>
                </option>

            <?php endwhile; ?>

        </select>


        <button
            type="submit"
            class="btn btn-primary"
        >
            <i class="fa-solid fa-filter"></i>
            Filter
        </button>

    </form>


    <!-- TABLE -->

    <div class="table-box">

        <?php if ($blogs->num_rows > 0): ?>

            <table>

                <thead>

                    <tr>

                        <th>
                            Blog
                        </th>

                        <th>
                            Category
                        </th>

                        <th>
                            Status
                        </th>

                        

                        <th>
                            Date
                        </th>

                        <th>
                            Actions
                        </th>

                        <th>
                            Views
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php while ($blog = $blogs->fetch_assoc()): ?>

                        <tr>

                            <td>

                                <div class="blog-info">

                                    <?php if (!empty($blog["image"])): ?>

                                        <img
                                            src="<?php echo htmlspecialchars($blog["image"]); ?>"
                                            class="blog-thumb"
                                            alt=""
                                        >

                                    <?php else: ?>

                                        <div class="blog-thumb">
                                        </div>

                                    <?php endif; ?>


                                    <div class="blog-title">

                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $blog["title"]
                                            );
                                            ?>
                                        </strong>

                                        <small>
                                            /<?php
                                            echo htmlspecialchars(
                                                $blog["slug"]
                                            );
                                            ?>
                                        </small>

                                    </div>

                                </div>

                            </td>


                            <td>

                                <span class="category">

                                    <?php
                                    echo htmlspecialchars(
                                        $blog["category"]
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <span
                                    class="status <?php echo strtolower($blog["status"]); ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $blog["status"]
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <?php
                                echo date(
                                    "M d, Y",
                                    strtotime($blog["created_at"])
                                );
                                ?>

                            </td>


                            <td>

                                <div class="actions">


                                    <!-- VIEW -->

                                    <?php if ($blog["status"] === "Published"): ?>

                                        <a
    href="blog/<?php echo urlencode($blog["slug"]); ?>"
    target="_blank"
    class="action-btn"
    title="View"
>
    <i class="fa-solid fa-eye"></i>
</a>

                                    <?php endif; ?>


                                    <!-- EDIT -->

                                    <a
                                        href="edit-blog.php?id=<?php echo $blog["id"]; ?>"
                                        class="action-btn"
                                        title="Edit"
                                    >
                                        <i class="fa-solid fa-pen"></i>
                                    </a>


                                    <!-- DELETE -->

                                    <form
                                        action="delete-blog.php"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this blog?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?php echo $blog["id"]; ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?php echo htmlspecialchars($csrf_token); ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="action-btn delete-btn"
                                            title="Delete"
                                        >
                                            <i class="fa-solid fa-trash"></i>
                                        </button>

                                    </form>

                                </div>

                            </td>

                            <td class="views-cell">
    <i class="fa-solid fa-eye"></i>
    <?php echo number_format((int) $blog["views"]); ?>
</td>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="empty">

                <i class="fa-solid fa-newspaper"></i>

                <h3>
                    No Blogs Found
                </h3>

                <p>
                    Create your first blog article.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

</div>

</body>

</html>

<?php

$stmt->close();
$conn->close();

?>
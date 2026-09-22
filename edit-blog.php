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
| Get Blog ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id) {
    header("Location: blogs.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Existing Blog
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT * FROM blogs WHERE id = ? LIMIT 1"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();
    $conn->close();

    die("Blog not found.");
}

$blog = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Update Blog
|--------------------------------------------------------------------------
*/

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf_token"]) ||
        !isset($_SESSION["csrf_token"]) ||
        !hash_equals(
            $_SESSION["csrf_token"],
            $_POST["csrf_token"]
        )
    ) {
        die("Invalid security token.");
    }


    $title = trim($_POST["title"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $excerpt = trim($_POST["excerpt"] ?? "");
    $content = trim($_POST["content"] ?? "");
    $seo_title = trim($_POST["seo_title"] ?? "");
    $meta_description = trim($_POST["meta_description"] ?? "");
    $status = $_POST["status"] ?? "Draft";


    if (
        $title === "" ||
        $category === "" ||
        $content === ""
    ) {

        $message = "Title, category and content are required.";
        $message_type = "error";

    } elseif (
        !in_array(
            $status,
            ["Draft", "Published"],
            true
        )
    ) {

        $message = "Invalid status.";
        $message_type = "error";

    } else {


        /*
        |--------------------------------------------------------------------------
        | Generate Slug
        |--------------------------------------------------------------------------
        */

        $slug = strtolower($title);

        $slug = preg_replace(
            "/[^a-z0-9]+/",
            "-",
            $slug
        );

        $slug = trim($slug, "-");


        /*
        |--------------------------------------------------------------------------
        | Check Duplicate Slug
        |--------------------------------------------------------------------------
        */

        $check = $conn->prepare(
            "SELECT id
             FROM blogs
             WHERE slug = ?
             AND id != ?
             LIMIT 1"
        );

        $check->bind_param(
            "si",
            $slug,
            $id
        );

        $check->execute();

        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            $slug .= "-" . time();
        }

        $check->close();


        /*
        |--------------------------------------------------------------------------
        | Existing Image
        |--------------------------------------------------------------------------
        */

        $image_path = $blog["image"];


        /*
        |--------------------------------------------------------------------------
        | New Image Upload
        |--------------------------------------------------------------------------
        */

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] === UPLOAD_ERR_OK
        ) {

            $allowed_types = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];

            $file_type = mime_content_type(
                $_FILES["image"]["tmp_name"]
            );


            if (
                !in_array(
                    $file_type,
                    $allowed_types,
                    true
                )
            ) {

                $message =
                    "Only JPG, PNG and WEBP images are allowed.";

                $message_type = "error";

            } elseif (
                $_FILES["image"]["size"] >
                5 * 1024 * 1024
            ) {

                $message =
                    "Image size must be less than 5MB.";

                $message_type = "error";

            } else {

                $upload_dir = "assets/blog/";

                if (!is_dir($upload_dir)) {

                    mkdir(
                        $upload_dir,
                        0755,
                        true
                    );
                }


                $extension = strtolower(
                    pathinfo(
                        $_FILES["image"]["name"],
                        PATHINFO_EXTENSION
                    )
                );


                $file_name =
                    $slug .
                    "-" .
                    time() .
                    "." .
                    $extension;


                $target_file =
                    $upload_dir .
                    $file_name;


                if (
                    move_uploaded_file(
                        $_FILES["image"]["tmp_name"],
                        $target_file
                    )
                ) {

                    /*
                     * Delete old image
                     */

                    if (
                        !empty($blog["image"]) &&
                        file_exists($blog["image"])
                    ) {
                        unlink($blog["image"]);
                    }


                    $image_path =
                        $target_file;

                } else {

                    $message =
                        "Unable to upload image.";

                    $message_type =
                        "error";
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Update Database
        |--------------------------------------------------------------------------
        */

        if ($message_type !== "error") {

            $update = $conn->prepare(
                "UPDATE blogs SET
                    title = ?,
                    slug = ?,
                    category = ?,
                    image = ?,
                    excerpt = ?,
                    content = ?,
                    seo_title = ?,
                    meta_description = ?,
                    status = ?
                 WHERE id = ?"
            );


            $update->bind_param(
                "sssssssssi",
                $title,
                $slug,
                $category,
                $image_path,
                $excerpt,
                $content,
                $seo_title,
                $meta_description,
                $status,
                $id
            );


            if ($update->execute()) {

                $message =
                    "Blog updated successfully.";

                $message_type =
                    "success";


                /*
                 * Refresh blog data
                 */

                $blog["title"] =
                    $title;

                $blog["slug"] =
                    $slug;

                $blog["category"] =
                    $category;

                $blog["image"] =
                    $image_path;

                $blog["excerpt"] =
                    $excerpt;

                $blog["content"] =
                    $content;

                $blog["seo_title"] =
                    $seo_title;

                $blog["meta_description"] =
                    $meta_description;

                $blog["status"] =
                    $status;

            } else {

                $message =
                    "Unable to update blog.";

                $message_type =
                    "error";
            }

            $update->close();
        }
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

    <title>
        Edit Blog | Prograte Admin
    </title>


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
            padding: 40px 20px;
        }

        .admin-container {
            max-width: 1100px;
            margin: auto;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 35px;
        }

        .topbar h1 {
            font-size: 30px;
        }

        .topbar h1 span {
            color: #d4af37;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 18px;
            border: 1px solid rgba(255,255,255,.1);
            color: #ddd;
            text-decoration: none;
        }

        .back-btn:hover {
            border-color: #d4af37;
            color: #d4af37;
        }

        .form-card {
            background: #111;
            border: 1px solid rgba(255,255,255,.08);
            padding: 35px;
        }

        .alert {
            padding: 15px 18px;
            margin-bottom: 25px;
            border: 1px solid;
        }

        .alert.success {
            color: #8ee7a1;
            border-color: rgba(80,200,120,.3);
            background: rgba(80,200,120,.08);
        }

        .alert.error {
            color: #ff8585;
            border-color: rgba(255,80,80,.3);
            background: rgba(255,80,80,.08);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 9px;
            color: #ddd;
            font-size: 14px;
            font-weight: 600;
        }

        input,
        select,
        textarea {
            width: 100%;
            background: #080808;
            color: #fff;
            border: 1px solid rgba(255,255,255,.12);
            padding: 14px 15px;
            font-family: inherit;
            font-size: 14px;
            outline: none;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #d4af37;
        }

        textarea {
            resize: vertical;
            line-height: 1.7;
        }

        .content-editor {
            min-height: 350px;
        }

        .current-image {
            margin-bottom: 15px;
        }

        .current-image img {
            width: 220px;
            height: 130px;
            object-fit: cover;
            display: block;
            border: 1px solid rgba(255,255,255,.1);
        }

        .image-upload {
            border: 1px dashed rgba(212,175,55,.4);
            padding: 20px;
            background: rgba(212,175,55,.03);
        }

        .image-upload input {
            border: none;
            background: transparent;
            padding: 8px 0;
        }

        .help-text {
            color: #777;
            font-size: 12px;
            margin-top: 7px;
        }

        .submit-row {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 10px;
            padding-top: 25px;
            border-top: 1px solid rgba(255,255,255,.08);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 14px 24px;
            border: none;
            font-family: inherit;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #d4af37;
            color: #000;
        }

        .btn-primary:hover {
            background: #f0d477;
        }

        .btn-secondary {
            background: #222;
            color: #ddd;
        }

        @media (max-width: 700px) {

            .admin-page {
                padding: 25px 15px;
            }

            .form-card {
                padding: 22px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .full {
                grid-column: auto;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .submit-row {
                flex-direction: column;
            }

            .current-image img {
                width: 100%;
                height: auto;
            }

        }

    </style>

<link
    href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css"
    rel="stylesheet"
>

</head>


<body>


<div class="admin-page">

    <div class="admin-container">


        <div class="topbar">

            <h1>
                Edit <span>Blog</span>
            </h1>


            <a
                href="blogs.php"
                class="back-btn"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Back to Blogs
            </a>

        </div>


        <?php if ($message !== ""): ?>

            <div
                class="alert <?php echo $message_type; ?>"
            >
                <?php
                echo htmlspecialchars($message);
                ?>
            </div>

        <?php endif; ?>


        <div class="form-card">

            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars($csrf_token); ?>"
                >


                <div class="form-grid">


                    <!-- TITLE -->

                    <div class="form-group full">

                        <label for="title">
                            Blog Title *
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="<?php echo htmlspecialchars($blog["title"]); ?>"
                            required
                        >

                    </div>


                    <!-- CATEGORY -->

                    <div class="form-group">

                        <label for="category">
                            Category *
                        </label>

                        <select
                            id="category"
                            name="category"
                            required
                        >

                            <?php

                            $categories = [
                                "Web Development",
                                "Mobile Apps",
                                "Software",
                                "E-Commerce",
                                "Digital Marketing",
                                "SEO",
                                "Technology",
                                "Business"
                            ];

                            ?>

                            <?php foreach ($categories as $cat): ?>

                                <option
                                    value="<?php echo htmlspecialchars($cat); ?>"
                                    <?php
                                    echo $blog["category"] === $cat
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    <?php
                                    echo htmlspecialchars($cat);
                                    ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- STATUS -->

                    <div class="form-group">

                        <label for="status">
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                        >

                            <option
                                value="Draft"
                                <?php
                                echo $blog["status"] === "Draft"
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Draft
                            </option>

                            <option
                                value="Published"
                                <?php
                                echo $blog["status"] === "Published"
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Published
                            </option>

                        </select>

                    </div>


                    <!-- IMAGE -->

                    <div class="form-group full">

                        <label>
                            Featured Image
                        </label>


                        <?php if (!empty($blog["image"])): ?>

                            <div class="current-image">

                                <img
                                    src="<?php echo htmlspecialchars($blog["image"]); ?>"
                                    alt=""
                                >

                            </div>

                        <?php endif; ?>


                        <div class="image-upload">

                            <input
                                type="file"
                                name="image"
                                accept=".jpg,.jpeg,.png,.webp"
                            >

                            <p class="help-text">
                                Leave empty to keep the current image.
                                JPG, PNG or WEBP — Maximum 5MB.
                            </p>

                        </div>

                    </div>


                    <!-- EXCERPT -->

                    <div class="form-group full">

                        <label for="excerpt">
                            Short Excerpt
                        </label>

                        <textarea
                            id="excerpt"
                            name="excerpt"
                            rows="4"
                        ><?php
                        echo htmlspecialchars(
                            $blog["excerpt"] ?? ""
                        );
                        ?></textarea>

                    </div>


                    <!-- CONTENT -->

                    <div class="form-group full">

                        <label for="content">
                            Blog Content *
                        </label>

                                 <div id="content-editor"></div>

<textarea
    id="content"
    name="content"
    style="display:none;"
    required
></textarea>

                    </div>


                    <!-- SEO TITLE -->

                    <div class="form-group">

                        <label for="seo_title">
                            SEO Title
                        </label>

                        <input
                            type="text"
                            id="seo_title"
                            name="seo_title"
                            maxlength="255"
                            value="<?php echo htmlspecialchars($blog["seo_title"] ?? ""); ?>"
                        >

                    </div>


                    <!-- META DESCRIPTION -->

                    <div class="form-group">

                        <label for="meta_description">
                            Meta Description
                        </label>

                        <textarea
                            id="meta_description"
                            name="meta_description"
                            rows="4"
                            maxlength="320"
                        ><?php
                        echo htmlspecialchars(
                            $blog["meta_description"] ?? ""
                        );
                        ?></textarea>

                    </div>


                </div>


                <div class="submit-row">

                    <a
                        href="blogs.php"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="fa-solid fa-save"></i>

                        Update Blog

                    </button>

                </div>


            </form>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const editor = document.getElementById("content-editor");
    const hiddenContent = document.getElementById("content");

    if (!editor || !hiddenContent) {
        console.log("Editor elements not found");
        return;
    }

    const quill = new Quill("#content-editor", {
        theme: "snow",

        placeholder: "Write your complete blog article here...",

        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ["bold", "italic", "underline", "strike"],
                [{ list: "ordered" }, { list: "bullet" }],
                [{ align: [] }],
                ["blockquote", "code-block"],
                ["link"],
                ["clean"]
            ]
        }
    });

    // Existing PHP blog content
    const existingContent = <?php echo json_encode($blog["content"] ?? ""); ?>;

    if (existingContent.trim() !== "") {
        quill.root.innerHTML = existingContent;
        hiddenContent.value = existingContent;
    }

    // Save editor content before form submit
    const form = document.querySelector("form");

    if (form) {
        form.addEventListener("submit", function () {
            hiddenContent.value = quill.root.innerHTML;
        });
    }

});
</script>

</body>

</html>
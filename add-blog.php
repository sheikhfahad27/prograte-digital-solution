<?php

session_start();

/* =========================
   ADMIN LOGIN CHECK
========================= */

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {
    header("Location: admin-login.php");
    exit;
}


/* =========================
   DATABASE
========================= */

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
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");


$message = "";
$message_type = "";


/* =========================
   FORM SUBMIT
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $slug = trim($_POST["slug"] ?? "");
    $excerpt = trim($_POST["excerpt"] ?? "");
    $content = trim($_POST["content"] ?? "");
    $seo_title = trim($_POST["seo_title"] ?? "");
    $meta_description = trim($_POST["meta_description"] ?? "");
    $status = $_POST["status"] ?? "Draft";


    /* =========================
       VALIDATION
    ========================= */

    if ($title === "") {

        $message = "Blog title is required.";
        $message_type = "error";

    } elseif ($category === "") {

        $message = "Please select a category.";
        $message_type = "error";

    } elseif (
        $content === "" ||
        trim(strip_tags($content)) === ""
    ) {

        $message = "Blog content is required.";
        $message_type = "error";

    } elseif (
        !in_array(
            $status,
            ["Draft", "Published"],
            true
        )
    ) {

        $message = "Invalid blog status.";
        $message_type = "error";

    } else {


        /* =========================
           AUTO GENERATE SLUG
        ========================= */

        $slug = strtolower($title);

        $slug = preg_replace(
            "/[^a-z0-9]+/",
            "-",
            $slug
        );

        $slug = trim(
            $slug,
            "-"
        );


        /* =========================
           CHECK SLUG
        ========================= */

        $check = $conn->prepare(
            "SELECT id FROM blogs WHERE slug = ? LIMIT 1"
        );

        if (!$check) {

            $message =
                "Database error: " . $conn->error;

            $message_type = "error";

        } else {

            $check->bind_param(
                "s",
                $slug
            );

            $check->execute();

            $result = $check->get_result();

            if ($result->num_rows > 0) {

                $slug =
                    $slug . "-" . time();
            }

            $check->close();
        }


        /* =========================
           IMAGE
        ========================= */

        $image_path = null;


        if (
            $message_type !== "error" &&
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {


            if (
                $_FILES["image"]["error"] !== UPLOAD_ERR_OK
            ) {

                $message =
                    "Image upload failed.";

                $message_type = "error";

            } else {


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
                        "Image must be less than 5MB.";

                    $message_type = "error";

                } else {


                    $upload_dir =
                        __DIR__ . "/assets/blog/";


                    if (!is_dir($upload_dir)) {

                        if (
                            !mkdir(
                                $upload_dir,
                                0755,
                                true
                            )
                        ) {

                            $message =
                                "Unable to create image folder.";

                            $message_type = "error";
                        }
                    }


                    if (
                        $message_type !== "error"
                    ) {


                        $extension =
                            strtolower(
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

                            $image_path =
                                "assets/blog/" .
                                $file_name;

                        } else {

                            $message =
                                "Unable to save uploaded image.";

                            $message_type = "error";
                        }
                    }
                }
            }
        }


        /* =========================
           INSERT BLOG
        ========================= */

        if (
            $message_type !== "error"
        ) {


            $stmt = $conn->prepare(
                "INSERT INTO blogs
                (
                    title,
                    slug,
                    category,
                    image,
                    excerpt,
                    content,
                    seo_title,
                    meta_description,
                    status
                )
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );


            if (!$stmt) {

                $message =
                    "Database error: " . $conn->error;

                $message_type = "error";

            } else {


                $stmt->bind_param(
                    "sssssssss",
                    $title,
                    $slug,
                    $category,
                    $image_path,
                    $excerpt,
                    $content,
                    $seo_title,
                    $meta_description,
                    $status
                );


                if ($stmt->execute()) {

                    $message =
                        "Blog created successfully.";

                    $message_type =
                        "success";


                    /* Clear form */

                    $_POST = [];


                } else {

                    $message =
                        "Database Error: " .
                        $stmt->error;

                    $message_type =
                        "error";


                    /* Delete image if DB failed */

                    if (
                        $image_path !== null &&
                        file_exists(
                            __DIR__ . "/" . $image_path
                        )
                    ) {

                        unlink(
                            __DIR__ . "/" . $image_path
                        );
                    }
                }


                $stmt->close();
            }
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
        Add Blog | Prograte Admin
    </title>


    <!-- Google Font -->

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- Quill CSS -->

    <link
        href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css"
        rel="stylesheet"
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
            transition: .3s;
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
            transition: .3s;
        }


        input:focus,
        select:focus,
        textarea:focus {
            border-color: #d4af37;
        }


        textarea {
            resize: vertical;
            min-height: 150px;
            line-height: 1.7;
        }


        /* Slug */

        #slug {
            color: #aaa;
            cursor: not-allowed;
        }


        .slug-help {
            display: block;
            margin-top: 7px;
            color: #777;
            font-size: 12px;
        }


        /* Image */

        .image-upload {
            border: 1px dashed rgba(212,175,55,.4);
            padding: 25px;
            text-align: center;
            background: rgba(212,175,55,.03);
        }


        .image-upload i {
            color: #d4af37;
            font-size: 30px;
            margin-bottom: 12px;
        }


        .image-upload input {
            border: none;
            background: transparent;
            padding: 10px 0 0;
        }


        /* Quill */

        #content-editor {
            background: #fff;
            color: #222;
            border-radius: 6px;
        }


        #content-editor .ql-editor {
            min-height: 400px;
            font-family: Arial, sans-serif;
            font-size: 16px;
            line-height: 1.8;
        }


        .ql-toolbar {
            background: #f5f5f5;
            border-radius: 6px 6px 0 0;
        }


        .ql-container {
            border-radius: 0 0 6px 6px;
        }


        /* Stats */

        .editor-stats {
            display: flex;
            gap: 25px;
            padding: 12px 15px;
            background: #181818;
            border: 1px solid rgba(255,255,255,.08);
            border-top: none;
            color: #aaa;
            font-size: 13px;
        }


        .editor-stats strong {
            color: #d4af37;
        }


        .help-text {
            margin-top: 8px;
            color: #777;
            font-size: 12px;
        }


        /* Buttons */

        .submit-row {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 10px;
            padding-top: 25px;
            border-top: 1px solid rgba(255,255,255,.08);
        }


        .btn {
            border: none;
            padding: 14px 25px;
            font-family: inherit;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
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


        .preview-btn {
            background: transparent;
            color: #d4af37;
            border: 1px solid #d4af37;
        }


        .preview-btn:hover {
            background: #d4af37;
            color: #000;
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


            .btn {
                width: 100%;
            }


            .editor-stats {
                flex-wrap: wrap;
                gap: 12px;
            }

        }

    </style>

</head>


<body>


<div class="admin-page">

    <div class="admin-container">


        <!-- TOP BAR -->

        <div class="topbar">

            <h1>
                Add <span>New Blog</span>
            </h1>


            <a
                href="admin-dashboard.php"
                class="back-btn"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Dashboard

            </a>

        </div>


        <!-- ALERT -->

        <?php if ($message !== ""): ?>

            <div
                class="alert <?php echo htmlspecialchars($message_type); ?>"
            >

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <!-- FORM -->

        <div class="form-card">

            <form
                id="blog-form"
                method="POST"
                action=""
                enctype="multipart/form-data"
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
                            placeholder="Enter blog title"
                            value="<?php echo htmlspecialchars($_POST["title"] ?? ""); ?>"
                            required
                        >

                    </div>


                    <!-- SLUG -->

                    <div class="form-group">

                        <label for="slug">
                            URL Slug
                        </label>


                        <input
                            type="text"
                            id="slug"
                            name="slug"
                            placeholder="blog-url-slug"
                            readonly
                        >


                        <small class="slug-help">
                            Automatically generated from the title.
                        </small>

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

                            <option value="">
                                Select Category
                            </option>


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


                            <?php foreach (
                                $categories
                                as $cat
                            ): ?>

                                <option
                                    value="<?php echo htmlspecialchars($cat); ?>"
                                    <?php
                                    echo (
                                        ($_POST["category"] ?? "") === $cat
                                    )
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
                                echo (
                                    ($_POST["status"] ?? "Draft")
                                    === "Draft"
                                )
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Draft
                            </option>


                            <option
                                value="Published"
                                <?php
                                echo (
                                    ($_POST["status"] ?? "")
                                    === "Published"
                                )
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


                        <div class="image-upload">

                            <i class="fa-solid fa-image"></i>


                            <input
                                type="file"
                                name="image"
                                accept=".jpg,.jpeg,.png,.webp"
                            >


                            <p class="help-text">
                                JPG, PNG or WEBP — Maximum 5MB
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
                            placeholder="Write a short description for the blog card..."
                        ><?php echo htmlspecialchars($_POST["excerpt"] ?? ""); ?></textarea>

                    </div>


                    <!-- BLOG CONTENT -->

                    <div class="form-group full">

                        <label>
                            Blog Content *
                        </label>


                        <!-- Quill -->

                        <div id="content-editor"></div>


                        <!-- Hidden textarea -->

                        <textarea
                            id="content"
                            name="content"
                            hidden
                        ><?php echo htmlspecialchars($_POST["content"] ?? ""); ?></textarea>


                        <!-- Stats -->

                        <div class="editor-stats">

                            <span>
                                Words:
                                <strong id="word-count">
                                    0
                                </strong>
                            </span>


                            <span>
                                Characters:
                                <strong id="character-count">
                                    0
                                </strong>
                            </span>

                        </div>


                        <p class="help-text">
                            Write and format your complete blog article using the editor.
                        </p>

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
                            placeholder="SEO optimized title"
                            value="<?php echo htmlspecialchars($_POST["seo_title"] ?? ""); ?>"
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
                            placeholder="Write SEO meta description..."
                        ><?php echo htmlspecialchars($_POST["meta_description"] ?? ""); ?></textarea>

                    </div>

                </div>


                <!-- BUTTONS -->

                <div class="submit-row">


                    <a
                        href="admin-dashboard.php"
                        class="btn btn-secondary"
                    >

                        Cancel

                    </a>


                    <button
                        type="button"
                        id="preview-blog"
                        class="btn preview-btn"
                    >

                        <i class="fa-solid fa-eye"></i>

                        Preview Blog

                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="fa-solid fa-plus"></i>

                        Create Blog

                    </button>

                </div>


            </form>

        </div>

    </div>

</div>


<!-- QUILL JS -->

<script
    src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"
></script>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        /* =========================
           ELEMENTS
        ========================= */

        const form =
            document.getElementById(
                "blog-form"
            );


        const editorElement =
            document.getElementById(
                "content-editor"
            );


        const hiddenContent =
            document.getElementById(
                "content"
            );


        const titleInput =
            document.getElementById(
                "title"
            );


        const slugInput =
            document.getElementById(
                "slug"
            );


        const wordCount =
            document.getElementById(
                "word-count"
            );


        const characterCount =
            document.getElementById(
                "character-count"
            );


        /* =========================
           CHECK ELEMENTS
        ========================= */

        if (
            !form ||
            !editorElement ||
            !hiddenContent
        ) {

            console.error(
                "Blog form/editor not found."
            );

            return;
        }


        /* =========================
           QUILL
        ========================= */

        const quill =
            new Quill(
                "#content-editor",
                {

                    theme: "snow",

                    placeholder:
                        "Write your complete blog article here...",

                    modules: {

                        toolbar: [

                            [
                                {
                                    header: [
                                        1,
                                        2,
                                        3,
                                        false
                                    ]
                                }
                            ],

                            [
                                "bold",
                                "italic",
                                "underline",
                                "strike"
                            ],

                            [
                                {
                                    color: []
                                },

                                {
                                    background: []
                                }
                            ],

                            [
                                {
                                    list: "ordered"
                                },

                                {
                                    list: "bullet"
                                }
                            ],

                            [
                                {
                                    align: []
                                }
                            ],

                            [
                                "blockquote",
                                "code-block"
                            ],

                            [
                                "link"
                            ],

                            [
                                "clean"
                            ]

                        ]

                    }

                }
            );


        /* =========================
           LOAD OLD CONTENT
           AFTER ERROR
        ========================= */

        const oldContent =
            hiddenContent.value.trim();


        if (
            oldContent !== ""
        ) {

            quill.root.innerHTML =
                oldContent;

        }


        /* =========================
           SLUG
        ========================= */

        function generateSlug() {

            let slug =
                titleInput.value
                    .toLowerCase()
                    .trim()
                    .replace(
                        /[^a-z0-9\s-]/g,
                        ""
                    )
                    .replace(
                        /\s+/g,
                        "-"
                    )
                    .replace(
                        /-+/g,
                        "-"
                    );


            slugInput.value =
                slug;
        }


        titleInput.addEventListener(
            "input",
            generateSlug
        );


        generateSlug();


        /* =========================
           WORD COUNT
        ========================= */

        function updateWordCount() {

            const text =
                quill
                    .getText()
                    .trim();


            const words =
                text
                    ? text.split(/\s+/).length
                    : 0;


            wordCount.textContent =
                words;


            characterCount.textContent =
                text.length;
        }


        quill.on(
            "text-change",
            updateWordCount
        );


        updateWordCount();


        /* =========================
           FORM SUBMIT
        ========================= */

        form.addEventListener(
            "submit",
            function (event) {


                const text =
                    quill
                        .getText()
                        .trim();


                const html =
                    quill.root.innerHTML.trim();


                /* Empty check */

                if (!text) {

                    event.preventDefault();


                    alert(
                        "Please write your blog content first."
                    );


                    quill.focus();


                    return;
                }


                /*
                 * VERY IMPORTANT:
                 * Put Quill HTML into
                 * hidden textarea
                 */

                hiddenContent.value =
                    html;

            }
        );


        /* =========================
           PREVIEW
        ========================= */

        const previewButton =
            document.getElementById(
                "preview-blog"
            );


        if (previewButton) {

            previewButton.addEventListener(
                "click",
                function () {


                    const title =
                        titleInput.value.trim();


                    const category =
                        document.getElementById(
                            "category"
                        ).value;


                    const content =
                        quill.root.innerHTML;


                    if (!title) {

                        alert(
                            "Please enter blog title first."
                        );

                        titleInput.focus();

                        return;
                    }


                    const preview =
                        window.open(
                            "",
                            "_blank"
                        );


                    if (!preview) {

                        alert(
                            "Please allow pop-ups in your browser."
                        );

                        return;
                    }


                    preview.document.write(`

                        <!DOCTYPE html>

                        <html>

                        <head>

                            <meta charset="UTF-8">

                            <meta
                                name="viewport"
                                content="width=device-width, initial-scale=1.0"
                            >

                            <title>
                                ${escapeHtml(title)}
                            </title>


                            <style>

                                body {
                                    margin: 0;
                                    background: #070707;
                                    color: #f2f2f2;
                                    font-family: Arial, sans-serif;
                                }


                                .preview {
                                    max-width: 900px;
                                    margin: auto;
                                    padding: 80px 25px;
                                }


                                .category {
                                    color: #d4af37;
                                    font-size: 13px;
                                    font-weight: bold;
                                    text-transform: uppercase;
                                }


                                h1 {
                                    font-size: 50px;
                                    line-height: 1.2;
                                    margin: 20px 0 45px;
                                }


                                .content {
                                    color: #bbb;
                                    font-size: 17px;
                                    line-height: 1.9;
                                }


                                .content h1,
                                .content h2,
                                .content h3 {
                                    color: #fff;
                                }


                                .content a {
                                    color: #d4af37;
                                }


                                .content img {
                                    max-width: 100%;
                                    height: auto;
                                }


                                .content blockquote {
                                    border-left: 3px solid #d4af37;
                                    padding-left: 20px;
                                }

                            </style>

                        </head>


                        <body>

                            <article class="preview">

                                <div class="category">
                                    ${escapeHtml(category)}
                                </div>


                                <h1>
                                    ${escapeHtml(title)}
                                </h1>


                                <div class="content">
                                    ${content}
                                </div>

                            </article>

                        </body>

                        </html>

                    `);


                    preview.document.close();

                }
            );

        }


        /* =========================
           ESCAPE HTML
        ========================= */

        function escapeHtml(value) {

            return value
                .replace(
                    /&/g,
                    "&amp;"
                )
                .replace(
                    /</g,
                    "&lt;"
                )
                .replace(
                    />/g,
                    "&gt;"
                )
                .replace(
                    /"/g,
                    "&quot;"
                )
                .replace(
                    /'/g,
                    "&#039;"
                );
        }

    }

);

</script>


</body>

</html>


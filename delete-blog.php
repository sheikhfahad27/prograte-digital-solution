<?php

session_start();

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {
    header("Location: admin-login.php");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: blogs.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| CSRF Protection
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Blog ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id) {
    die("Invalid blog ID.");
}


/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
*/

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
| Get Image Before Delete
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT image FROM blogs WHERE id = ? LIMIT 1"
);

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$result = $stmt->get_result();

$image = null;

if ($result->num_rows === 1) {

    $blog = $result->fetch_assoc();

    $image = $blog["image"];
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| Delete Blog
|--------------------------------------------------------------------------
*/

$delete = $conn->prepare(
    "DELETE FROM blogs WHERE id = ?"
);

$delete->bind_param(
    "i",
    $id
);

$delete->execute();

$delete->close();


/*
|--------------------------------------------------------------------------
| Delete Image
|--------------------------------------------------------------------------
*/

if (
    !empty($image) &&
    file_exists($image)
) {
    unlink($image);
}


$conn->close();


header("Location: blogs.php");

exit;

?>
<?php
include "db_conn.php";

$id = $_GET["id"];
$table = "keyword";

$sql = "DELETE FROM `$table` WHERE id = $id";
$result = mysqli_query($conn, $sql);

if ($result) {
    header("Location: keyword.php?msg=Data deleted successfully");
    exit();
} else {
    echo "Failed: " . mysqli_error($conn);
}
?>

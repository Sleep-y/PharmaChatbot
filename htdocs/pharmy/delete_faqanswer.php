<?php
include "db_conn.php";

$id = $_GET["id"];
$table = "faq_answer";

$sql = "DELETE FROM `$table` WHERE id = $id";
$result = mysqli_query($conn, $sql);

if ($result) {
    header("Location: faqanswer.php?msg=Data deleted successfully");
    exit();
} else {
    echo "Failed: " . mysqli_error($conn);
}
?>

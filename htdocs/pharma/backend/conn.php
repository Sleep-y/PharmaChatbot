<?php
if ($_SERVER['HTTP_HOST'] == "localhost") {
  $host = "localhost";
  $user = "root";
  $password = "";
  // $db = "pharma_bak";
  $db = "pharma";
} else {
  $host = "localhost";
  $user = "id21263608_ciollesliboon";
  $password = "Liboon444!";
  $db = "id21263608_pharma";
}

$response = array(
  "success" => false,
  "message" => ""
);

try {
  $conn = mysqli_connect($host, $user, $password, $db);
} catch (Exception $e) {
  $response["message"] = $e->getMessage();
}

<?php

$dateNow = date("Y-m-d H:i:s");

$separator = "!I_I!";

$ORIGIN = "$_SERVER[REQUEST_SCHEME]://$_SERVER[SERVER_NAME]";
$PATH = ("/" . explode("/", $_SERVER["REQUEST_URI"])[1]);

$SERVER_NAME = "";
$whitelist = array(
  '127.0.0.1',
  '::1'
);

if (in_array($_SERVER['REMOTE_ADDR'], $whitelist) || str_contains($_SERVER['REMOTE_ADDR'], '192.168')) {
  $SERVER_NAME = ($ORIGIN . $PATH);
} else {
  $SERVER_NAME = ($ORIGIN);
}

$defaultMedicineImg = "$SERVER_NAME/public/medicine.png";

function getCartCountByMedId($medId)
{
  global $conn;

  $query = mysqli_query(
    $conn,
    "SELECT * FROM carts WHERE medicine_id ='$medId'"
  );

  return mysqli_num_rows($query) > 0 ? mysqli_num_rows($query) : 0;
}

function getCartCount($userId)
{
  global $conn;

  $query = mysqli_query(
    $conn,
    "SELECT * FROM carts WHERE user_id ='$userId'"
  );

  return mysqli_num_rows($query) > 0 ? mysqli_num_rows($query) : 0;
}

function getCartDataIdIfExist($inventoryId, $userId)
{
  global $conn;

  $query = mysqli_query(
    $conn,
    "SELECT * FROM cart WHERE user_id='$userId' and inventory_id='$inventoryId' and checkout_date IS NULL"
  );

  if (mysqli_num_rows($query) > 0) {
    $cartData = mysqli_fetch_object($query);
    return $cartData->id;
  }

  return null;
}

function isMedicineExist($name, $generic, $brand_id, $dosage, $category_id, $id = null)
{
  global $conn;

  $query = mysqli_query(
    $conn,
    "SELECT * FROM medicine_profile WHERE LOWER(medicine_name)='$name' and LOWER(generic_name)='$generic' and LOWER(dosage)='$dosage' and brand_id='$brand_id' and category_id='$category_id' " . ($id ? "and id <> $id" : "")
  );

  return mysqli_num_rows($query) > 0 ? true : false;
}

function uploadImg($file, $path)
{
  $res = array(
    "success" => false,
    "file_name" => ""
  );

  if (intval($file["error"]) == 0) {
    $uploadFile = date("mdY-his") . "_" . basename($file['name']);

    if (!is_dir($path)) {
      mkdir($path, 0777, true);
    }

    if (move_uploaded_file($file['tmp_name'], "$path/$uploadFile")) {
      $res["success"] = true;
      $res["file_name"] = $uploadFile;
    }
  }
  return (object) $res;
}

function isBrandExist($name, $id = null)
{
  global $conn;
  $newName = strtolower($name);
  $query = mysqli_query(
    $conn,
    "SELECT * FROM brands WHERE LOWER(`brand_name`)='$newName'" . ($id ? " and id <> '$id'" : "")
  );

  return mysqli_num_rows($query) > 0 ? true : false;
}

function isCategoryExist($name, $id = null)
{
  global $conn;
  $newName = strtolower($name);
  $query = mysqli_query(
    $conn,
    "SELECT * FROM category WHERE LOWER(`category_name`)='$newName'" . ($id ? " and id <> '$id'" : "")
  );

  return mysqli_num_rows($query) > 0 ? true : false;
}

function isSupplierExist($name, $address, $id = null)
{
  global $conn;
  $newName = strtolower($name);
  $newAddress = strtolower($address);

  $query = mysqli_query(
    $conn,
    "SELECT * FROM supplier WHERE  LOWER(`address`)='$newAddress' and LOWER(`supplier_name`)='$newName'" . ($id ? " and id <> '$id'" : "")
  );

  return mysqli_num_rows($query) > 0 ? true : false;
}

function checkItemNameCount($itemId = null, $itemName)
{
  global $conn;;

  $itemNameCount = mysqli_num_rows(
    mysqli_query(
      $conn,
      "SELECT * FROM `inventory` WHERE " . ($itemId == null ? "" : "item_id != '$itemId' and ") . " name='$itemName'"
    )
  );

  return $itemNameCount;
}

function getTableWithWhere($table, $condition = null)
{
  global $conn;

  $data = array();

  $cond = $condition ? " WHERE $condition" : "";

  $query = mysqli_query(
    $conn,
    "SELECT * FROM $table $cond"
  );

  while ($res = mysqli_fetch_object($query)) {
    array_push($data, $res);
  }

  return $data;
}

function getSingleDataWithWhere($table, $condition = null)
{
  global $conn;

  $cond = $condition ? " WHERE $condition" : "";

  $query = mysqli_query(
    $conn,
    "SELECT * FROM $table $cond"
  );

  return mysqli_num_rows($query) > 0 ? mysqli_fetch_object($query) : null;
}

function getTableData($table, $column = null, $value = null)
{
  global $conn;

  $data = array();

  $query = mysqli_query(
    $conn,
    "SELECT * FROM $table " . ($column ? "WHERE $column='$value'" : "")
  );

  while ($row = mysqli_fetch_object($query)) {
    array_push($data, $row);
  }

  return $data;
}

function getTableDataById($table, $columnId, $value)
{
  global $conn;

  $query = mysqli_query(
    $conn,
    "SELECT * FROM $table WHERE $columnId='$value' "
  );

  return mysqli_num_rows($query) > 0 ? mysqli_fetch_object($query) : null;
}

function update($table, $data, $columnWHere, $columnVal)
{

  global $conn;

  $set = array();

  try {
    if (count($data) > 0) {
      foreach ($data as $column => $value) {
        if ($value) {

          if ($value == "set_null") {
            array_push($set, "$column = NULL");
          } else if ($value == "set_zero") {
            array_push($set, "$column = '0'");
          } else {
            array_push($set, "$column = '" . mysqli_escape_string($conn, $value) . "'");
          }
        } else if ($column == "quantity" && $table == "medicines") {
          array_push($set, "$column = '" . mysqli_escape_string($conn, $value) . "'");
        }
      }

      if (count($set) > 0) {
        $queryStr = "UPDATE `$table` SET " . (implode(', ', $set)) . " WHERE $columnWHere='$columnVal'";
        $query = mysqli_query($conn, $queryStr);
        $err = mysqli_error($conn);

        return $query;
      }

      return null;
    }
  } catch (Exception $e) {
    $error = $e->getMessage();
  }

  return null;
}

function delete($table, $column, $value)
{
  global $conn;

  try {
    $queryStr = "DELETE FROM `$table` WHERE `$column`='$value'";

    return mysqli_query($conn, $queryStr);
  } catch (Exception $e) {
    $error = $e->getMessage();
  }

  return null;
}

function insert($table, $data)
{
  global $conn;

  $columns = array();
  $values = array();

  try {
    if (count($data) > 0) {
      foreach ($data as $column => $value) {
        if ($value) {

          if ($value == "set_zero") {
            array_push($columns, "`$column`");
            array_push($values, "'0'");
          } else if ($value == "set_null") {
            array_push($columns, "`$column`");
            array_push($values, "NULL");
          } else {
            array_push($columns, "`$column`");
            array_push($values, "'" . mysqli_escape_string($conn, $value) . "'");
          }
        }
      }

      if (count($values) == count($columns)) {
        $queryStr = "INSERT INTO `$table` (" . implode(",", $columns) . ") VALUES (" . implode(",", $values) . ")";

        $query = mysqli_query($conn, $queryStr);

        if ($query) {
          return mysqli_insert_id($conn);
        } else {
          $error = mysqli_error($conn);
        }
      }

      return null;
    }
  } catch (Exception $e) {
    $error = $e->getMessage();
  }

  return null;
}

function generateSystemId($table, $primaryStr, $preferredLetter = null)
{
  global $conn, $db;
  $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

  $AUTO_INCREMENT = mysqli_fetch_object(
    mysqli_query(
      $conn,
      "SELECT AUTO_INCREMENT AS ID FROM information_schema.tables WHERE table_name = '$table' and table_schema = '$db'"
    )
  );

  $countUser = mysqli_num_rows(
    mysqli_query(
      $conn,
      "SELECT COUNT(*) AS count FROM `$table`"
    )
  );

  $letterIndex = intval(intval($countUser) / 100);
  $letter = $preferredLetter == null ? $characters[$letterIndex] : $preferredLetter;

  return $primaryStr . date('y') . $letter . str_pad($AUTO_INCREMENT->ID, 4, '0', STR_PAD_LEFT);
}

function isSelected($value, $toCheck)
{
  if ($value && $toCheck) {
    if ($value == $toCheck) {
      return "selected";
    } else {
      return "";
    }
  }
  return "";
}

function getUserById($userId)
{
  global $conn;

  $query = mysqli_query(
    $conn,
    "SELECT * FROM users WHERE id='$userId'"
  );

  return mysqli_num_rows($query) > 0 ? mysqli_fetch_object($query) : null;
}

function getFullName($userId, $format = "") // format = with_middle
{
  $user = getUserById($userId);
  $fullName = "";

  if ($user->mname == "") {
    $fullName = ucwords("$user->fname $user->lname");
  } else {
    if ($format) {
      $fullName = ucwords("$user->fname $user->mname $user->lname");
    } else {
      $middle = $user->mname[0];
      $fullName = ucwords("$user->fname " . $middle . ". $user->lname");
    }
  }

  return $fullName;
}

function getAvatar($userId)
{
  global $SERVER_NAME;
  if ($userId) {
    $user = getUserById($userId);

    if ($user->avatar) {
      return "$SERVER_NAME/media/users/$user->avatar";
    }
  }

  return "$SERVER_NAME/public/default.png";
}

function getPrescriptionImg($id = null)
{
  global $SERVER_NAME, $conn;

  $defaultPrescription = "";

  if ($id) {
    $medicineQuery = mysqli_query(
      $conn,
      "SELECT * FROM order_tbl WHERE id='$id'"
    );

    if (mysqli_num_rows($medicineQuery) > 0) {
      $medicine = mysqli_fetch_object($medicineQuery);
      if ($medicine->prescription) {
        return "$SERVER_NAME/media/prescription/$medicine->prescription";
      }
      return $defaultPrescription;
    }
  }
  return "$defaultPrescription";
}

function getMedicineImage($itemId = null)
{
  global $SERVER_NAME, $conn, $defaultMedicineImg;

  if ($itemId) {
    $medicineQuery = mysqli_query(
      $conn,
      "SELECT * FROM medicine_profile WHERE id='$itemId'"
    );

    if (mysqli_num_rows($medicineQuery) > 0) {
      $medicine = mysqli_fetch_object($medicineQuery);
      if ($medicine->image) {
        return "$SERVER_NAME/media/drugs/$medicine->image";
      }
      return $defaultMedicineImg;
    }
  }
  return $defaultMedicineImg;
}

function returnResponse($params)
{
  print_r(
    json_encode($params)
  );
}

function pr($data)
{
  echo "<pre>";
  print_r($data); // or var_dump($data);
  echo "</pre>";
}

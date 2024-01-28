<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

date_default_timezone_set("Asia/Manila");

include("conn.php");
include("helpers.php");

$response = array(
  "success" => false,
  "message" => ""
);

$user = null;
$isLogin = isset($_SESSION["userId"]) ? true : false;
if ($isLogin) {
  $user = getUserById($_SESSION["userId"]);
}

if (isset($_GET['action'])) {
  try {
    switch ($_GET['action']) {
      case "logout":
        logout();
        break;
      case "login":
        login();
        break;
      case "addUser":
        addUser();
        break;
      case "update_profile":
        update_profile();
        break;
      case "update-user":
        update_profile();
        break;
      case "check_email":
        checkEmailIfExistR();
        break;
      case "delete_item":
        deleteItem();
        break;
      case "change_password":
        changePassword();
        break;
      case "save_supplier":
        save_supplier();
        break;
      case "save_category":
        save_category();
        break;
      case "save_brand":
        save_brand();
        break;
      case "medicine_save":
        medicine_save();
        break;
      case "add_medicine_quantity":
        add_medicine_quantity();
        break;
      case "add_to_cart":
        add_to_cart();
        break;
      case "remove_to_cart":
        remove_to_cart();
        break;
      case "update_cart":
        update_cart();
        break;
      case "admin_checkout":
        admin_checkout();
        break;
      case "get_order_details":
        get_order_details();
        break;
      case "get_category":
        get_select_data("category");
        break;
      case "get_brand":
        get_select_data("brands");
        break;
      case "get_supplier":
        get_select_data("supplier");
        break;
      case "get_medicine":
        get_select_data("medicine_profile");
        break;
      case "save_purchase":
        save_purchase();
        break;
      case "delete_med":
        delete_med();
        break;
      case "save_stock":
        save_stock();
        break;
      case "save_checkout":
        save_checkout();
        break;
      case "lock_screen":
        lock_screen();
        break;
      case "checkout":
        checkout();
        break;
      case "cancel_order":
        cancel_order();
        break;
      case "change_order_status":
        change_order_status();
        break;
      case "claim_online_order":
        claim_online_order();
        break;
      case "decline_order":
        decline_order();
        break;
      case "return_to_supplier":
        return_to_supplier();
        break;
      case "get_stocks":
        get_stocks();
        break;
      case "add_walk_in_order":
        add_walk_in_order();
        break;
      case "remove_walk_in_order":
        remove_walk_in_order();
        break;
      default:
        null;
        break;
    }
  } catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
  }
}

function get_near_expiration($medicine_id)
{
  global $conn;

  $query = mysqli_query(
    $conn,
    "SELECT * FROM inventory_general WHERE medicine_id='$medicine_id' ORDER BY expiration_date ASC LIMIT 1"
  );

  $medData = mysqli_fetch_object($query);

  return $medData->id;
}

function remove_walk_in_order()
{
  global $conn, $_POST;

  $inventory_id = $_POST["inventory_id"];
  $order_details_id = $_POST["order_details_id"];
  $order_id = $_POST["order_id"];
  $quantity = $_POST["quantity"];

  $delOrderDetails = delete("order_details", "id", $order_details_id);

  if ($delOrderDetails) {
    $getOrderDetailsData = getTableData("order_details", "order_id", $order_id);

    if (count($getOrderDetailsData) == 0) {
      delete("order_tbl", "id", $order_id);

      $response["success"] = true;
    } else {
      $response["success"] = true;
    }

    orderSubtotal($order_id);
    updateInventoryItems($inventory_id, $quantity);
  } else {
    $response["success"] = false;
    $response["message"] = mysqli_error($conn);
  }

  returnResponse($response);
}

function add_walk_in_order()
{
  global $conn, $_SESSION, $_POST;

  $user_id = $_SESSION["userId"];
  $orderNumber = generateSystemId("order_tbl", "ORD");

  $inventory_id = $_POST["inventory_id"];
  $quantity = $_POST["quantity"];
  $price = $_POST["price"];

  $orderTableData = getSingleDataWithWhere("order_tbl", "user_id='$_SESSION[userId]' and overall_total IS NULL");

  $inOrder = 0;

  if ($orderTableData) {
    $inOrder = $orderTableData->id;
  } else {
    $orderData = array(
      "order_number" => $orderNumber,
      "user_id" => $user_id,
      "type" => "walk_in"
    );

    $inOrder = insert("order_tbl", $orderData);
  }

  if ($inOrder) {

    $orderDetails = getSingleDataWithWhere("order_details", "order_id='$inOrder' and inventory_general_id='$inventory_id'");

    if (!$orderDetails) {
      $orderDetailsData = array(
        "order_id" => $inOrder,
        "order_subtotal" => doubleval($price) * intval($quantity),
        "quantity" => $quantity,
        "inventory_general_id" => $inventory_id
      );

      $inOrderDetails = insert("order_details", $orderDetailsData);

      if ($inOrderDetails) {
        $response["success"] = true;
        orderSubtotal($inOrder);

        updateInventoryItems($inventory_id, $quantity, "minus");
      } else {
        delete("order_tbl", "id", $inOrder);
      }
    } else {
      $order_subtotal = doubleval((doubleval($price) * intval($quantity)) + doubleval($orderDetails->order_subtotal));
      $order_details_quantity  = intval($quantity) + $orderDetails->quantity;
      $updateOrderSub = update("order_details", array("order_subtotal" => $order_subtotal, "quantity" => $order_details_quantity), "id", $orderDetails->id);

      if ($updateOrderSub) {
        $response["success"] = true;
        orderSubtotal($inOrder);
        updateInventoryItems($inventory_id, $quantity, "minus");
      }
    }
  } else {
    $response["success"] = false;
    $response["message"] = mysqli_error($conn);
  }

  returnResponse($response);
}

function orderSubtotal($orderId)
{
  $orderDetailsData = getTableData("order_details", "order_id", "$orderId");

  $orderSubtotal = 0.00;

  foreach ($orderDetailsData as $orderDetail) {
    $orderSubtotal += doubleval($orderDetail->order_subtotal);
  }

  update("order_tbl", array("subtotal" => $orderSubtotal), "id", $orderId);
}

function updateInventoryItems($inventory_id, $quantity, $action = "add")
{
  $inventoryData = getSingleDataWithWhere("inventory_general", "id='$inventory_id'");
  $newQuantity = 0;

  if ($action == "add") {
    $newQuantity = (intval($inventoryData->quantity) + intval($quantity));
  } else {
    $newQuantity = (intval($inventoryData->quantity) - intval($quantity));
  }

  if ($newQuantity == 0) {
    $newQuantity = "set_zero";
  }
  update("inventory_general", array("quantity" => $newQuantity), "id", $inventory_id);
}

function getDiscounted($inventoryId, $price)
{
  $inventory = getSingleDataWithWhere("inventory_general", "id='$inventoryId'");

  if ($inventory->is_discountable == "1") {
    $discount = intval($price) * 0.20;

    return number_format($price - $discount, 2, '.', ',');
  }

  return number_format($price, 2, '.', ',');
}

function get_stocks()
{
  global $_GET;

  $returned_id = $_GET["returned_id"];
  $inventory_id = $_GET["inventory_id"];

  try {
    $stockData = getSingleDataWithWhere("inventory_general", "id='$inventory_id'");

    $medicineData = getSingleDataWithWhere("medicine_profile", "id='$stockData->medicine_id'");
    $brandData = getSingleDataWithWhere("brands", "id='$medicineData->brand_id'");

    $supplierData = getSingleDataWithWhere("supplier", "id='$stockData->supplier_id'");

    $priceData = getSingleDataWithWhere("price", "id='$stockData->price_id'");

    $medicineName = "$medicineData->medicine_name/ $brandData->brand_name/ $medicineData->generic_name";

    $responseData = array(
      "return_id" => "$returned_id",
      "medicine_id" => "$stockData->medicine_id",
      "medicine_name" => "$medicineName",
      "supplier_id" => "$stockData->supplier_id",
      "supplier_name" => "$supplierData->supplier_name",
      "purchase_price" => "$priceData->purchased_price",
      "mark_up" => "$priceData->markup",
      "price" => "$priceData->price",
      "quantity" => "$stockData->quantity",
      "date_received" => date("Y-m-d"),
      "is_vatable" => $stockData->is_vatable == "0" ? false : true,
      "is_discountable" => $stockData->is_discountable == "0" ? false : true
    );

    $response["success"] = true;
    $response["data"] = $responseData;
  } catch (Exception $e) {
    $response["success"] = false;
    $response["data"] = $e->getMessage();
  }

  returnResponse($response);
}

function return_to_supplier()
{
  global $conn, $_POST;

  $inventoryId = $_POST["inventory_id"];

  $returnData = array(
    "inventory_id" => $inventoryId
  );

  $inReturn = insert("returned", $returnData);

  if ($inReturn) {
    $upDataInventoryTable = update("inventory_general", array("is_returned" => "1"), "id", $inventoryId);

    $response["success"] = true;
    $response["message"] = "Successfully returned to supplier.";
  } else {
    $response["success"] = false;
    $response["message"] = mysqli_error($conn);
  }

  returnResponse($response);
}

function decline_order()
{
  global $conn, $_POST;

  $order_id = $_POST['order_id'];
  $note = $_POST['note'];

  $orderData = array(
    "note" => "Declined reason: $note",
    "status" => "declined"
  );

  $update = update("order_tbl", $orderData, "id", $order_id);

  if ($update) {
    $response["success"] = true;
    $response["message"] = "Successfully declined order.";

    $orderDetailsData = getTableData("order_details", "order_id", $order_id);

    foreach ($orderDetailsData as $orderDetail) {
      updateInventoryItems($orderDetail->inventory_general_id, $orderDetail->quantity);
    }
  } else {
    $response["success"] = false;
    $response["message"] = mysqli_error($conn);
  }

  returnResponse($response);
}

function claim_online_order()
{
  global $conn, $_POST, $_SESSION;

  $order_id = $_POST["order_id"];

  $orderTblData = getSingleDataWithWhere("order_tbl", "id='$order_id'");
  $orderDetailsData = mysqli_fetch_object(mysqli_query($conn, "SELECT SUM(quantity) AS 'count' FROM order_details WHERE order_id='$order_id'"));

  $total = $_POST["total"];
  $amount = $_POST["amount"];
  $change = $_POST["change"];

  $totalQuantitySold = $orderDetailsData->count;

  $discount = (doubleval($orderTblData->subtotal) - doubleval($total));

  $paymentData = array(
    "order_id" => $order_id,
    "paid_amount" => "$amount",
    "customer_change" => "$change"
  );

  $paymentIn = insert("payment", $paymentData);

  $invoiceData = array(
    "payment_id" => $paymentIn,
    "order_id" => $order_id,
    "user_id" => $_SESSION["userId"]
  );

  $invoiceIn = insert("invoice", $invoiceData);

  $salesData = array(
    "invoice_id" => $invoiceIn,
    "total_quantity_sold" => $totalQuantitySold
  );

  $salesIn = insert("sales", $salesData);

  $orderData = array(
    "discount" => $discount ? $discount : "set_null",
    "overall_total" => $total,
    "status" => "claimed"
  );

  $updateOrder = update("order_tbl", $orderData, "id", $order_id);

  if (mysqli_error($conn)) {
    $response["success"] = false;
    $response["message"] = mysqli_error($conn);
  } else {
    $response["success"] = true;
    $response["message"] = "Item(s) set claimed";
    $response["invoice_id"] = $invoiceIn;
  }

  returnResponse($response);
}

function change_order_status()
{
  global $_POST;

  $order_id = $_POST["order_id"];
  $status = $_POST["status"];

  $update = update("order_tbl", array("status" => "$status"), "id", $order_id);

  if ($update) {
    $response["success"] = true;
  } else {
    $response["success"] = false;
    $response["message"] = "Error while updating order status<br>Please try again later.";
  }

  returnResponse($response);
}

function cancel_order()
{
  global $conn, $_POST;

  $order_id = $_POST["order_id"];

  $orderData = array(
    "status" => "canceled",
    "note" => "User Canceled"
  );

  $orderUp = update("order_tbl", $orderData, "id", $order_id);

  if ($orderUp) {
    $orderDetails = getTableWithWhere("order_details", "order_id='$order_id'");
    foreach ($orderDetails as $detail) {
      $inventory = getSingleDataWithWhere("inventory_general", "id='$detail->inventory_general_id'");

      $newQuantity = intval($inventory->quantity) + intval($detail->quantity);

      $updateInData = array(
        "quantity" => "$newQuantity"
      );

      update("inventory_general", $updateInData, "id", $inventory->id);
    }
    update("cart", array("status" => "canceled"), "order_id", $order_id);
  }

  if (mysqli_error($conn)) {
    $response["success"] = false;
    $response["message"] = mysqli_error($conn);
  } else {
    $response["success"] = true;
    $response["message"] = "Order successfully canceled";
  }

  returnResponse($response);
}

function checkout()
{
  global $conn, $_SESSION, $_FILES;

  $user_id = $_SESSION["userId"];
  $orderNumber = generateSystemId("order_tbl", "ORD");
  $prescription = isset($_FILES["prescription_img"]) ? $_FILES["prescription_img"] : null;

  $orderData = array(
    "order_number" => $orderNumber,
    "user_id" => $user_id,
    "subtotal" => "",
    "discount" => "0.00",
    "overall_total" => "",
    "type" => "online",
    "status" => "pending",
    "prescription" => ""
  );

  if ($prescription) {
    $uploadedImg = uploadImg($prescription, "../media/prescription");
    $orderData["prescription"] = $uploadedImg->success ? $uploadedImg->file_name : "";
  } else {
    $orderData["prescription"] = "set_null";
  }

  $orderSubtotal = 0.00;
  $overallTotal = 0.00;

  $cartData = getTableWithWhere("cart", "user_id ='$user_id' and status='pending' and checkout_date IS NULL");

  // Create total
  foreach ($cartData as $cart) {
    $inventoryQStr = mysqli_query(
      $conn,
      "SELECT 
      ig.id AS 'inventory_id',
      ig.medicine_id,
      (SELECT price FROM price p WHERE p.id = ig.price_id) AS 'price'
      FROM inventory_general ig
      LEFT JOIN medicine_profile mp
      ON mp.id = ig.medicine_id
      WHERE ig.id = '$cart->inventory_id'
      "
    );
    $inventory = mysqli_fetch_object($inventoryQStr);

    if (mysqli_num_rows($inventoryQStr) > 0) {
      $orderSubtotal += (doubleval($inventory->price) * intval($cart->quantity));
      $overallTotal += (doubleval($inventory->price) * intval($cart->quantity));
    }
  }

  $orderData["subtotal"] = $orderSubtotal;
  $orderData["overall_total"] = $overallTotal;

  $orderIn = insert("order_tbl", $orderData);

  if ($orderIn) {
    // Insert order details
    foreach ($cartData as $cart) {
      $inventoryQStr = mysqli_query(
        $conn,
        "SELECT 
        ig.id AS 'inventory_id',
        ig.medicine_id,
        ig.quantity,
        (SELECT price FROM price p WHERE p.id = ig.price_id) AS 'price'
        FROM inventory_general ig
        LEFT JOIN medicine_profile mp
        ON mp.id = ig.medicine_id
        WHERE ig.id = '$cart->inventory_id'
      "
      );

      if (mysqli_num_rows($inventoryQStr) > 0) {
        $inventory = mysqli_fetch_object($inventoryQStr);

        $subTotal = (doubleval($inventory->price) * intval($cart->quantity));

        $orderDetailsData = array(
          "order_id" => $orderIn,
          "order_subtotal" => "$subTotal",
          "quantity" => "$cart->quantity",
          "inventory_general_id" => $inventory->inventory_id
        );

        $orderDetailsIn = insert("order_details", $orderDetailsData);

        if ($orderDetailsIn) {
          $newQuantity = intval($inventory->quantity) - intval($cart->quantity);

          $updateInData = array(
            "quantity" => "$newQuantity"
          );

          $updateIn = update("inventory_general", $updateInData, "id", $inventory->inventory_id);

          if ($updateIn) {
            $dateNow = date("Y-m-d");
            $updateCartData = array(
              "order_id" => $orderIn,
              "checkout_date" => $dateNow
            );
            update("cart", $updateCartData, "id", $cart->id);
          }
        }
      }
    }

    $response["success"] = true;
    $response["message"] = "Your order is now being process!<br>Please present the order number upon claiming the order.<br><strong>Order number</strong> is be located in your order page.<br>Thank you!";
  } else {
    $response["success"] = false;
    $response["message"] = mysqli_error($conn);
  }

  returnResponse($response);
}

function getPageCount($searchVal = "", $limit)
{
  global $conn;
  $query = null;
  $qStr = ("SELECT 
            ig.id AS 'inventory_id',
            ig.medicine_id,
            mp.medicine_name,
            mp.generic_name,
            ig.product_number,
            (SELECT brand_name FROM brands b WHERE b.id = mp.brand_id) AS 'brand_name',
            (SELECT price FROM price p WHERE p.id = ig.price_id) AS 'price'
            FROM inventory_general ig
            LEFT JOIN medicine_profile mp
            ON mp.id = ig.medicine_id
          " . " WHERE mp.medicine_name LIKE '%$searchVal%'");

  $query = mysqli_query($conn, $qStr);

  return ceil(mysqli_num_rows($query) / $limit);
}

function lock_screen()
{
  global $_SESSION;
  $user = getUserById($_SESSION['userId']);

  session_unset();
  session_destroy();

  session_start();
  $_SESSION["email"] = $user->email;
  header("location: ../admin/views/lock-screen");
}

function save_checkout()
{
  global $conn, $_SESSION, $_POST;

  $user_id = $_SESSION["userId"];

  $discountType = $_POST["discountType"];

  $total = $_POST["total"];
  $amount = $_POST["amount"];
  $change = $_POST["change"];


  if (intval($amount) >= intval($total)) {
    $orderQuery = mysqli_query(
      $conn,
      "SELECT * FROM order_tbl WHERE user_id='$user_id' and `type`='walk_in' and overall_total IS NULL"
    );

    if (mysqli_num_rows($orderQuery) > 0) {
      $orderData = mysqli_fetch_object($orderQuery);

      $discount = (doubleval($orderData->subtotal) - doubleval($total));

      $upOrderData = array(
        "discount_type" => $discountType,
        "discount" => $discount ? $discount : "set_null",
        "overall_total" => $total
      );

      update("order_tbl", $upOrderData, "id", $orderData->id);

      $orderDetails = getTableData("order_details", "order_id", $orderData->id);

      $totalQuantitySold = 0;
      $inventory_id = 0;

      foreach ($orderDetails as $orderDetail) {
        if (!$inventory_id) {
          $inventory_id = $orderDetail->inventory_general_id;
        }
        $totalQuantitySold += intval($orderDetail->quantity);
      }

      $paymentData = array(
        "order_id" => $orderData->id,
        "paid_amount" => "$amount",
        "customer_change" => "$change"
      );

      $paymentIn = insert("payment", $paymentData);

      $invoiceData = array(
        "payment_id" => $paymentIn,
        "order_id" => $orderData->id,
        "user_id" => $_SESSION["userId"]
      );

      $invoiceIn = insert("invoice", $invoiceData);

      $salesData = array(
        "invoice_id" => $invoiceIn,
        "total_quantity_sold" => $totalQuantitySold
      );

      $salesIn = insert("sales", $salesData);

      $response["success"] = true;
      $response["message"] = "Item(s) successfully added to invoice";
      $response["invoice_id"] = $invoiceIn;
    }
  } else {
    $response["success"] = false;
    $response["message"] = "Amount should not be less than to total.";
  }

  returnResponse($response);
}

// function save_checkout()
// {
//   global $conn, $_POST, $_SESSION;

//   $productData = json_decode($_POST["productData"]);
//   $subTotal = $_POST["subTotal"];
//   $discount = $_POST["discount"];
//   $total = $_POST["total"];
//   $amount = $_POST["amount"];
//   $change = $_POST["change"];

//   if (intval($amount) >= intval($total)) {
//     $totalQuantitySold = 0;

//     $orderTableData = array(
//       "order_number" => generateSystemId("order_tbl", "ORD"),
//       "user_id" => NULL,
//       "subtotal" => $subTotal,
//       "discount" => $discount,
//       "overall_total" => $total,
//       "type" => "walk_in",
//       "status" => "claimed"
//     );

//     $orderIn = insert("order_tbl", $orderTableData);

//     foreach ($productData->data as $product) {
//       $inventory = getSingleDataWithWhere("inventory_general", "product_number = '$product->product_number'");

//       $orderDetailsData = array(
//         "order_id" => $orderIn,
//         "order_subtotal" => "$product->orderTotal",
//         "quantity" => "$product->quantity",
//         "inventory_general_id" => $inventory->id
//       );

//       $orderDetailsIn = insert("order_details", $orderDetailsData);

//       if ($orderDetailsIn) {
//         $totalQuantitySold += intval($product->quantity);
//         $newQuantity = intval($inventory->quantity) - intval($product->quantity);

//         $updateInData = array(
//           "quantity" => "$newQuantity"
//         );

//         $updateIn = update("inventory_general", $updateInData, "id", $inventory->id);
//       }
//     }

//     $paymentData = array(
//       "order_id" => $orderIn,
//       "paid_amount" => "$amount",
//       "customer_change" => "$change"
//     );

//     $paymentIn = insert("payment", $paymentData);

//     $invoiceData = array(
//       "payment_id" => $paymentIn,
//       "order_id" => $orderIn,
//       "user_id" => $_SESSION["userId"]
//     );

//     $invoiceIn = insert("invoice", $invoiceData);

//     $salesData = array(
//       "invoice_id" => $invoiceIn,
//       "total_quantity_sold" => $totalQuantitySold
//     );

//     $salesIn = insert("sales", $salesData);

//     $response["success"] = true;
//     $response["message"] = "Item(s) successfully added to invoice";
//     $response["invoice_id"] = $invoiceIn;
//   } else {
//     $response["success"] = false;
//     $response["message"] = "Amount should not be less than to total.";
//   }

//   returnResponse($response);
// }

function save_stock()
{
  global $conn, $_POST;

  $returnedId = isset($_POST["returned_id"]) ? $_POST["returned_id"] : null;

  $medicine_id = $_POST["medicine_id"];
  $supplier_id = $_POST["supplier_id"];
  $price = $_POST["price"];
  $purchased_price = $_POST["purchased_price"];
  $mark_up = $_POST["mark_up"];
  $quantity = $_POST["quantity"];
  $received_date = $_POST["received_date"];
  $expiration_date = $_POST["expiration_date"];
  $serial_number = $_POST["serial_number"];

  $isVatable =  isset($_POST["isVatable"]) ? "1" : "set_zero";
  $isDiscountable =  isset($_POST["isDiscountable"]) ? "1" : "set_zero";

  $priceData = array(
    "purchased_price" => $purchased_price,
    "markup" => $mark_up,
    "price" => $price,
    "status" => "active"
  );

  $price_id = insert("price", $priceData);

  $productNumber = generateSystemId("inventory_general", "PROD");

  if ($price_id) {
    $stockData = array(
      "medicine_id" => $medicine_id,
      "price_id" => $price_id,
      "supplier_id" => $supplier_id,
      "quantity" => $quantity,
      "date_received" => $received_date,
      "expiration_date" => $expiration_date,
      "serial_number" => $serial_number,
      "product_number" => $productNumber,
      "is_vatable" => $isVatable,
      "is_discountable" => $isDiscountable
    );

    $inStock = insert("inventory_general", $stockData);

    if ($inStock) {

      if ($returnedId) {
        $returnData = array(
          "product_number" => $productNumber,
          "date_replaced" => date("Y-m-d")
        );
        $upReturnData = update("returned", $returnData, "id", $returnedId);
      }

      $response["success"] = true;
      $response["message"] = "Stock successfully added.";
    } else {
      $response["success"] = false;
      $response["message"] = mysqli_error($conn);
    }
  } else {
    $response["success"] = false;
    $response["message"] = mysqli_error($conn);
  }

  returnResponse($response);
}

function delete_med()
{
  global $conn, $_POST;

  $deletedData = array(
    "deleted" => "1"
  );

  $up = update("medicine_profile", $deletedData, "id", $_POST["medicine_id"]);

  if ($up) {
    $response["success"] = true;
  } else {
    $response["success"] = false;
    $response["message"] = mysqli_error($conn);
  }

  returnResponse($response);
}

function save_purchase()
{
  global $conn, $_POST, $_SESSION;

  $supplier_id = $_POST["supplier_id"];
  $medicine_id = $_POST["medicine_id"];
  $payment_date = $_POST["payment_date"];
  $payment_amount = $_POST["payment_amount"];
  $quantity = $_POST["quantity"];

  $createdBy = $_SESSION["userId"];
  $creationDate = date("Y-m-d");

  $purchaseOrderData = array(
    "supplier_id" => $supplier_id,
    "created_by" => $createdBy,
    "medicine_id" => $medicine_id,
    "creation_date" => $creationDate,
    "payment_amount" => $payment_amount,
    "payment_date" => $payment_date,
    "quantity" => $quantity
  );

  $procPurchase = insert("purchase_order", $purchaseOrderData);

  if ($procPurchase) {
    $response["success"] = true;
    $response["message"] = "Purchase order successfully added.";

    $supplierData = getTableDataById("supplier", "id", $supplier_id);

    $query = mysqli_query(
      $conn,
      "SELECT 
      mp.medicine_name,
      mp.generic_name,
      (SELECT brand_name FROM brands b WHERE b.id = mp.brand_id) AS 'brand_name'
      FROM medicine_profile mp
      WHERE mp.id = '$medicine_id'
        "
    );

    $medicineData = mysqli_fetch_object($query);

    $response["data"] = array(
      "supplierId" => $supplier_id,
      "supplierName" => $supplierData->supplier_name,
      "medicineId" => $medicine_id,
      "medicineName" =>  "$medicineData->medicine_name/ $medicineData->brand_name/ $medicineData->generic_name",
      "paymentAmount" => $payment_amount,
      "paymentDate" => $payment_date,
      "quantity" => $quantity
    );
  } else {
    $response["success"] = false;
    $response["message"] = mysqli_error($conn);
  }

  returnResponse($response);
}

function get_select_data($table)
{
  global $conn;

  $data = [];

  if ($table == "medicine_profile") {
    $medQuery = mysqli_query(
      $conn,
      "SELECT 
      mp.id,
      mp.medicine_name,
      mp.generic_name,
      (SELECT brand_name FROM brands b WHERE b.id = mp.brand_id) AS 'brand_name'
      FROM medicine_profile mp
      "
    );

    while ($row = mysqli_fetch_object($medQuery)) {
      array_push($data, $row);
    }
  } else {
    $data = getTableData($table);
  }

  returnResponse($data);
}

function get_order_details()
{
  global $_POST;

  $orderItems = array();
  $orderDetails = getTableDataById("orders", "order_id", $_POST['order_id']);

  array_push($orderItems, stripcslashes($orderDetails->items));

  returnResponse($orderItems);
}

function admin_checkout()
{
  global $conn, $_SESSION;

  if (isset($_SESSION["userId"])) {
    $cartData = getTableData("carts", "user_id", $_SESSION["userId"]);

    if (count($cartData) > 0) {
      $cartDetails = array(
        "order_code" => generateSystemId("order", "ODR"),
        "user_id" => $_SESSION["userId"],
        "overall_total" => "",
        "order_from" => "system",
        "items" => array()
      );
      $overallTotal = 0;
      foreach ($cartData as $cart) {
        $medicine = getTableDataById("medicines", "medicine_id", $cart->medicine_id);

        $overallTotal += floatval($medicine->price *  $cart->quantity);

        $itemData = array(
          "classification" => $medicine->classification,
          "generic_name" => $medicine->generic_name,
          "brand_name" => $medicine->brand_name,
          "price" => $medicine->price,
          "order_quantity" => $cart->quantity,
          "total" => number_format(floatval($medicine->price *  $cart->quantity), 2, ".")
        );

        array_push($cartDetails["items"], $itemData);
      }
      $cartDetails["items"] = mysqli_escape_string($conn, json_encode($cartDetails["items"]));
      $cartDetails["overall_total"] = $overallTotal;

      $insertOrder = insert("orders", $cartDetails);

      if ($insertOrder) {
        delete("carts", "user_id", $_SESSION["userId"]);
        $response["success"] = true;
        $response["message"] = "Cart successfully checked out.";
      } else {
        $response["success"] = false;
        $response["message"] = mysqli_error($conn);
      }
    } else {
      $response["success"] = false;
      $response["message"] = "Internal server error.<br>Please contact administrator";
    }
  } else {
    $response["success"] = false;
    $response["message"] = "Internal server error.<br>Please contact administrator";
  }

  returnResponse($response);
}

function update_cart()
{
  global $conn, $_POST;

  $cart_id = $_POST["cart_id"];
  $quantity = $_POST["quantity"];

  $upCart = update("cart", array("quantity" => $quantity), "id", $cart_id);

  if ($upCart) {
    $response["success"] = true;
  } else {
    $response["success"] = false;
  }


  returnResponse($response);
}

// function update_cart()
// {
//   global $conn, $_POST, $_SESSION;

//   if (isset($_SESSION["userId"])) {
//     $cartDbData = getTableWithWhere("cart", "user_id ='$_SESSION[userId]' and status='pending' and checkout_date IS NULL");

//     $hasError = false;
//     foreach ($cartDbData as $cart) {
//       $cartQuantity = $_POST["cartQty$cart->id"];

//       $cartData = array("quantity" => $cartQuantity);

//       $updateCart = update("cart", $cartData, "id", $cart->id);

//       $hasError = $updateCart && !$hasError ? false : true;
//     }

//     if (!$hasError) {
//       $response["success"] = true;
//       $response["message"] = "Cart updated successfully";
//     } else {
//       $response["success"] = false;
//       $response["message"] = ("Other cart items successfully update but encountered an error<br>Error: " . mysqli_error($conn));
//     }
//   } else {
//     $response["success"] = false;
//     $response["message"] = "Error while updating cart<br>Please try again later.";
//   }


//   returnResponse($response);
// }

function remove_to_cart()
{
  global $conn, $_POST;

  $getCartData = getTableData("carts", "cart_id", $_POST["cart_id"]);
  if (count($getCartData) > 0) {
    $cartData = $getCartData[0];
    $newQuantity = intval($_POST["medicine_qty"]) + intval($cartData->quantity);
    $updateQty = update("medicines", array("quantity" => $newQuantity), "medicine_id", $cartData->medicine_id);

    if ($updateQty) {
      delete("carts", "cart_id", $cartData->cart_id);
      $response["success"] = true;
      $response["message"] = "Item in cart successfully remove";
    } else {
      $response["success"] = false;
      $response["message"] = mysqli_error($conn);
    }
  } else {
    $response["success"] = false;
    $response["message"] = "Internal server error.<br>Please contact Administrator.";
  }

  returnResponse($response);
}

function add_to_cart()
{
  global $conn, $_POST, $_SESSION;

  $inventory_id = $_POST["inventory_id"];
  $quantity = $_POST["quantity"];
  $userId = $_SESSION["userId"];

  if (isset($_SESSION["userId"])) {
    $cartId = getCartDataIdIfExist($inventory_id, $userId);

    $cartData = array(
      "user_id" => $userId,
      "inventory_id" => $inventory_id,
      "quantity" => $quantity
    );

    if ($cartId) {
      $dbCartData = getTableData("cart", "id", $cartId);
      $newCartQuantity = intval($dbCartData[0]->quantity) + intval($quantity);

      $comm = update("cart", array("quantity" => $newCartQuantity), "id", $cartId);
    } else {
      $comm = insert("cart", $cartData);
    }

    if ($comm) {
      // update_quantity($medicine_id, $quantity_to_add);
      $response["success"] = true;
      $response["message"] = "($quantity) Item(s) added to cart.";
    } else {
      $response["success"] = false;
      $response["message"] = mysqli_error($conn);
    }
  } else {
    $response["success"] = false;
    $response["message"] = "Internal server error!<br>Please contact administrator.";
  }

  returnResponse($response);
}

function update_quantity($medicine_id, $quantity)
{
  $medicineData = getTableData("medicines", "medicine_id", $medicine_id)[0];
  $newQuantity = intval($medicineData->quantity) - intval($quantity);

  update("medicines", array("quantity" => $newQuantity), "medicine_id", $medicine_id);
}

function add_medicine_quantity()
{
  global $conn, $_POST;

  $quantity_to_add = $_POST['quantity_to_add'];
  $inventory_id = $_POST['inventory_id'];

  $inventoryData = getTableData("inventory_general", "id", $inventory_id);

  if (count($inventoryData) > 0) {
    $newInventoryQty = intval($inventoryData[0]->quantity) + intval($quantity_to_add);
    $inventory_arr = array(
      "quantity" => $newInventoryQty
    );
    $comm = update("inventory_general", $inventory_arr, "id", $inventory_id);

    if ($comm) {
      $response["success"] = true;
      $response["message"] = "Successfully added.";
    } else {
      $response["success"] = false;
      $response["message"] = mysqli_error($conn);
    }
  } else {
    $response["success"] = false;
    $response["message"] = "Error while adding quantity.<br>Please try again later.";
  }

  returnResponse($response);
}

function medicine_save()
{
  global $conn, $_POST, $_FILES;

  $action = $_POST["action"];
  $isCleared = isset($_POST["isCleared"]) ? $_POST["isCleared"] : "";

  $medicine_id = isset($_POST["medicine_id"]) ? $_POST["medicine_id"] : null;

  $medicine_img = $_FILES["medicine_img"];
  $name = ucwords($_POST["name"]);
  $category_id = $_POST["category_id"];
  $dose = $_POST["dose"];
  $generic_name = ucwords($_POST["generic_name"]);
  $brand_id = $_POST["brand_id"];

  $med_desc = "";

  $action = $_POST["action"];

  $med_desc = $_POST["med_desc"] == "" ? "set_null" : ucfirst($_POST["med_desc"]);

  if (!isMedicineExist(strtolower($name), strtolower($generic_name), $brand_id, strtolower($dose), $category_id, $medicine_id)) {

    $medicineData = array(
      "medicine_name" => $name,
      "category_id" => $category_id,
      "image" => "",
      "brand_id" => $brand_id,
      "generic_name" => $generic_name,
      "description" => $med_desc,
      "dosage" => $dose
    );

    $uploadedImg = uploadImg($medicine_img, "../media/drugs");
    $medicineData["image"] = $uploadedImg->success ? $uploadedImg->file_name : "";

    $comm = null;

    if ($action == "add") {
      $medicineData["image"] = $uploadedImg->file_name;

      $comm = insert("medicine_profile", $medicineData);
    } else if ($action == "edit") {
      $medicineData["image"] = $isCleared == "Yes" ? "set_null" : $uploadedImg->file_name;

      $comm = update("medicine_profile", $medicineData, "id", $medicine_id);
    } else {
      $response["success"] = false;
      $response["message"] = "An error occurred while uploading the image.<br>Please try again later.";
    }

    if ($comm) {
      $response["success"] = true;
      if ($action == "edit") {
        $response["message"] = "Medicine successfully updated.";
      } else {
        $response["message"] = "Successfully added new medicine.";
      }
    } else {
      $response["success"] = false;
      $response["message"] = mysqli_error($conn);
    }
  } else {
    $response["success"] = false;
    $response["message"] = "Medicine is already exist.";
  }

  returnResponse($response);
}

function save_brand()
{
  global $conn, $_POST;

  $brandId = isset($_POST["brandId"]) ? $_POST["brandId"] : null;
  $type = isset($_POST["type"]) ? $_POST["type"] : null;

  $name = ucwords($_POST["name"]);
  $description = $_POST["description"] == "" ? "set_null" : ucfirst($_POST["description"]);
  $status = isset($_POST["isActive"]) ? "1" : "set_zero";

  $action = $_POST["action"];

  if (!isBrandExist($name, $brandId)) {
    $brandData = array(
      "brand_name" => $name,
      "brand_description" => $description,
      "status" => $status
    );

    $procBrand = null;
    if ($action == "add") {
      $procBrand = insert("brands", $brandData);
    } else {
      $procBrand = update("brands", $brandData, "id", $brandId);
    }

    if ($procBrand) {
      $response["success"] = true;
      $response["message"] = "Brand successfully " . ($action == "add" ? "added." : "updated.");

      if ($type == "add_select") {
        $response["id"] = $procBrand;
        $response["description"] = $description;
        $response["brand_name"] = $name;
      }
    } else {
      $response["success"] = false;
      $response["message"] = mysqli_error($conn);
    }
  } else {
    $response["success"] = false;
    $response["message"] = "Brand name: <strong>\"$name\"</strong> already exist.";
  }

  returnResponse($response);
}

function save_category()
{
  global $conn, $_POST;

  $categoryId = isset($_POST["categoryId"]) ? $_POST["categoryId"] : null;
  $name = ucwords($_POST["name"]);
  $status = isset($_POST["isActive"]) ? "1" : "set_zero";
  $prescriptionRequired = isset($_POST["prescriptionRequired"]) ? "1" : "set_zero";

  $action = $_POST["action"];
  $description = $_POST["description"] == "" ? "set_null" : ucfirst($_POST["description"]);

  if (!isCategoryExist($name, $categoryId)) {
    $categoryData = array(
      "category_name" => $name,
      "description" => $description,
      "status" => $status,
      "prescription_required" => $prescriptionRequired
    );

    $procCategory = null;
    if ($action == "add") {
      $procCategory = insert("category", $categoryData);
    } else {
      $procCategory = update("category", $categoryData, "id", $categoryId);
    }

    if ($procCategory) {
      $response["success"] = true;
      $response["message"] = "Category successfully " . ($action == "add" ? "added." : "updated.");
    } else {
      $response["success"] = false;
      $response["message"] = mysqli_error($conn);
    }
  } else {
    $response["success"] = false;
    $response["message"] = "Category name: <strong>\"$name\"</strong> already exist.";
  }

  returnResponse($response);
}

function save_supplier()
{
  global $conn, $_POST;

  $supplierId = isset($_POST["supplierId"]) ? $_POST["supplierId"] : null;
  $name = ucwords($_POST["name"]);
  $address = ucwords($_POST["address"]);
  $contact = ucwords($_POST["contact"]);
  $status = isset($_POST["isActive"]) ? "1" : "set_zero";

  $action = $_POST["action"];

  if (!isSupplierExist($name, $address, $supplierId)) {
    $supplierData = array(
      "supplier_name" => $name,
      "address" => $address,
      "contact" => $contact,
      "status" => $status
    );

    $procSupplier = null;
    if ($action == "add") {
      $procSupplier = insert("supplier", $supplierData);
    } else {
      $procSupplier = update("supplier", $supplierData, "id", $supplierId);
    }

    if ($procSupplier) {
      $response["success"] = true;
      $response["message"] = "Supplier successfully " . ($action == "add" ? "added." : "updated.");
    } else {
      $response["success"] = false;
      $response["message"] = mysqli_error($conn);
    }
  } else {
    $response["success"] = false;
    $response["message"] = "Supplier name: <strong>\"$name\"</strong> already exist.";
  }

  returnResponse($response);
}

function addUser()
{
  global $conn, $_POST, $_SESSION;

  $fname = mysqli_escape_string($conn, ucwords($_POST["fname"]));
  $mname = mysqli_escape_string($conn, ucwords($_POST["mname"]));
  $lname = mysqli_escape_string($conn, ucwords($_POST["lname"]));
  $uname = isset($_POST["uname"]) ? mysqli_escape_string($conn, ucwords($_POST["uname"])) : null;

  $email = $_POST["email"];
  $password = isset($_POST["password"]) ? $_POST["password"] : "password123";
  $role = $_POST["role"];
  $action = $_POST["action"];

  $isEmailExist = checkEmailIfExistF($email);

  if (!$isEmailExist) {
    $userData = array(
      "fname" => $fname,
      "mname" => $mname,
      "lname" => $lname,
      "uname" => $uname,
      "email" => $email,
      "password" => password_hash($password, PASSWORD_DEFAULT),
      "role" => $role,
      "isNew" => "1"
    );

    $user = insert("users", $userData);

    if ($user) {
      $response["success"] = true;
      if ($action == "register") {
        $_SESSION["userId"] = $user;
        $response["message"] = "You are now registered";
      } else {
        $response["message"] = "User successfully added<br>User's <strong>default</strong> password is \"<strong>$password</strong>\"";
      }
    } else {
      $response["success"] = false;
      $response["message"] = mysqli_error($conn);
    }
  } else {
    $response["success"] = false;
    $response["message"] = "Email already exist.";
  }

  returnResponse($response);
}

function changePassword()
{
  global $conn, $_POST;

  $userId = $_POST["userId"];

  $old = $_POST["old_password"];
  $new = $_POST["new_password"];

  $user = getUserById($userId);

  if ($old == $new) {
    $response["success"] = false;
    $response["message"] = "Old password and New password should not be the same.";
  } else if (!password_verify($old, $user->password)) {
    $response["success"] = false;
    $response["message"] = "Old password does not match!";
  } else {
    $update = update(
      "users",
      array(
        "password" => password_hash($new, PASSWORD_DEFAULT),
        "isNew" => "set_null"
      ),
      "id",
      $userId
    );

    if ($update) {
      $response["success"] = true;
      $response["userId"] = $userId;
      $response["message"] = "Password successfully change";
    } else {
      $response["success"] = false;
      $response["message"] = mysqli_error($conn);
    }
  }

  returnResponse($response);
}

function deleteItem()
{
  global $_POST, $conn;

  $table = $_POST["table"];
  $column = $_POST["column"];
  $val = $_POST["val"];

  $del = delete($table, $column, $val);

  if ($del) {
    $response["success"] = true;
  } else {
    $response["success"] = false;
    $response["message"] = mysqli_error($conn);
  }

  returnResponse($response);
}

function checkEmailIfExistR()
{
  global $conn, $_POST;

  $id = isset($_GET['id']) ? $_GET['id'] : null;

  returnResponse(
    ["isExist" => mysqli_num_rows(
      mysqli_query(
        $conn,
        "SELECT * FROM users WHERE " . ($id ? "id != '$id' and " : "") . " email = '{$_POST['email']}'"
      )
    ) > 0 ? true : false]
  );
}

function checkEmailIfExistF($email, $id = null)
{
  global $conn;

  return mysqli_num_rows(
    mysqli_query(
      $conn,
      "SELECT * FROM users WHERE " . ($id ? "id != '$id' and " : "") . " email = '{$email}'"
    )
  ) > 0 ? true : false;
}

function update_profile()
{
  global $conn, $_POST, $_FILES, $_SESSION;

  $uploadedFile = "";

  $set_null = $_POST["set_null"];
  $profile = $_FILES["image"];

  $fname = ucwords($_POST["fname"]);
  $mname = $_POST["mname"] ? ucwords($_POST["mname"]) : null;
  $lname = ucwords($_POST["lname"]);
  $email = $_POST["email"];
  $uname = isset($_POST["uname"]) ? $_POST["uname"] : null;

  if (intval($profile["error"]) == 0) {
    $uploadFile = date("mdY-his") . "_" . basename($profile['name']);
    $target_dir = "../media/users";

    if (!is_dir($target_dir)) {
      mkdir($target_dir, 0777, true);
    }

    if (move_uploaded_file($profile['tmp_name'], "$target_dir/$uploadFile")) {
      $uploadedFile = $uploadFile;
    } else {
      $response["success"] = false;
      $response["message"] = "Error uploading profile.<br>Please try again later.";
      exit();
    }
  }

  $userProfileData = array(
    "uname" => $uname,
    "fname" => $fname,
    "mname" => $mname,
    "lname" => $lname,
    "email" => $email,
    "avatar" => $set_null == "Yes" ? "set_null" : $uploadedFile
  );

  $updateUser = update("users", $userProfileData, "id", $_SESSION["userId"]);
  if ($updateUser) {
    $response["success"] = true;
    $response["message"] = "Profile updated successfully";
  } else {
    $response["success"] = false;
    $response["message"] = mysqli_error($conn);
  }

  returnResponse($response);
}

function login()
{
  global $conn;

  $email = $_POST["email"];
  $password = $_POST["password"];
  $role = $_POST["role"];

  $query = mysqli_query(
    $conn,
    "SELECT * FROM users WHERE email='$email'"
  );

  if (mysqli_num_rows($query) > 0) {
    $user = mysqli_fetch_object($query);
    if ($role != $user->role) {
      $response["success"] = false;
      $response["message"] = "You are not allowed to login on this page.";
    } else {
      if (password_verify($password, $user->password)) {
        $response["success"] = true;
        $_SESSION["userId"] = $user->id;

        if ($role == "admin") {
          $response["isNew"] = $user->isNew;

          if (isset($_SESSION["email"])) {
            unset($_SESSION["email"]);
          }
        }
      } else {
        $response["success"] = false;
        $response["message"] = "Password not match.";
      }
    }
  } else {
    $response["success"] = false;
    $response["message"] = "User not found.";
  }

  returnResponse($response);
}

function logout()
{
  global $_SESSION, $_GET;
  $_SESSION = array();

  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
      session_name(),
      '',
      time() - 42000,
      $params["path"],
      $params["domain"],
      $params["secure"],
      $params["httponly"]
    );
  }

  session_destroy();
  if ($_GET["location"] == "user") {
    header("location: ../");
  } else {
    header("location: ../admin/");
  }
}

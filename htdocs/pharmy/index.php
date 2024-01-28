<?php
include "db_conn.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <title>Keyword and FAQ Management</title>
</head>

<body>
  <div class="container">
    <!-- Keyword Table -->
    <div class="text-center">
      <h3>Keyword</h3>
    </div>
    <?php
    if (isset($_GET["msg"])) {
      $msg = $_GET["msg"];
      echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
      ' . $msg . '
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    }
    ?>
    <a href="add-new.php" class="btn btn-dark mb-3">Add New Keyword</a>

    <table class="table table-hover text-center">
      <thead class="table-dark">
        <tr>
          <th scope="col">KeywordID</th>
          <th scope="col">Keyword</th>
          <th scope="col">Description</th>
          <th scope="col">Category</th>
          <!--
            th scope="col">Action</th>
          -->
        </tr>
      </thead>
      <tbody>
        <?php
        $sql = "SELECT * FROM `keywords`";
        $result = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
        ?>
          <tr>
            <td><?php echo $row["keywordID"] ?></td>
            <td><?php echo $row["keywordName"] ?></td>
            <td><?php echo $row["termDescription"] ?></td>
            <td><?php echo $row["keyword_category"] ?></td>
            <td>
              <a href="edit_key.php?keywordID=<?php echo $row['keywordID'] ?>" class="link-dark"><i class="fa-solid fa-pen-to-square fs-5 me-3"></i></a>
              <a href="delete_keyword.php?keywordID=<?php echo $row['keywordID'] ?>" class="link-dark"><i class="fa-solid fa-trash fs-5"></i></a>
            </td>
          </tr>
        <?php
        }
        ?>
      </tbody>
    </table>

    <div class="text-center">
      <h3>Keyword Categories</h3>
    </div>
    <table class="table table-hover text-center">
      <thead class="table-dark">
        <tr>
          <th scope="col">Category ID</th>
          <th scope="col">Definition</th>
          <th scope="col">Descriptor</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $sql = "SELECT * FROM `keywords_categories`";
        $result = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
        ?>
          <tr>
            <td><?php echo $row["keycatID"] ?></td>
            <td><?php echo $row["category_name"] ?></td>
            <td><?php echo $row["description"] ?></td>
            <!-- Not used for now
            <td>
              <a href="edit_key.php?keyword=<?php echo $row["keycatID"] ?>" class="link-dark"><i class="fa-solid fa-pen-to-square fs-5 me-3"></i></a>
              <a href="delete_keyword.php?keyword=<?php echo $row["keycatID"] ?>" class="link-dark"><i class="fa-solid fa-trash fs-5"></i></a>
            </td>
            --->
          </tr>
        <?php
        }
        ?>
      </tbody>
    </table>

    <div class="text-center">
      <h3>Dosage Instructions</h3>
    </div>
    <table class="table table-hover text-center">
      <thead class="table-dark">
        <tr>
          <th scope="col">ID</th>
          <th scope="col">Name</th>
          <th scope="col">Dosage Instruction</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $sql = "SELECT * FROM `generic_medicine`";
        $result = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
        ?>
          <tr>
            <td><?php echo $row["genericID"] ?></td>
            <td><?php echo $row["genericName"] ?></td>
            <td><?php echo $row["dosageForm"] ?></td>
            <!-- Not used for now
            <td>
              <a href="edit_dosage.php?keyword=<?php echo $row["genericID"] ?>" class="link-dark"><i class="fa-solid fa-pen-to-square fs-5 me-3"></i></a>
              <a href="delete_dosage.php?keyword=<?php echo $row["genericID"] ?>" class="link-dark"><i class="fa-solid fa-trash fs-5"></i></a>
            </td>
            --->
          </tr>
        <?php
        }
        ?>
      </tbody>
    </table>

  <!-- Bootstrap -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

</body>

</html>

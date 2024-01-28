<!DOCTYPE html>
<html>
<head>
    <title>Navigation Sidebar</title>
    <style>
        /* Basic styles for the sidebar */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
        }

        .sidebar {
            height: 100%;
            width: 200px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #b603fc;
            padding-top: 20px;
        }

        .sidebar a {
            display: block;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
        }

        .sidebar a:hover {
            background-color: #9100c6;
        }

        /* Logo styles */
        .logos {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px;
            margin-bottom: 20px;
        }

        /* Center the Messenger logo */
        .messenger-logo {
            display: flex;
            justify-content: center;
        }

        /* Main content styles */
        .main-content {
            margin-left: 200px;
            padding: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

<?php
// Include the database connection file
include 'db_conn.php';
?>
<div class="sidebar">
    <div class="logos">
        <img src="pharmalogo.png" alt="pharmalogo" width="150" height="155">
        <!-- Icon for FB Messenger -->
        <br><br><br><br><br><br>
        <div class="messengerlogo">
            <a href="https://www.facebook.com/messages/t/YourPageID" target="_blank">
                <img src="messengerlogo.png" width="50" height="55">
            </a>
        </div>
        <!-- Icon for FAQ page -->
        <br><br><br><br><br><br>
        <div class="keywordlogo">
            <a href="index.php">
                <img src="booklogo.png" width="50" height="55" alt="FAQ">
            </a>
        </div>
    </div>
</div>
<!-- Your content goes here -->
<div class="main-content">
    <h1>Chatbot Update</h1>
    <br><br><br><br><br><br><br><br><br>
    <h1>Keyword Table</h1>

    <table border="1" style="margin: 0 auto;">
        <thead>
            <tr>
                <th>Keyword</th>
                <th>Definition</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Perform a SELECT query to fetch data from the "keyword" table
            $keyword_sql = "SELECT * FROM `keyword`"; // Assuming this table exists
            $keyword_result = $conn->query($keyword_sql);

            // Display the keyword table
            if ($keyword_result && $keyword_result->num_rows > 0) {
                while ($row = $keyword_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$row['keyword']}</td>";
                    echo "<td>{$row['definition']}</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2'>No data found in Keyword table</td></tr>";
            }

            // Perform a JOIN query to fetch data from "FAQ-Categorizer" and "FAQ-Answer" tables
            $faq_sql = "SELECT FC.prompt, FA.response FROM `FAQ-Categorizer` AS FC 
                        INNER JOIN `FAQ-Answer` AS FA ON FC.category = FA.category";
            $faq_result = $conn->query($faq_sql);

            // Display the FAQ table
            if ($faq_result && $faq_result->num_rows > 0) {
                echo "</tbody></table>"; // Close the keyword table

                echo "<h1>FAQ Table</h1>";
                echo "<table border='1' style='margin: 0 auto;'>";
                echo "<thead><tr><th>Prompt</th><th>Response</th></tr></thead><tbody>";

                while ($row = $faq_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$row['prompt']}</td>";
                    echo "<td>{$row['response']}</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2'>No data found in FAQ tables</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>


</body>
</html>
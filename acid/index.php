
<?php
include "db_conn.php";

// these statements are prepared because i used existing provinces and 
//town/cities in the students database for selection in the form than manual typing
$province_query = "SELECT * FROM province ORDER BY name";
$province_result = $conn->query($province_query);
$town_query = "SELECT * FROM town_city ORDER BY name";
$town_result = $conn->query($town_query);

if (isset($_POST['submit'])) {
    $s_num = $_POST['s_number'];
    $s_fn = $_POST['s_fn'];
    $s_mn = $_POST['s_mn'];
    $s_ln = $_POST['s_ln'];
    $s_gender = $_POST['s_gender'];
    $s_bday = $_POST['s_birthday'];

    $s_contact = $_POST['s_contact'];
    $s_street = $_POST['s_street'];
    $s_zipcode = $_POST['s_zipcode']; 
    $s_province = $_POST['province_name']; 
    $s_town_city = $_POST['town_city_name']; 

    $conn->begin_transaction();
    try {
        $sql = "INSERT INTO students(student_number, first_name, middle_name, last_name, gender, birthday) 
                VALUES('$s_num', '$s_fn', '$s_mn', '$s_ln', '$s_gender', '$s_bday')";
        $result = mysqli_query($conn, $sql);
        
        $student_id = $conn->insert_id;

        $sql_details = "INSERT INTO student_details(student_id, contact_number, street, zip_code, town_city, province) 
                        VALUES('$student_id', '$s_contact', '$s_street', '$s_zipcode', 1, 1)";
        //default values for town and province because it is set to not null and cannot be done in another insert, to work around this restriction, i will set some default values first and 
        //use update than use insert for province and town, because i have to make a selection dropdown and look up the id of the selection
        //it would probably be easier for me this way 


        $result = mysqli_query($conn, $sql_details);

        $id_retrival_sql = "SELECT id FROM province WHERE name = '$s_province'";
        $id_retrieval_result = $conn->query($id_retrival_sql);
        if ($id_retrieval_result->num_rows > 0) {
            $province_row = $id_retrieval_result->fetch_assoc();
            $province_id = $province_row['id'];

            $town_id_retrieval_sql = "SELECT id FROM town_city WHERE name = '$s_town_city'";
            $town_id_result = $conn->query($town_id_retrieval_sql);
            if ($town_id_result->num_rows > 0) {
                $town_row = $town_id_result->fetch_assoc();
                $town_city_id = $town_row['id'];

                $update_sql = "UPDATE student_details SET province = '$province_id', town_city = '$town_city_id' WHERE student_id = '$student_id'";
                $result = mysqli_query($conn, $update_sql);
            }
        }

        $conn->commit();

        echo "New student record added successfully.";
    } 
    catch (Exception $e) {
        $conn->rollback();
        echo "Transaction failed: " . $e->getMessage();
    }
}

if (isset($_POST['update_id'])) {
    $current_id = $_POST['current_id'];
    $new_id = $_POST['new_id'];

    $conn->begin_transaction();
    try {
        $update_students_sql = "UPDATE students SET student_number = '$new_id' WHERE student_number = '$current_id'";
        $result1 = mysqli_query($conn, $update_students_sql);
        if (!$result1) {
            throw new Exception("Error updating student number in students table.");
        }


        $update_details_sql = "UPDATE student_details SET student_id = '$new_id' WHERE student_id = '$current_id'";
        $result2 = mysqli_query($conn, $update_details_sql);
        if (!$result2) {
            throw new Exception("Error updating student number in student_details table.");
        }

        $conn->commit();
        echo "Student ID updated successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Transaction failed: " . $e->getMessage();
    }
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class = "form-container">
    <h2>Add Student </h2>
    <form action="" method="post">
        <label>Student Number:</label> <input type="text" name="s_number"><br><br>
        <label>First Name:</label><input type="text" name="s_fn"><br><br>
        <label>Middle Name:</label><input type="text" name="s_mn"><br><br>                
        <label>Last Name:</label><input type="text" name="s_ln"><br><br>       
        <label>Gender:</label><input type="text" name="s_gender"><br><br>          
        <label>Birthday:</label><input type="text" name="s_birthday"><br><br>      
        <label>Contact Number:</label><input type="text" name="s_contact"><br><br>    
        <label>Street Name:</label><input type="text" name="s_street"><br><br>   
        <label>Province:</label>
        <select name="province_name" required>
            <option value="">-Select Province-</option>
            <?php
            while ($row = $province_result->fetch_assoc()) {
                echo "<option value='{$row['name']}'>{$row['name']}</option>";
            }
            ?>
        </select>
        <br><br> 
        <label>Town/City:</label>
        <select name="town_city_name" required>
            <option value="">-Select Town/City-</option>
            <?php
            while ($row = $town_result->fetch_assoc()) {
                echo "<option value='{$row['name']}'>{$row['name']}</option>";
            }
            ?>
        </select>
        <br><br> 
        <label>Zip Code:</label><input type="text" name="s_zipcode"><br><br>                                  
        <button type="submit" name="submit"> Submit </button>
    </form>
</div>

<div class="form-container">
    <h2>Update Student ID</h2>
        <form action="" method="post">
            <label>Current Student ID:</label><input type="text" name="current_id" required><br><br>
            <label>New Student ID:</label><input type="text" name="new_id" required><br><br>
            <button type="submit" name="update_id">Update ID</button>
        </form>
</div>
</form>
</body>
</html>

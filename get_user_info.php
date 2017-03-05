<?php

    $id = $_GET['id'];
    $api_token = $_GET['api_token'];
    $user_to_show_info_id = $_GET['user_to_show_info_id'];
    
    // Check all entered data
    if (strcmp($id, '') == 0 || strcmp($api_token, '') == 0 || strcmp($user_to_show_info_id, '') == 0) {
        echo json_encode(array('error' => 1, 'info' => 'Some entered data are empty.'));
        exit();
    }
    
    // Connect to database
    $link = mysqli_connect('sql11.freemysqlhosting.net', 'sql11154458', 'sYUmbMukdS', 'sql11154458', 3306);
	if (!$link) {
		echo json_encode(array('error' => 2, 'info' => 'Server error occured. Try later.'));
		mysqli_close($link);
		exit();
	}
	
	mysqli_query($link, "SET NAMES 'utf8'");
	mysqli_query($link, "SET CHARACTER SET 'utf8'");
	mysqli_query($link, "SET SESSION collation_connection = 'utf8_general_ci'");
	
	// Checking if user is exists
	$result = mysqli_query($link, "SELECT * FROM `users` WHERE `id` = ".$id);
	if (mysqli_num_rows($result) != 0) {
	    // Ok, user exists. Now checking his api_token
	    $row = mysqli_fetch_array($result);
	    if (strcmp($row['api_token'], $api_token) == 0) {
	        // Token is good. Check if user we want to show info is exists
	        $result = mysqli_query($link, "SELECT * FROM `users` WHERE `id` = ".$user_to_show_info_id);
	        if (mysqli_num_rows($result) != 0) {
	            // Everything is okay. Returning user information
	            $row = mysqli_fetch_array($result);
	            echo json_encode(array('error' => 0, 'login' => $row['login'], 'online' => (bool)$row['online'], 'name' => $row['first_name']." ".$row['last_name'], 'email' => $row['email'], 'country' => $row['country'], 'city' => $row['city'], 'about' => $row['about']));
	        } else {
	            echo json_encode(array('error' => 10, 'info' => 'User you want to show info doesn\'t exists.'));
	        }
	    } else {
	        echo json_encode(array('error' => 6, 'info' => 'Wrong api_token.'));
	    }
	} else {
	    echo json_encode(array('error' => 5, 'info' => 'User doesn\'t exists.'));
	}
	
	// Release data and disconnect from database
	mysqli_free_result($result);
	mysqli_close($link);

?>
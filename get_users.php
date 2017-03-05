<?php

    $id = $_GET['id'];
    $api_token = $_GET['api_token'];
    $offset = $_GET['offset'];
    
    // Check all entered data
    if (strcmp($id, '') == 0 || strcmp($api_token, '') == 0 || strcmp($offset, '') == 0) {
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
	        // Token is good. Returning users
	        $result = mysqli_query($link, "SELECT * FROM `users` WHERE `id` <> ".$id." LIMIT ".$offset.", 10");
	        if (mysqli_num_rows($result) != 0) {
	            $users = array();
			    while ($row = mysqli_fetch_array($result)) {
			    	// Combine user info
			    	$user = array('id' => $row['id'], 'login' => $row['login'], 'name' => $row['first_name']." ".$row['last_name'], 'online' => (bool)$row['online'], 'has_access_password' => (bool)strcmp($row['access_password'], '') != 0);
				    array_push($users, $user);
			    }
			    echo json_encode(array('error' => 0, 'users' => $users));
	        } else {
	            echo json_encode(array('error' => 8, 'info' => 'No more users.'));
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
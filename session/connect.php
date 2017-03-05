<?php

    $id = $_GET['id'];
    $api_token = $_GET['api_token'];
    $id_to_spectate = $_GET['id_to_spectate'];
    $access_password = $_GET['access_password'];
    
    // Check all entered data
    if (strcmp($id, '') == 0 || strcmp($api_token, '') == 0 || strcmp($id_to_spectate, '') == 0 || strcmp($access_password, '') == 0) {
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
	
	// Checking if user is exists
	$result = mysqli_query($link, "SELECT * FROM `users` WHERE `id` = ".$id);
	if (mysqli_num_rows($result) != 0) {
	    // Ok, user exists. Now checking his api_token
	    $row = mysqli_fetch_array($result);
	    if (strcmp($row['api_token'], $api_token) == 0) {
	    	// Checking if user we want to spectate exists
	        $result = mysqli_query($link, "SELECT * FROM `users` WHERE `id` = ".$id_to_spectate);
	        if (mysqli_num_rows($result) != 0) {
	            // Ok, user exists. Checking if he is online
	            $row = mysqli_fetch_array($result);
	            if ($row['online']) {
	            	// User is online. Checking access_password to be right
	            	if (strcmp($row['access_password'], $access_password) == 0) {
	                	// Good access_password. Return WebSocket server address to connect
	                	echo json_encode(array('error' => 0, 'ws_server_address' => 'ws://where-u-are.com:8080'));
	            	} else {
	                	echo json_encode(array('error' => 12, 'info' => 'Wrong access_password.'));
	            	}
	        	} else {
	            	echo json_encode(array('error' => 11, 'info' => 'User to spectate isn\'t online.'));
	        	}
	        } else {
	        	echo json_encode(array('error' => 10, 'info' => 'User to spectate with that id doesn\'t exists.'));
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
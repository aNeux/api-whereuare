<?php

    $login = $_GET['login'];
    $password = $_GET['password'];
    
    // Check all entered data
    if (strcmp($login, '') == 0 || strcmp($password, '') == 0) {
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
	
	// Trying to identify user
	$result = mysqli_query($link, "SELECT * FROM `users` WHERE `login` = '".$login."' AND `password` = '".$password."'");
	if (mysqli_num_rows($result) != 0) {
		// Correct login and password. Checking if user's email is validated
		$row = mysqli_fetch_array($result);
		if (strcmp($row['email_verification_code'], "OK") == 0) {
			// Email is really validated. Generating api_token and add it into the database
			$chars_to_generate_api_token = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
			$api_token = '';
			for ($i = 0; $i <= 10; $i++) {
				$api_token .= substr($chars_to_generate_api_token, mt_rand(0, strlen($chars_to_generate_api_token) - 1), 1);
			}
			mysqli_query($link, "UPDATE `users` SET `api_token` = '".md5($api_token)."' WHERE `id` = ".$row['id']);
			echo json_encode(array('error' => 0, 'id' => $row['id'], 'name' => $row['first_name']." ".$row['last_name'], 'api_token' => md5($api_token)));
		} else {
			echo json_encode(array('error' => 15, 'email' => $row['email'], 'info' => 'User\'s email address isn\'t confirmed.'));
		}
	} else {
		echo json_encode(array('error' => 9, 'info' => 'Wrong login or password.'));
	}
	
	// Release data and disconnect from database
	mysqli_free_result($result);
	mysqli_close($link);

?>
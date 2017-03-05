<?php

	require '/home/workspace/www/vendor/autoload.php';
    use Mailgun\Mailgun;
    
    // Notify user about changing his password
    function send_email_about_new_password($email, $country_code) {
        $mg = new Mailgun("key-bf83a6907336282d92c094888c6554e3");
        $domain = "mailing.where-u-are.com";
        $subject = "";
        $message = "";
        if (strcasecmp($country_code, "ru") == 0) {
            $subject = "Изменение пароля";
            $message = "Ваш пароль был изменен. Если данную операцию проводили не Вы, то немедленно восстановите свой пароль!\r\n\r\nСпасибо, что выбрали именно нас :)\r\n---\r\nС уважением, команда \"Where U Are\"";
        } else {
            $subject = "Changing password";
            $message = "Your password was changed. If this operation isn't carried out by you then immediately restore your password!\r\n\r\nThank you for choosing us :)\r\n---\r\nBest regards, \"Where U Are\" team";
        }
        $mg->sendMessage($domain, array('from' => 'Where U Are <noreply@where-u-are.com>', 
                                'to'      => $email, 
                                'subject' => $subject, 
                                 'text'    => $message));
    }

    $id = $_GET['id'];
    $api_token = $_GET['api_token'];
    $old_password = $_GET['old_password'];
    $new_password = $_GET['new_password'];
    
    // Check all entered data
    if (strcmp($id, '') == 0 || strcmp($api_token, '') == 0 || strcmp($old_password, '') == 0 || strcmp($new_password, '') == 0) {
        echo json_encode(array('error' => 1, 'info' => 'Some entered data are empty.'));
        exit();
    }
    
    // Connect to database
    $link = mysqli_connect('sql11.freemysqlhosting.net', 'sql11154458', 'sYUmbMukd', 'sql11154458', 3306);
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
	    // Ok, user is exists. Now checking his api_token
	    $row = mysqli_fetch_array($result);
	    if (strcmp($row['api_token'], $api_token) == 0) {
	        // Token is good. Checking password
	        if (strcmp($row['password'], $old_password) == 0) {
	            // Right password. Changing it to new one and api_token too
	            $chars_to_generate_api_token = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
				$api_token = '';
				for ($i = 0; $i <= 10; $i++) {
					$api_token .= substr($chars_to_generate_api_token, mt_rand(0, strlen($chars_to_generate_api_token) - 1), 1);
				}
	            mysqli_query($link, "UPDATE `users` SET `password` = '".$new_password."', `api_token` = '".md5($api_token)."' WHERE `id` = ".$id);
	            // Notify user about changed password by email
	            send_email_about_new_password($row['email'], $row['country_code']);
	            echo json_encode(array('error' => 0, 'info' => 'Your password was successfully changed.', 'api_token' => md5($api_token)));
	        } else {
	            echo json_encode(array('error' => 7, 'info' => 'Wrong password.'));
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
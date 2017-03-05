<?php

	require '/home/workspace/www/vendor/autoload.php';
    use Mailgun\Mailgun;
	
	// Send successful registration email to new user
    function send_successful_registration_email($email, $country_code) {
    	$mg = new Mailgun("key-bf83a6907336282d92c094888c6554e3");
    	$domain = "mailing.where-u-are.com";
    	$subject = "";
    	$message = "";
    	if (strcasecmp($country_code, "ru") == 0) {
        	$subject = "Регистрация завершена";
        	$message = "Ваш почтовый ящик успешно подтвержден! С этого момента Вы можете свободно использовать все возможности нашего программного продукта, а мы, в свою очередь, обязуемся не тревожить Вас надоедливыми рассылками.\r\n\r\nСпасибо, что выбрали именно нас :)\r\n---\r\nС уважением, команда \"Where U Are\"";
    	} else {
        	$subject = "Registration completed";
        	$message = "Your email was successfuly confirmed. Now you can freely use all features of our software, and we promise you to don\'t disturb with annoying Email newsletters.\r\n\r\nThank you for choosing us :)\r\n---\r\nBest regards, \"Where U Are\" team";
    	}
    	$mg->sendMessage($domain, array('from' => 'Where U Are <noreply@where-u-are.com>', 
                            	'to'      => $email, 
                            	'subject' => $subject, 
                            	'text'    => $message));
    }

    $email = $_GET['email'];
    $email_verification_code = $_GET['email_verification_code'];
    
    // Check all entered data
    if (strcmp($email, '') == 0 || strcmp($email_verification_code, '') == 0) {
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
	
	// Checking if user with that email exists
	$result = mysqli_query($link, "SELECT * FROM `users` WHERE `email` = '".$email."'");
	if (mysqli_num_rows($result) != 0) {
		// Ok, such user exists. Now checking if his email isn't validated
		$row = mysqli_fetch_array($result);
		if (strcmp($row['email_verification_code'], "OK") != 0) {
			// Сhecking email_verification_code to be right
			if (strcmp($row['email_verification_code'], $email_verification_code) == 0) {
				// email_verification_code is good. Now setting that user is validated
	    		mysqli_query($link, "UPDATE `users` SET `email_verification_code` = 'OK' WHERE `email` = '".$email."'");
	    		send_successful_registration_email($email, $row['country_code']);
	    		echo json_encode(array('error' => 0, 'info' => 'User\'s email address was successfuly confirmed.'));
			} else {
				echo json_encode(array('error' => 13, 'info' => 'Wrong email_verification_code.'));
			}
		} else {
			echo json_encode(array('error' => 16, 'info' => 'That user\'s email is already confirmed.'));
		}
	} else {
	    echo json_encode(array('error' => 14, 'info' => 'User with that email address doesn\'t exists.'));
	}
	
	// Release data and disconnect from database
	mysqli_free_result($result);
	mysqli_close($link);

?>
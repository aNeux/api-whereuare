<?php

    require '/home/workspace/www/vendor/autoload.php';
    use Mailgun\Mailgun;
    
    // Send to user's email address email_verification_code once more
    function send_new_email_verification_code($email, $new_email_verification_code, $country_code) {
        $mg = new Mailgun("key-bf83a6907336282d92c094888c6554e3");
        $domain = "mailing.where-u-are.com";
        $subject = "";
        $message = "";
        if (strcasecmp($country_code, "ru") == 0) {
            $subject = "Подтверждение E-mail адреса";
            $message = "Для завершения регистрации в сервисе \"Where U Are\" необходимо ввести код подтверждения E-mail адреса в соответствующее окно приложения.\r\n\r\nВаш новый код: ".$new_email_verification_code."\r\n\r\nСпасибо, что выбрали именно нас :)\r\n---\r\nС уважением, команда \"Where U Are\"";
        } else {
            $subject = "Confirming E-mail address";
            $message = "To complete registration in the service \"Where U Are\" you must enter E-mail address confirmation code in the appropriate application window.\r\n\r\nYours new code: ".$new_email_verification_code."\r\n\r\nThank you for choosing us :)\r\n---\r\nBest regards, \"Where U Are\" team";
        }
        $mg->sendMessage($domain, array('from' => 'Where U Are <noreply@where-u-are.com>', 
                                'to'      => $email, 
                                'subject' => $subject, 
                                 'text'    => $message));
    }
    
    $email = $_GET['email'];
    
    // Check all entered data
    if (strcmp($email, '') == 0) {
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
	
	// Checking if user with that email exists
	$result = mysqli_query($link, "SELECT * FROM `users` WHERE `email` = '".$email."'");
	if (mysqli_num_rows($result) != 0) {
	    // Ok, such user exists. Now checking if his email isn't validated
	    $row = mysqli_fetch_array($result);
	    if (strcmp($row['email_verification_code'], "OK") != 0) {
	        // Generating new code and change it in database
		    $chars_to_generate_email_verification_code = "1234567890";
		    $new_email_verification_code = '';
		    for ($i = 0; $i < 5; $i++) {
			    $new_email_verification_code .= substr($chars_to_generate_email_verification_code, mt_rand(0, strlen($chars_to_generate_email_verification_code) - 1), 1);
		    }
		    mysqli_query($link, "UPDATE `users` SET `email_verification_code` = '".$new_email_verification_code."' WHERE `email` = '".$email."'");
		    // Now sending new email_verification_code to his email
		    send_new_email_verification_code($email, $new_email_verification_code, $row['country_code']);
		    echo json_encode(array('error' => 0, 'info' => 'New email_verification_code has been sent to '.$email.'.'));
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
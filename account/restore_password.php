<?php

	require '/home/workspace/www/vendor/autoload.php';
    use Mailgun\Mailgun;
    
    // Notify user about restoring his password
    function send_email_about_restored_password($email, $new_password, $country_code) {
        $mg = new Mailgun("key-bf83a6907336282d92c094888c6554e3");
        $domain = "mailing.where-u-are.com";
        $subject = "";
        $message = "";
        if (strcasecmp($country_code, "ru") == 0) {
            $subject = "Восстановление пароля";
            $message = "Ваш пароль был успешено изменен на следующий: ".$new_password."\r\nДабы избежать несанкционированного доступа, как можно скорее смените пароль на новый.\r\n\r\nСпасибо, что выбрали именно нас :)\r\n---\r\nС уважением, команда \"Where U Are\"";
        } else {
            $subject = "Restoring password";
            $message = "Your password was successfully changed to following: ".$new_password."\r\nTo avoid unauthorized access change your password to new one as soon as possible.\r\n\r\nThank you for choosing us :)\r\n---\r\nBest regards, \"Where U Are\" team";
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
	
	mysqli_query($link, "SET NAMES 'utf8'");
	mysqli_query($link, "SET CHARACTER SET 'utf8'");
	mysqli_query($link, "SET SESSION collation_connection = 'utf8_general_ci'");
	
	// Checking if user with entered email address exists
	$result = mysqli_query($link, "SELECT * FROM `users` WHERE `email` = '".$email."'");
	if (mysqli_num_rows($result) != 0) {
	    // Ok, user exists. Changing his password to new random generated
	    $chars_to_generate_password = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
	    $new_generated_password = '';
	    for ($i = 0; $i <= 7; $i++) {
		    $new_generated_password .= substr($chars_to_generate_password, mt_rand(0, strlen($chars_to_generate_password) - 1), 1);
	    }
	    mysqli_query($link, "UPDATE `users` SET `password` = '".md5($new_generated_password)."', `api_token` = '' WHERE `email` = '".$email."'");
	    $row = mysqli_fetch_array($result);
	    // Notify user about new password by email
	    send_email_about_restored_password($email, $new_generated_password, $row['country_code']);
	    echo json_encode(array('error' => 0, 'info' => 'Your password was successfully changed. Look into your mailbox.'));
	} else {
	    echo json_encode(array('error' => 14, 'info' => 'User with that email address doesn\'t exists.'));
	}
	
	// Release data and disconnect from database
	mysqli_free_result($result);
	mysqli_close($link);

?>
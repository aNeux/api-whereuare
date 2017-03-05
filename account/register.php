<?php

	require '/home/workspace/www/vendor/autoload.php';
    use Mailgun\Mailgun;
	
	// Send Registration email to new user with email_verification_code
    function send_registration_email($email, $email_verification_code, $country_code) {
    	$mg = new Mailgun("key-bf83a6907336282d92c094888c6554e3");
    	$domain = "mailing.where-u-are.com";
    	$subject = "";
    	$message = "";
    	if (strcasecmp($country_code, "ru") == 0) {
        	$subject = "Регистрация в системе";
        	$message = "Спасибо за регистрацию в \"Where U Are\"! Наш сервис предоставляет возможность наблюдать за географическим положением Ваших родных и близких, где бы они ни находились. Все, что необходимо - это телефон или планшет с доступом к GPS и Интернет.\r\n\r\nДля окончания процедуры регистрации необходимо ввести следующий код подтверждения E-mail адреса в соответствующем окне приложения: ".$email_verification_code."\r\n\r\nСпасибо, что выбрали именно нас :)\r\n---\r\nС уважением, команда \"Where U Are\"";
    	} else {
        	$subject = "Registration in the system";
        	$message = "Thank you for registration in the \"Where U Are\"! Our service allow to follow geographical position of your relatives or nearest people in any place they are. All you need is phone or tablet with access to GPS and the Internet.\r\n\r\nTo complete the registration process, you must enter the following E-mail address confirmation code in the appropriate application window: ".$email_verification_code."\r\n\r\nThank you for choosing us :)\r\n---\r\nBest regards, \"Where U Are\" team";
    	}
    	$mg->sendMessage($domain, array('from' => 'Where U Are <noreply@where-u-are.com>', 
                            	'to'      => $email, 
                            	'subject' => $subject, 
                            	'text'    => $message));
    }
	
    $login = $_GET["login"];
    $email = $_GET["email"];
    $password = $_GET["password"];
	$first_name = $_GET["first_name"];
	$last_name = $_GET["last_name"];
	$country_code = $_GET['country_code'];
	$country = $_GET['country'];
	$city = $_GET['city'];
	$about = $_GET['about'];
    
	// Check important entered data
    if (strcmp($login, '') == 0 || strcmp($email, '') == 0 || strcmp($password, '') == 0 || strcmp($first_name, '') == 0 || strcmp($last_name, '') == 0 || strcmp($country_code, '') == 0 || strcmp($country, '') == 0 || strcmp($city, '') == 0) {
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
	
	// Checking if login is free
	$result = mysqli_query($link, "SELECT * FROM `users` WHERE `login` = '".$login."'");
	if (mysqli_num_rows($result) == 0) {
		// Login is free. Checking if email is free
		$result = mysqli_query($link, "SELECT * FROM `users` WHERE `email` = '".$email."'");
		if (mysqli_num_rows($result) == 0) {
			// Login and email are free. Generating email_verification_code
			$chars_to_generate_email_verification_code = "1234567890";
			$email_verification_code = '';
			for ($i = 0; $i < 5; $i++) {
				$email_verification_code .= substr($chars_to_generate_email_verification_code, mt_rand(0, strlen($chars_to_generate_email_verification_code) - 1), 1);
			}
			// Creating new account
			mysqli_query($link, "INSERT INTO `users` (`login`, `email`, `password`, `first_name`, `last_name`, `country_code`, `country`, `city`, `about`, `email_verification_code`, `online`) VALUES ('".$login."', '".$email."', '".$password."', '".$first_name."', '".$last_name."', '".$country_code."', '".$country."', '".$city."', '".$about."', '".$email_verification_code."', false)");
			// New account is created. Sending email to new user with email_verification_code
			send_registration_email($email, $email_verification_code, $country_code);
			echo json_encode(array('error' => 0, 'info' => 'Registration email with email_verification_code has been sent.'));
		} else {
			echo json_encode(array('error' => 4, 'info' => 'This email has already used.'));
		}
	} else {
		echo json_encode(array('error' => 3, 'info' => 'This login has already used.'));
	}
	
	// Release data and disconnect from database
	mysqli_free_result($result);
	mysqli_close($link);

?>
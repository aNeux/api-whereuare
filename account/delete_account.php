<?php

	require '/home/workspace/www/vendor/autoload.php';
    use Mailgun\Mailgun;

	// Notify user about deleting his account
    function send_email_about_deleting_account($email, $country_code) {
        $mg = new Mailgun("key-bf83a6907336282d92c094888c6554e3");
        $domain = "mailing.where-u-are.com";
        $subject = "";
        $message = "";
        if (strcasecmp($country_code, "ru") == 0) {
            $subject = "Аккаунт удален";
            $message = "Ваша учетная запись была успешно удалена. Нам очень жаль, что Вы отказались от наших услуг, но выбор всегда остается за пользователем. Тем не менее, желаем Вам удачи и успехов!\r\n\r\nСпасибо, что были с нами :)\r\n---\r\nС уважением, команда \"Where U Are\"";
        } else {
            $subject = "Account removed";
            $message = "Your account was successfully removed. It's a pity that you refused from our service, but anyway it's your choice. Wish you good luck and success!\r\n\r\nThank you for being with us :)\r\n---\r\nBest regards, \"Where U Are\" team";
        }
        $mg->sendMessage($domain, array('from' => 'Where U Are <noreply@where-u-are.com>', 
                                'to'      => $email, 
                                'subject' => $subject, 
                                 'text'    => $message));
    }

    $id = $_GET['id'];
    $api_token = $_GET['api_token'];
    $password = $_GET['password'];
    
    // Check all entered data
    if (strcmp($id, '') == 0 || strcmp($api_token, '') == 0 || strcmp($password, '') == 0) {
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
	        // Token is good. Checking password
	        if (strcmp($row['password'], $password) == 0) {
	            // Right password. Sending last email to user and deleting his account
	            send_email_about_deleting_account($row['email'], $row['country_code']);
	            mysqli_query($link, "DELETE FROM `users` WHERE `id` = ".$id);
	            echo json_encode(array('error' => 0, 'info' => "Your account was removed. It's a pity."));
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
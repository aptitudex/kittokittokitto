<?php
$GENDER = array(
    'male' => 'Male',
    'female' => 'Female',
);

$AGE = array();
for($i=5;$i<70;$i++)
{
    $AGE[$i] = $i;
}

switch($_REQUEST['state'])
{
	default:
	{
        array_unshift($GENDER,'Select one...');
        
        $renderer->assign('genders',$GENDER);
        $renderer->assign('ages',$AGE);
		$renderer->display('user/register_form.tpl');
		
		break;
	} // end default
	
	case 'process':
	{
		$ERRORS = array();
		
		if(!isset($_SESSION['security_code']))
		{
			$ERRORS[] = 'Internal CAPTCHA error. Please report this if it persists.';
		}
		elseif($_POST['captcha_code'] != $_SESSION['security_code'])
		{
			$ERRORS[] = "Incorrect security code specified.";
		}
				
		$USER = array(
			'user_name' => stripinput($_POST['user']['user_name']),
			'password' => $_POST['user']['password'], // Don't care about shit in here - the md5sum is what hits the db.
            'email' => stripinput($_POST['user']['email']),
            'age' => stripinput($_POST['user']['age']),
            'gender' => stripinput($_POST['user']['gender']),
		);
		
		if($USER['user_name'] == null)
		{
			$ERRORS[] = 'You cannot create an account with a blank username.';
		}
		elseif(preg_match('/^[A-Z0-9_!@#\$%\^&\*\(\)\.-]*$/i',$USER['user_name']) == false)
		{
			$ERRORS[] = 'Invalid characters in your username. Try A-Z0-9_!@#$%^&*().,;:.';
		}
		elseif(strlen($USER['user_name']) > 25)
		{
			$ERRORS[] = 'There is a maxlength=25 attribute on that input tag for a reason.';
		}
		else
		{
			$user_check = new User($db);
			$user_check = $user_check->findOneByUserName($USER['user_name']);
			
			if(is_a($user_check,'User') == true)
			{
				$ERRORS[] = 'That user name is already taken.';
			}
			
		} // end username is valid
		
		if($USER['password'] == null)
		{
			$ERRORS[] = 'Specified password was blank.';
		}
		elseif($USER['password'] != $_POST['user']['password_again'])
		{
			$ERRORS[] = 'Your password did not match the confirmation field.';
		}

        if(in_array($USER['age'],$AGE) == false)
        {
            $ERRORS[] = 'Invalid age specified.';
        }

        if(in_array($USER['gender'],array_keys($GENDER)) == false)
        {
            $ERRORS[] = 'Invalid gender specified.';
        }

        if($USER['email'] == null)
        {
            $ERRORS[] = 'E-mail address was blank.';
        }
        elseif(preg_match('/^[a-z0-9_+.]{1,64}@([a-z0-9-.]*){1,}\.[a-z]{1,5}$/i',$USER['email']) == false) // Doesn't adhere to RFC.
        {
            $ERRORS[] = 'Invalid e-mail address specified.';
        }
        
		if(sizeof($ERRORS) > 0)
		{
			draw_errors($ERRORS);
		}
		else
		{			
			// Create an user and set some base attrs.
			$new_user = new User($db);
			$new_user->setUserName($USER['user_name']);
			$new_user->setRegisteredIpAddr($_SERVER['REMOTE_ADDR']);
			$new_user->setPassword($USER['password']);
			$new_user->setAccessLevel('user');
            $new_user->setEmail($USER['email']);
            $new_user->setAge($USER['age']);
            $new_user->setGender($USER['gender']);
            $new_user->setProfile($USER['profile']);
            $new_user->setCurrency($APP_CONFIG['starting_funds']);
            $new_user->setUserTitle('User');
            $new_user->setTextareaPreference('tinymce');
            $new_user->setDatetimeCreated($new_user->sysdate());
			$new_user->save();
			
			// Log the user in and send him back home.
			$new_user->login();
			redirect('home');
		} // end success
			
		break;
	} // end process
} // end switch

?>

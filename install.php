<?php
if( is_file('classes/config.class.php') ) {
	header('Location: admin/');
	return;
}

if($_POST) {
	$host = $_POST['host'];
	$dbName = $_POST['db_name'];
	$username = $_POST['username'];
	$password = $_POST['password'];
	$n = 0;
	$s = false;
	
	//Check they all have a value
	foreach ($_POST as $key => $value) {
		if($value != '') {
			$n++;
		}
	}
	
	if($n >= 4) {
		$err = 0;
		$eStr = '';
		
		try {
			//Try connect to the database
			$dsn = 'mysql:host='.$host.';dbname='.$dbName.';port=3306;connect_timeout=15';
		   	$testDb = new PDO($dsn, $username, $password);               	
		} catch(PDOException $e) {
			$eStr =  $e->getMessage();
			$err = 1;
		}
		
       	if($err == 0) {
			//Write the config
			$file = file_get_contents('classes/config.class.sample.php');
			$file = str_replace("{DBHOST}", $host, $file);
			$file = str_replace("{DBNAME}", $dbName, $file);
			$file = str_replace("{DBUSER}", $username, $file);
			$file = str_replace("{DBPASSWORD}", $password, $file);
			
			//Check BaseUrl has http
			$baseUrl = $_POST['baseUrl'];
			if (strpos($baseUrl,'://') === false){
	            $baseUrl = 'http://'. $baseUrl;
	        }	        
	        
	        // Add the BaseUrl to the configuration and writing to it
			$file = str_replace("{BASEURL}", $baseUrl, $file);
			if(@file_put_contents('classes/config.class.php', $file)) {
				require_once('classes/config.class.php');
				require_once('classes/core.class.php');
				$core = Core::getInstance();
				$s = true; //Set success to true
			} else {
				$permission = substr(sprintf('%o', fileperms('classes')), -3);
				echo 'An Error occured, please ensure config.class.php is writeable.', '<br />';
				echo 'The current permissions for the directory is: ', $permission, '<br />';
				die;
			}

			//Perform the SQL
			$sqlQueries = file_get_contents('database/initial_database.sql');
			$q = $core->dbh->exec($sqlQueries);

			//Add the admin user
			$adminUser = $_POST['adminUser'];
			$adminPassword = $_POST['adminPassword'];

			$hashedPass = Core::getHash($adminUser,$adminPassword);
			$q = $core->dbh->prepare('INSERT INTO `users` (`username`,`password`, `role`) VALUES(?,?, 1)');
			$q->execute(array($adminUser,$hashedPass));

		}
	}
}
if(get_magic_quotes_gpc()) die('Please turn off magic quotes before installing the CMS, see <a href="http://php.net/manual/en/security.magicquotes.disabling.php">http://php.net/manual/en/security.magicquotes.disabling.php</a> for more information or contact your host.');
$suggestedUrl = $_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Install - CMS</title>
	<link href='http://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
	<script src="resources/scripts/jquery-1.10.1.min.js"></script>
	<link rel="stylesheet" type="text/css" href='resources/styles/style.css' />
</head>
<body>
	<div id="wrapper">
		<div id="header">
			<h1>CMS Installation</h1>
			<p>This will create your config and install the database</p>
		</div>
		<div id="main">
			<?php if($s == true) { ?><div id="success">Successfully created config file and setup database, you may view your admin at <a href="<?php echo $baseUrl.'/admin'; ?>"><?php echo $baseUrl.'/admin'; ?></a></div><?php } ?>
			<?php if($eStr) { ?><div id="error"><?php echo $eStr; ?></div><?php } ?>
			<h2>Please fill in your database details below</h2>
			<form action="" method="POST">
				<div class="item">
					<label>Base URL</label>
					<input type="text" value="<?php echo isset($baseUrl) ? $baseUrl : $suggestedUrl; ?>" name="baseUrl" />
					<div class="err">Please fill in a base URL value</div>
				</div>
				<div class="item">
					<label>Host</label>
					<input type="text" value="<?php echo isset($host) ? $host : '';?>" name="host" />
					<div class="err">Please fill in a host value</div>
				</div>
				<div class="item">
					<label>Database name</label>
					<input type="text" value="<?php echo isset($dbName) ? $dbName : '';?>" name="db_name" />
					<div class="err">Please fill in your database name</div>
				</div>
				<div class="item">
					<label>Username</label>
					<input type="text" value="<?php echo isset($username) ? $username : '';?>" name="username" />
					<div class="err">Please fill in your database username</div>
				</div>
				<div class="item">
					<label>Password</label>
					<input type="text" value="<?php echo isset($password) ? $password : '';?>" name="password" />
					<div class="err">Please fill in your password</div>
				</div>
				<div class="item">
					<label>Admin Username</label>
					<input type="text" value="<?php echo isset($adminUser) ? $adminUser : '';?>" name="adminUser" />
					<div class="err">Please fill in your admin username</div>
				</div>
				<div class="item">
					<label>Admin Password</label>
					<input type="text" value="<?php echo isset($adminPassword) ? $adminPassword : '';?>" name="adminPassword" />
					<div class="err">Please fill in your admin password</div>
				</div>
				<div class="item">
					<input type="submit" value="Submit" name="submit" />
				</div>
			</form>
			<script>
				$(document).ready(function() {
					$("#success").fadeIn(2000);
					$("#error").fadeIn(2000);
				});
				$("form").submit(function(e) {
					c = 0;
					$("form input[type=text]").each(function() {
						if($(this).val() == '') {
							c+=1;
							$(this).parent().children(".err").fadeIn("slow");
						} else {
							$(this).parent().children(".err").fadeOut("slow");
						}
					});
					if(c == 0) {
						return true;
					} else {
						return false;
					}
				});
			</script>
		</div>
	</div>
</body>
</html>

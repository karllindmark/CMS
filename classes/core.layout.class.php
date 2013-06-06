<?php

class CoreLayout {
	
	public static function getNav() {
		//Check if install file is still there
		if(file_exists('../install.php')) $insExists = '<div id="error"><b>Warning</b>: Please delete your install.php</div>';
		$nav = 	'<ul>
					<li><a href="'.Config::read("baseUrl").'/admin">Home</a></li>
					<li><a>Pages</a>
						<ul>
							<li><a href="admin/page">Create New Page</a></li>
							<li><a href="admin/edit-pages">Edit Pages</a></li>
						</ul>
					</li>
					<li class="logout"><a href="?logout">Logout</a></li>
				</ul>';
		if(isset($insExists)) $nav = $insExists.$nav;

		echo $nav;

	}

	//Takes eStr(Error string) and username as variable
	public static function loginPage($eStr, $adminUser) {
			echo self::buildHeader(array("jquery")).'
			<body id="admin">
				<did id="wrapper">
					<div id="header">
						<h1>Admin Login</h1>
					</div>
					<div id="main">
						'.(($eStr != '') ? '<div id="error">'.$eStr.'</div>' : '').'
						<h2>Please login below</h2>
						<form method="POST">
							<div class="item input">
								<label>Username</label>
								<input type="text" value="'. (($adminUser != '') ? $adminUser : '').'" name="username" />
								<div class="err">Please enter your username</div>
							</div>
							<div class="item input">
								<label>Password</label>
									<input type="password" value="" name="password" />
									<div class="err">Please enter your password</div>
							</div>
							<div class="item">
								<input type="submit" value="Login" name="login" />
							</div>
						</form>
						<script>
							$(document).ready(function() {
								$("#error").fadeIn("2000");
							});
							$("form").submit(function(e) {
								c = 0; //Error count for validation
								//Loop through input\'s to check they aren\'t empty, if so fade in the error message
								$("form .item.input input").each(function() {
									if($(this).val() == \'\') {
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
			</html>';
	}

	//Builds scripts and styles etc needed for header and echos it out
	public static function buildHeader($headerArray, $jQuery = false, $tinyMce = false) {
		$filesArray = array("jquery"=>"resources/scripts/jquery-1.10.1.min.js","tinymce"=>"resources/scripts/tinymce/tinymce.min.js");
		//Check which header scripts are needed
		if(in_array("jquery", $headerArray)) $jQuery = true;
		if(in_array("tinymce", $headerArray)) $tinyMce = true;

		//Build the header
		echo '<!DOCTYPE html>
				<html>
				<head>
					<meta charset="utf-8" />
					<title>Create Page - CMS</title>
					<base href="'.Config::read('baseUrl').'/" />
					<link href="http://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">
					'.(($jQuery === true) ? '<script src="resources/scripts/jquery-1.10.1.min.js"></script>' : '').
					(($tinyMce === true) ? '<script src="resources/scripts/tinymce/tinymce.min.js"></script>
					<script>
						tinymce.init({
						    selector: "textarea"
						 });
					</script>' : '').'					
					<link rel="stylesheet" type="text/css" href="resources/styles/style.css" />
				</head>';
	}

}

?>
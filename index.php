<?php
session_start();

include_once __DIR__ . "/class/NodeJsAuthBridge.php";

$nodeBridge = new \Nodejs\NodeJsAuthBridge();
$nodeBridge->setPath("/nodejs/NodeJsAuthBridge");
$isLoggedIn = false;

if (isset($_POST['sender'])) {
	unset($_POST['sender']);
	$nodeBridge->login($_POST);
	header('Location: index.php', true, 302);
} elseif (isset($_GET['action']) && $_GET['action'] === "logout") {
	$nodeBridge->logout();
	header('Location: index.php', true, 302);
} else {
	$isLoggedIn = $nodeBridge->isLoggedIn();
}

var_dump($_COOKIE);
var_dump($_SERVER['REQUEST_URI']);

?>
<html>
<head>
<meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="css/dropzone.css"></link>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="js/dropzone/dropzone.min.js"></script>
    <script>

        jQuery.support.cors = true;
        Dropzone.options.myAwesomeDropzone = {
            paramName: "file", // The name that will be used to transfer the file
            maxFilesize: 20000000000, // MB
            withCredentials: true,
            forceFallback: false,
            url: 'http://localhost:3000/nodejs/NodeJsAuthBridge/upload',
            accept: function(file, done) {
                done();
            }
        };


    </script>
<script>
	if (window.WebSocket) {
		var w = new WebSocket("ws://localhost:3101");
		w.onopen = function () {
			var login = "<?php if (isset($_COOKIE['login'])) echo $_COOKIE['login']; else echo ""; ?>";
			var data = "{\"login\":\"" + login + "\"}";
			w.send(data);
		};
		w.onerror = function (error) {
			// an error occurred when sending/receiving data
			console.log(error);
		};

		w.onmessage = function(e) {
			//console.log(e);
			console.log(e);

			if (e.data != "") {
				$("#list").html(e.data + "<br>");
			}
		};
	}
</script>
</head>

<body>
<?php if (!$isLoggedIn): ?>
<form method="POST">
	<input type="text" name="username">
	<input type="password" name="password">
	<input type="submit" name="sender">
</form>
<?php else: ?>
	<a href="index.php?action=logout">logout</a>

    <br>
    <br>

    <div class="dropzone" id="my-awesome-dropzone"></div>

<?php endif ?>

<div>
	<p id="list"></p>
</div>

</body>
</html>
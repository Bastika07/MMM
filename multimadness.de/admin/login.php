<?php
/* Benutzeranmeldung */
ob_start();
require('controller.php');
include_once "getsession.php";
include_once 'PHPMailer/PHPMailerAutoload.php';

if (isset($nLoginID) && $nLoginID > 0) {
	if (isset($_GET['sReferer']) && $_GET['sReferer'] != "") {
		$referer = $_GET['sReferer'];
		// Only allow relative paths (no scheme, no authority) to prevent open redirect attacks
		if (preg_match('/^[a-zA-Z0-9_.~\-\/%?&=#+!\'()*,;]+$/', $referer) && !preg_match('/^\/\/|:/', $referer)) {
			header("Location: " . $referer);
		} else {
			header("Location: index.php");
		}
	} else
		header("Location: index.php");
}
?>

<html>
<head>
  <title>PELAS.login</title>
  <link rel="stylesheet" type="text/css" href="style/style.css">
</head>
<body bgcolor="#FFFFFF" onLoad="document.forms.lForm.iLogin.focus();">

<table width="100%" height="100%">
  <tr>
    <td align="center">
      <img src="gfx/logo_gross.gif" alt="PELAS" style="border: 0; margin-bottom: 3em;"/>

      <h1>Login</h1>

<?php
include "pelasfront/login.php";
ob_flush();
?>

    </td>
  </tr>
</table>

</body>
</html>

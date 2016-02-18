<?php

/**
 * PHP file to login with code from Authenticator
 *
 * @author Norman Thimm (source based on Zarafa WebApp login.php)
 * @copyright Zarafa WebApp copyright
 * @license http://www.gnu.org/licenses/ GNU Affero General Public License
 */

	include("../../../config.php");

        session_name(COOKIE_NAME);
        session_start();

	$error = (isset($_SESSION['google2FALoggedOn']) && !$_SESSION['google2FALoggedOn']) ? TRUE : FALSE;

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
        <head>
                <meta name="Generator" content="Zarafa WebApp">
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <title>Zarafa WebApp</title>
                <link rel="stylesheet" type="text/css" href="../../../client/resources/css-extern/login.css">
                <link rel="icon" href="../../../client/resources/images/favicon.ico" type="image/x-icon">
                <link rel="shortcut icon" href="../../../client/resources/images/favicon.ico" type="image/x-icon">
                <script type="text/javascript">
                        window.onload = function(){
                                if (document.getElementById("token").value == ""){
                                        document.getElementById("token").focus();
                                }
                        }
                </script>
        </head>
	<body class="login">
                <table id="layout">
                        <tr><td>
                                <div id="login_main">
					<form action="logon.php" method="post"> 
                                		<div id="login_data">
							<p><?= !($error) ? $_SESSION['google2FAEcho']['boxTitle'] : "&nbsp;" ?></p>
							<p class="error"><?php
							        if ($error)
							                echo $_SESSION['google2FAEcho']['msgInvalidCode'];
								else
									echo ("&nbsp;");
                                                        ?></p>
                                                	<table id="form_fields">
                                                		<tr>
                                                			<td>
										<input type="text" name="token" id="token" placeholder="<?= $_SESSION['google2FAEcho']['txtCodePlaceholder']; ?>" class="inputelement">
									</td>
								</tr>
                                                		<tr>
                                                			<td class="button-row"><input id="submitbutton" class="button" type="submit" value="<?= $_SESSION['google2FAEcho']['butLogin']; ?>"></td>
                                                		</tr>
                                                	</table>
						</div>
					</form>
                                </div>
                        </td></tr>
                </table>
        </body>
</html>

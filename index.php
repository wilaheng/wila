<?php
define("LOG_LEVEL", -1); /* quality assurance */
define("DEBUG_MSG", false);

//define("REQUEST_URI", $_SERVER["REQUEST_URI"]); # root directory
//define("REQUEST_URI", substr($_SERVER["REQUEST_URI"], LENGTH)); # replace LENGTH with (path-length) to avoid useless function call
define("REQUEST_URI", ($_SERVER["SCRIPT_NAME"] == "/index.php") ? $_SERVER["REQUEST_URI"] : substr($_SERVER["REQUEST_URI"], strlen(dirname($_SERVER["SCRIPT_NAME"])))); # portable alternative. cost: 1 eval + 3 function call.

//define("EPR", "/var/opt");
define("EPR", strtr(dirname(__FILE__), "\\", "/")); # portable alternative.

define("FWK", EPR); # only if not in public folder

require_once FWK."/classes/FrontController.php";
require_once FWK."/classes/CGI.php";
?>
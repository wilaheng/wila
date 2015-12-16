<?php
define("LOG_LEVEL", -1); /* quality assurance */
define("DEBUG_MSG", false);

define("EPR", strtr(dirname(__FILE__), "\\", "/")); # portable alternative.

define("FWK", EPR); # only if not in public folder

require_once FWK."/classes/FrontController.php";
require_once FWK."/classes/CLI.php";
?>
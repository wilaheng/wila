<?php
return array(
    "PKG" => array("req" => RE_ADD, "type" => "int", "minv" => 1, "maxv" => 255),
    "CODE" => array("req" => RE_ADD, "type" => "alnum", "minl" => 2, "maxl" => 6),
    "NAME" => array("req" => RE_ADD),
    "CHECKED" => array("req" => RE_ADD, "type" => "digit")
);
?>
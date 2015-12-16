<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  wilaheng@gmail.com
 */
/**
 * @singleton
 * @protected
 */
final class api_DBG {
    static private $_map = array();
    static public function getMap() {return self::$_map;}
    static public function setMap(array $_map, array $_arg) {
        $_val = req()->getMap();
        unset($_val["context"]);
        foreach($_val as $k => $v) {
            $type = $minl = $maxl = $minv = $maxv = STR_EMPTY;
            if (isset($_arg[$k])) {
                if (isset($_arg[$k]["type"])) $type = strtoupper($_arg[$k]["type"]);
                if (isset($_arg[$k]["minl"])) $minl = _2nf($_arg[$k]["minl"]);
                if (isset($_arg[$k]["maxl"])) $maxl = _2nf($_arg[$k]["maxl"]);
                if (isset($_arg[$k]["minv"])) $minv = _2nf($_arg[$k]["minv"]);
                if (isset($_arg[$k]["maxv"])) $maxv = _2nf($_arg[$k]["maxv"]);
            }
            $checked = isset($_map[$k]);
            array_push(self::$_map, array(
                array("value" => ($checked) ? "Y" : "N", "align" => "center"),
                $k,
                ($checked) ? $_map[$k] : $v,
                array("value" => (isset($_arg[$k]["req"]) && $_arg[$k]["req"]) ? "Y" : "N", "align" => "center"),
                array("value" => $type, "align" => "center"),
                array("value" => $minl, "align" => "right"),
                array("value" => $maxl, "align" => "right"),
                array("value" => $minv, "align" => "right"),
                array("value" => $maxv, "align" => "right")
            ));
        }
    }
}
?>
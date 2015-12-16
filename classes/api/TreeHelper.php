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
 * @protected
 */
final class api_TreeHelper {
    private $a = array();
    public function __set($a, $b) {
        $this->a[$a] = $b;
    }
    public function __get($a) {
        return (!isset($this->a[$a]) || !($this->a[$a] instanceof api_TreeHelper)) ? ($this->a[$a] = new api_TreeHelper()) : $this->a[$a];
    }
    public function pack() {
        $o = array();
        foreach($this->a as $k => $v) {
            $o[$k] = ($v instanceof api_TreeHelper) ? $v->pack() : $v;
        }
        return $o;
    }
}
?>
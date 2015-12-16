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
final class api_mysqli_Connection extends Connection {
    public function __construct($a, $n) {
        $_ = false;
        if (!empty($a->c)) $a->h.= ":" . $a->c;
        if ($a->h && $a->u && $a->p) {
            $_ = mysqli_connect($a->h, $a->u, $a->p);
        } elseif ($a->h && $a->u) {
            $_ = mysqli_connect($a->h, $a->u);
        } else {
            $_ = mysqli_connect($a->h);
        }
        if ($_ == false) throw new Exception(sprintf("ConnectionException: %s", $a->h));
        if (!mysqli_select_db($_, $a->d)) throw new Exception(mysqli_error($_));
        $this->d = $a->d;
        $this->_ = $_;
    }
    public function __destruct() {$this->close();}
    public function close() {
        if (is_resource($this->_)) mysqli_close($this->_);
    }
    public function getDBName() {return $this->d;}
    public function getId($name) {}
    public function getInsertId() {return mysqli_insert_id($this->_);}
    public function getError($e = null) {return mysqli_error($this->_);}
    public function executeQuery() {
        $j = func_num_args();
        $q = func_get_arg(0);
        if ($j > 1) {
            for($i = 1, $v = null; $i < $j; $i++) $v[] = $this->escape(func_get_arg($i));
            $q = vsprintf($q, $v);
        }
        if (!mysqli_select_db($this->_, $this->d)) throw new Exception($this->getError());
        $b = mysqli_query($this->_, $q);
        if (!$b) throw new Exception($this->getError() . "\n\n" . $q);
        return new api_mysqli_ResultSet($this, $b);
    }
    public function executeUpdate() {
        $j = func_num_args();
        $q = func_get_arg(0);
        if ($j > 1) {
            for($i = 1, $v = null; $i < $j; $i++) $v[] = func_get_arg($i);
            $q = vsprintf($q, $v);
        }
        if (!mysqli_select_db($this->_, $this->d)) throw new Exception($this->getError());
        if (!mysqli_query($this->_, $q)) throw new Exception($this->getError() . "\n\n" . $q);
        return intval(mysqli_affected_rows($this->_));
    }
    protected function begin_() {
        if (!mysqli_select_db($this->_, $this->d)) throw new Exception($this->getError());
        if (!mysqli_autocommit($this->_, false)) throw new Exception($this->getError());
    }
    protected function commit_() {
        if (!mysqli_select_db($this->_, $this->d)) throw new Exception($this->getError());
        if (!mysqli_commit($this->_)) throw new Exception($this->getError());
        mysqli_autocommit($this->_, true);
    }
    protected function rollback_() {
        if (!mysqli_select_db($this->_, $this->d)) throw new Exception($this->getError());
        if (!mysqli_rollback($this->_)) throw new Exception($this->getError());
        mysqli_autocommit($this->_, true);
    }
    public function escape($v) {
        return mysqli_real_escape_string($this->_, $v);
    }
}
/**
 * @protected
 */
final class api_mysqli_ResultSet extends ResultSet {
    public function __destruct() {$this->close();}
    public function close() {
        mysqli_free_result($this->b);
    }
    public function getRecordCount() {
        $a = mysqli_num_rows($this->b);
        return ($a == false) ? 0 : intval($a);
    }
    public function seek($p) {
        if (!mysqli_data_seek($this->b, $p)) return false;
        $this->c = $p;
        return true;
    }
    public function next() {
        if ($this->f = mysqli_fetch_assoc($this->b)) {
            $this->c++;
            return true;
        }
        $this->afterLast();
        return false;
    }
}
?>
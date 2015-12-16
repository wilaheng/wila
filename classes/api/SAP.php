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
final class api_SAP {
    private $conn, $conf, $func, $user, $init = array();
    private $curr_init = true;
    private $seqn = 1;
    private $VR, $VC, $CC, $PC;
    private function initTable($n) {
        if (!isset($this->init[$n])) {
            saprfc_table_init($this->func, $n);
            $this->init[$n] = true;
        }
    }
    public function connect($func, $CDSA) {
        $conn = inf()->sap->conn;
        $conn["USER"] = $_SESSION["SAP_USERNAME"];
        $conn["PASSWD"] = $_SESSION["SAP_PASSWORD"];
        $this->user = $conn["USER"];
        $this->conn = saprfc_open($conn);
        if (!$this->conn) throw new Exception("SAPRFC CONNECTION FAILED");
        $this->func = saprfc_function_discover($this->conn, $func);
        if (!$this->func) {
            throw new Exception(sprintf("DISCOVERING INTERFACE OF FUNCTION %s MODULE FAILED", $func));
        }
        $this->conf = inf()->sap->conf;
        $rs = DB::lookup("vfs")->executeQuery(
            "SELECT * FROM sap_conf WHERE AREA_SUB='%s'", $CDSA
        );
        if ($rs->next()) {
            $this->conf["BUS_AREA"] = $rs->get("BUS_AREA");
            $this->VR = $rs->get("VENDOR");
            $this->VC = $rs->get("VENDOR_CO");
            $this->CC = $rs->get("COST_CENTER");
            $this->PC = $rs->get("PROFIT_CENTER");
        } else {
            throw new Exception(sprintf("LOADING SAP CONFIG FOR FUNCTION %s MODULE FAILED", $func));
        }
    }
    public function hdr($n, array $a) {
        $this->conf["COMP_CODE"] = $a["COMP_CODE"];
        $a["USERNAME"] = $this->user;
        $this->set($this->func, $n, $a);
    }
    public function getExport($n) {return saprfc_export($this->func, $n);}
    public function set($n, array $a) {saprfc_import($this->func, $n, $a);}
    public function setValue($n, $v) {saprfc_import($this->func, $n, $v);}
    public function add($n, $m, array $a) {
        $curr = "T_CURRENCYAMOUNT";
        $this->initTable($n);
        $j = str_pad($this->seqn++, 10, "0", STR_PAD_LEFT);
        $a["ITEMNO_ACC"] = $j;
        if ($n !== $curr) foreach($this->conf as $k => $v) $a[$k] = $v;
        saprfc_table_append($this->func, $n, $a);
        if ($this->curr_init) {
            $this->initTable($curr);
            $this->curr_init = false;
        }
        saprfc_table_append($this->func, $curr, array("ITEMNO_ACC" => $j, "CURRENCY" => "IDR", "AMT_DOCCUR" => $m));
    }
    public function call() {
        $r = saprfc_call_and_receive($this->func);
        if ($r != SAPRFC_OK) {
            throw new Exception(($r == SAPRFC_EXCEPTION) ? saprfc_exception($this->func) : saprfc_error($this->func));
        }
    }
    public function get($n) {
        $j = array();
        $r = saprfc_table_rows($this->func, $n);
        for($i = 1; $i <= $r; $i++) array_push($j, saprfc_table_read($this->func, $n, $i));
        return $j;
    }
    public function getUser() {return $this->user;}
    public function getVR() {return $this->VR;}
    public function getVC() {return $this->VC;}
    public function getCC() {return $this->CC;}
    public function getPC() {return $this->PC;}
    public function __destruct() {
        saprfc_function_free($this->func);
        saprfc_close($this->conn);
    }
}
?>
<?php
/**
 * super duper simplified version of java-impl sql builder.
 *
 * idenya sederhana, menghilangkan sama sekali setter/getter pada DML berbasis object
 * untuk mereduksi penulisan syntax DML to the max :D
 *
 * versi PHP ini tidak mendukung automate-join, tanpa perhitungan cost path (berdasarkan PK/FK + indexes),
 * tanpa semua fitur versi java kecuali untuk membentuk SQL.
 *
 * <code>
 * $argv = dbm()->vfs_packages->argv(); // pemetaan parameter (GET/POST) sesuai constraint ada disini.
 * $conn = DB::lookup("vfs");
 * try {
 *     $conn->begin();
 *     $conn->vfs_packages($argv)->insert();
 *     $conn->commit();
 * } catch(Exception $e) {
 *     $conn->rollback();
 * }
 * </code>
 *
 * tidak peduli berapa banyak column pada tabel vfs_packages (on-the-fly eval via magic method)
 * seluruh constraint field ada pada metadata. expressive to the max :D
 *
 * @author  wilaheng@gmail.com
 * @version 1.1, 02/07/13
 */

/**
 * A table of data representing a database result set, which
 * is usually generated by executing a statement that queries the database.
 *
 * <P>A <code>ResultSet</code> object  maintains a cursor pointing
 * to its current row of data.  Initially the cursor is positioned
 * before the first row. The <code>next</code> method moves the
 * cursor to the next row, and because it returns <code>false</code>
 * when there are no more rows in the <code>ResultSet</code> object,
 * it can be used in a <code>while</code> loop to iterate through
 * the result set.
 * <P>
 *
 * A default <code>ResultSet</code> object is not updatable and
 * has a cursor that moves forward only.  Thus, you can
 * iterate through it only once and only from the first row to the
 * last row.
 * <PRE>
 *       $rset = $conn->executeQuery("SELECT A, B FROM TABLE_NAME");
 * </PRE>
 *
 * The <code>ResultSet</code> interface provides
 * <i>getter</i> methods (<code>getInt</code>, <code>getFloat</code>, and so on)
 * for retrieving column values from the current row.
 *
 * <P>Column names used as input to getter methods are case
 * insensitive.  When a getter method is called  with
 * a column name and several columns have the same name,
 * the value of the first matching column will be returned.
 * The column name option is
 * designed to be used when column names are used in the SQL
 * query that generated the result set.
 * For columns that are NOT explicitly named in the query, it
 * is best to use column numbers. If column names are used, the
 * programmer should take care to guarantee that they uniquely refer to
 * the intended columns, which can be assured with the SQL <i>AS</i> clause.
 * <P>
 *
 * @protected
 */
abstract class ResultSet {
    protected $_, $a, $b, $c = 0, $f, $m = array();
    public function __construct(Connection $_, $b) {
        $this->_ = $_;
        $this->b = $b;
    }

    /**
     * auto lookup column (menghindari join untuk mengurangi cost eksekusi)
     */
    public function setMap($n, array $m) {$this->m[$n] = $m;}

    /**
     * Moves the cursor to the previous row in this
     * <code>ResultSet</code> object.
     * <p>
     * When a call to the <code>previous</code> method returns <code>false</code>,
     * the cursor is positioned before the first row.  Any invocation of a
     * <code>ResultSet</code> method which requires a current row will result in a
     * <code>SQLException</code> being thrown.
     * <p>
     * If an input stream is open for the current row, a call to the method
     * <code>previous</code> will implicitly close it.  A <code>ResultSet</code>
     *  object's warning change is cleared when a new row is read.
     * <p>
     *
     * @return <code>true</code> if the cursor is now positioned on a valid row;
     * <code>false</code> if the cursor is positioned before the first row
     */
    public function prev() {
        $r = $this->seek($this->c - 2);
        if ($r === false) {
            $this->beforeFirst();
            return false;
        }

        return $this->next();
    }

    /**
     * Moves the cursor a relative number of rows, either positive or negative.
     * Attempting to move beyond the first/last row in the
     * result set positions the cursor before/after the
     * the first/last row. Calling <code>relative(0)</code> is valid, but does
     * not change the cursor position.
     *
     * <p>Note: Calling the method <code>relative(1)</code>
     * is identical to calling the method <code>next()</code> and
     * calling the method <code>relative(-1)</code> is identical
     * to calling the method <code>previous()</code>.
     *
     * @param rows an <code>int</code> specifying the number of rows to
     *        move from the current row; a positive number moves the cursor
     *        forward; a negative number moves the cursor backward
     * @return <code>true</code> if the cursor is on a row;
     *         <code>false</code> otherwise
     */
    public function relative($o) {
        $p = $this->c + ($o - 1);
        $q = $this->seek($p);
        if ($q === false) {
            if ($p < 0) {
                $this->beforeFirst();
            } else {
                $this->afterLast();
            }
        } else {
            $q = $this->next();
        }

        return $q;
    }

    /**
     * Moves the cursor to the given row number in
     * this <code>ResultSet</code> object.
     *
     * <p>If the row number is positive, the cursor moves to
     * the given row number with respect to the
     * beginning of the result set.  The first row is row 1, the second
     * is row 2, and so on.
     *
     * <p>If the given row number is negative, the cursor moves to
     * an absolute row position with respect to
     * the end of the result set.  For example, calling the method
     * <code>absolute(-1)</code> positions the
     * cursor on the last row; calling the method <code>absolute(-2)</code>
     * moves the cursor to the next-to-last row, and so on.
     *
     * <p>An attempt to position the cursor beyond the first/last row in
     * the result set leaves the cursor before the first row or after
     * the last row.
     *
     * <p><B>Note:</B> Calling <code>absolute(1)</code> is the same
     * as calling <code>first()</code>. Calling <code>absolute(-1)</code>
     * is the same as calling <code>last()</code>.
     *
     * @param row the number of the row to which the cursor should move.
     *        A positive number indicates the row number counting from the
     *        beginning of the result set; a negative number indicates the
     *        row number counting from the end of the result set
     * @return <code>true</code> if the cursor is moved to a position in this
     * <code>ResultSet</code> object;
     * <code>false</code> if the cursor is before the first row or after the
     * last row
     */
    public function absolute($p) {
        $q = $this->seek($p - 1);
        if ($q === false) {
            if ($p - 1 < 0) {
                $this->beforeFirst();
            } else {
                $this->afterLast();
            }
        } else {
            $q = $this->next();
        }

        return $q;
    }

    /**
     * Moves the cursor to the first row in
     * this <code>ResultSet</code> object.
     *
     * @return <code>true</code> if the cursor is on a valid row;
     * <code>false</code> if there are no rows in the result set
     * @exception SQLException if a database access error
     * occurs; this method is called on a closed result set
     * or the result set type is <code>TYPE_FORWARD_ONLY</code>
     */
    public function first() {
        if ($this->c !== 0) $this->seek(0);

        return $this->next();
    }

    /**
     * Moves the cursor to the last row in
     * this <code>ResultSet</code> object.
     *
     * @return <code>true</code> if the cursor is on a valid row;
     * <code>false</code> if there are no rows in the result set
     * @exception SQLException if a database access error
     * occurs; this method is called on a closed result set
     * or the result set type is <code>TYPE_FORWARD_ONLY</code>
     */
    public function last() {
        if ($this->c !== ($p = $this->getRecordCount() - 1)) $this->seek($p);

        return $this->next();
    }

    /**
     * <p>Gets the value of the designated column in the current row
     * of this <code>ResultSet</code> object as
     * an <code>Object</code> in the Java programming language.
     *
     * <p>This method will return the value of the given column as a
     * Java object.  The type of the Java object will be the default
     * Java object type corresponding to the column's SQL type,
     * following the mapping for built-in types specified in the JDBC
     * specification. If the value is an SQL <code>NULL</code>,
     * the driver returns a Java <code>null</code>.
     *
     * <p>This method may also be used to read database-specific
     * abstract data types.
     */
    public function get($j, $default = null) {
        if (!array_key_exists($j, $this->f)) throw new Exception("ResultSetException: Unknown Column " . $j);
        $v = $this->f[$j];
        if ($v == null && $default !== null) $v = $default;

        return $v;
    }

    public function __get($a) {return $this->get($a);}

    public function withMap($j) {
        $v = $this->get($j);
        return (isset($this->m[$j])) ? $this->m[$j][$v] : $v;
    }
    public function getCell($j, $default = null,
        $align = null, $callback = null, $params = null)
    {
        return $this->setCell($this->get($j, $default), $align, $callback, $params, false);
    }
    public function setCell($j, $align = null, $callback = null,
        $params = null, $_blank = true)
    {
        $v = array("value" => $j);
        if ($align != null) $v["align"] = $align;
        if ($callback != null) $v["callback"] = $callback;
        if ($params != null) $v["params"] = $params;
        if ($_blank) $v["_blank"] = true;

        return $v;
    }

    /**
     * Moves the cursor to the front of
     * this <code>ResultSet</code> object, just before the
     * first row. This method has no effect if the result set contains no rows.
     *
     * @exception SQLException if a database access error
     * occurs; this method is called on a closed result set or the
     * result set type is <code>TYPE_FORWARD_ONLY</code>
     */
    public function beforeFirst() {$this->c = 0;}

    /**
     * Moves the cursor to the end of
     * this <code>ResultSet</code> object, just after the
     * last row. This method has no effect if the result set contains no rows.
     * @exception SQLException if a database access error
     * occurs; this method is called on a closed result set
     * or the result set type is <code>TYPE_FORWARD_ONLY</code>
     */
    public function afterLast() {$this->c = $this->getRecordCount() + 1;}
    public function getRow() {return $this->f;}

    /**
     * Retrieves the value of the designated column in the current row
     * of this <code>ResultSet</code> object as
     * an <code>int</code> in the Java programming language.
     *
     * @param columnIndex the first column is 1, the second is 2, ...
     * @return the column value; if the value is SQL <code>NULL</code>, the
     * value returned is <code>0</code>
     */
    public function getInt($n) {return intval($this->get($n, 0));}

    /**
     * Retrieves the value of the designated column in the current row
     * of this <code>ResultSet</code> object as
     * a <code>float</code> in the Java programming language.
     *
     * @param columnIndex the first column is 1, the second is 2, ...
     * @return the column value; if the value is SQL <code>NULL</code>, the
     * value returned is <code>0</code>
     */
    public function getFloat($n) {return floatval($this->get($n, 0));}

    /**
     * reset pointer.
     */
    public function rewind() {if (!$this->beforeFirst()) $this->seek(0);}

    /**
     * Check connection.
     */
    public function valid() {return $this->next();}

    /**
     * pointer position.
     */
    public function key() {return $this->c;}

    /**
     * current pointer.
     */
    public function current() {return $this->getRow();}

    /**
     * generates a url-encoded query string from the current pointed fields
     */
    public function httpQuery() {
        $j = func_num_args();
        $q = STR_EMPTY;
        if ($j > 0) {
        for($i = 0, $v = false, $n = null; $i < $j; $i++) {
            $n = func_get_arg($i);
            if ($v) $q.= "&"; else $v = true;
            $q.= $n;
            $q.= "=";
            $q.= $this->get($n, STR_EMPTY);
        }}

        return $q;
    }

    /**
     * raw fields.
     */
    public function getMap() {return $this->f;}

    abstract public function seek($p);

    /**
     * forward pointer.
     */
    abstract public function next();

    /**
     * total rows.
     */
    abstract public function getRecordCount();

    /**
     * close resultset.
     */
    abstract public function close();

    /**
     * generate INSERT statement from the current fields.
     */
    public function insertStatement() {
        $m = $this->getMap();
        $k = implode(",", array_keys($m));
        $v = implode("','", array_values($m));

        return sprintf("INSERT INTO %s(%s) VALUES ('%s');\r\n", $this->_->from, $k, $v);
    }
}

/**
 * @protected
 */
abstract class Connection {
    protected $_, $d, $c = 0;
    private $args, $orby, $limit = 0, $offset = 0;
    public $from;
    public function getConnection() {return $this->_;}
    public function begin() {
        if ($this->c == 0) $this->begin_();
        $this->c++;
    }
    public function commit() {
        if ($this->c > 0) {
            if ($this->c == 1) $this->commit_();
            $this->c--;
        }
    }
    public function rollback() {
        if ($this->c > 0) {
            if ($this->c == 1) $this->rollback_();
            $this->c--;
        }
    }
    public function __get($from) {
        $this->from = $from;

        return $this;
    }
    public function __call($from, $args) {
        $this->from = $from;
        $this->args = $args;

        return $this;
    }
    public function slice() {
        $n = func_num_args();
        $f = func_get_args();
        $r = array();
        for($i = 0, $j; $i < $n; $i++) {
            $j = $f[$i];
            $r[$j] = $this->args[0][$j];
            unset($this->args[0][$j]);
        }

        return $r;
    }
    public function orderBy($by) {
        $this->orby = $by;

        return $this;
    }
    public function limit($limit, $offset) {
        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }
    public function replace() {return $this->create("REPLACE");}
    public function insert() {return $this->create("INSERT");}
    public function update(array $args = null) {
        if (sizeof($this->args) > 0) {
            $argv = array_shift($this->args);
            $_set = array();
            foreach($argv as $k => $v) {
                if (!preg_match("/^[a-zA-Z0-9_]+$/", $k)) throw new Exception(inf()->alnum($k, $k));
                $m = null;
                if ($v instanceof StringHelper) {
                    $m = sprintf("`%s`=%s", $k, $v->toString());
                } else {
                    $m = sprintf("`%s`='%s'", $k, $this->escape($v));
                }
                array_push($_set, $m);
            }
            $r = $this->executeUpdate(sprintf(
                "UPDATE %s SET %s %s", $this->from, implode(",", $_set), (is_null($args)) ? STR_EMPTY : $this->where($args)
            ));
        }

        return $r;
    }
    public function delete() {
        $r = $this->executeUpdate(sprintf(
            (empty($this->args)) ? sprintf("TRUNCATE %s", $n) : sprintf("DELETE FROM %s %s", $this->from, $this->where(array_shift($this->args)))
        ));

        return $r;
    }
    public function select($columns = "*") {
        $_sql = sprintf("SELECT %s FROM %s", $columns, $this->from);
        if (!empty($this->args)) $_sql.= $this->where(array_shift($this->args));
        if (!empty($this->orby)) $_sql.= sprintf(" ORDER BY %s", $this->orby);
        if (!empty($this->limit)) {
            if ($this instanceof api_oci_Connection) {
                $_sql = "SELECT B.* FROM (SELECT A.*, rownum AS SQL$ROWNUM FROM (" . $_sql . ") A) B WHERE ";
                if ($this->offset > 0) {
                    $_sql .= " B.SQL$ROWNUM > " . $this->offset;
                    if ($this->limit > 0) {
                        $_sql .= " AND B.SQL$ROWNUM <= " . ($this->offset + $this->limit);
                    } 
                } else {
                    $_sql .= " B.SQL$ROWNUM <= " . $this->limit;
                } 
            } else {
                $_sql.= sprintf(" LIMIT %s, %s", $this->offset, $this->limit);
            }
        }

        return $this->executeQuery($_sql);
    }
    private function create($methodName) {
        $r = 0;
        if (sizeof($this->args) > 0) {
            $argv = array_shift($this->args);
            $keys = $vals = array();
            foreach($argv as $k => $v) {
                if (!preg_match("/^[a-zA-Z0-9_]+$/", $k)) throw new Exception(inf()->alnum($k, $k));
                array_push($vals, ($v instanceof StringHelper) ? $v->toString() : "'".$this->escape($v)."'"); # no sprintf
                array_push($keys, $k);
            }
            $r = $this->executeUpdate(sprintf(
                "%s INTO %s (`%s`) VALUES (%s)", $methodName, $this->from, implode("`,`", $keys), implode(",", $vals)
            ));
            $this->r = false;
        }

        return $r;
    }
    private function where($args) {
        $a = false;
        $q = " WHERE ";
        if (is_array($args)) {
            foreach($args as $k => $v) {
                if ($a) $q.= " AND "; else $a = true;
                if ($v instanceof StringHelper) $q.= sprintf("`%s`=%s", $k, $this->escape($v));
                else {
                    $q.= sprintf("`%s`='%s'", $k, $this->escape($v));
                }
            }
        } else {
            $q.= $args;
        }

        return $q;
    }
    public function reset() {
        $this->from = $this->args = $this->orby = null;
    }

    /**
     * Escape string
     */
    public function escape($v) {return addslashes($v);}

    /**
     * BEGIN TRANSACTION
     */
    abstract protected function begin_();

    /**
     * Makes all changes made since the previous commit/rollback permanent and
     * releases any database locks currently held by this <code>Connection</code>
     * object.
     * This method should be used only when auto-commit mode has been disabled.
     */
    abstract protected function commit_();

    /**
     * Undoes all changes made in the current transaction and releases any
     * database locks currently held by this <code>Connection</code> object.
     * This method should be  used only when auto-commit mode has been disabled.
     */
    abstract protected function rollback_();

    abstract public function getDBName();
    abstract public function getId($name);

    /**
     * Retrieves any auto-generated keys created as a result of executing this
     * <code>Statement</code> object. If this <code>Statement</code> object did
     * not generate any keys, an empty <code>ResultSet</code>
     * object is returned.
     *
     * <p><B>Note:</B>If the columns which represent the auto-generated keys were not specified,
     * the JDBC driver implementation will determine the columns which best represent the auto-generated keys.
     *
     * @return  last auto-generated key(s) generated by the execution of this <code>Connection</code> object
     */
    abstract public function getInsertId();

    /**
     * Executes the given SQL statement, which returns a single
     * <code>ResultSet</code> object.
     *
     * @param sql an SQL statement to be sent to the database, typically a
     *        static SQL <code>SELECT</code> statement
     * @return a <code>ResultSet</code> object that contains the data produced
     *         by the given query; never <code>null</code>
     */
    abstract public function executeUpdate();

    /**
     * Executes the given SQL statement, which may be an <code>INSERT</code>,
     * <code>UPDATE</code>, or <code>DELETE</code> statement or an
     * SQL statement that returns nothing, such as an SQL DDL statement.
     *
     * @param sql an SQL Data Manipulation Language (DML) statement, such as <code>INSERT</code>, <code>UPDATE</code> or
     * <code>DELETE</code>; or an SQL statement that returns nothing,
     * such as a DDL statement.
     *
     * @return either (1) the row count for SQL Data Manipulation Language (DML) statements
     *         or (2) 0 for SQL statements that return nothing
     */
    abstract public function executeQuery();
    abstract public function getError($e = null);
}

/**
 * @singleton
 * @protected
 */
final class DB {
    static private $conn = array();
    static private $cons = array();
    static private $_map = array();
    static public function rollback()
    {
        foreach(self::$conn as $k => $c) $c->rollback();
    }
    static public function lookup($n, $u = false) {
        $v = new stdClass();
        if (!isset(inf()->datasource->$n)) throw new Exception(sprintf("DataSource '%s' does not exist", $n));
        if ($u) {
            if (isset(self::$_map[$n])) return self::$_map[$n];
        }
        $x = inf()->datasource->$n;
        $i = parse_url($x);
        if (!isset($i["scheme"]) || !isset($i["path"])) throw new Exception(sprintf("InvalidURL: %s", $x));
        $q = null;
        if (isset($i["query"])) parse_str($i["query"], $q);
        $v = (object) array(
            "u" => $i["user"],
            "p" => (isset($i["pass"])) ? $i["pass"] : null,
            "h" => $i["host"],
            "c" => (isset($i["port"])) ? $i["port"] : null,
            "d" => substr($i["path"], 1),
            "q" => $q,
            "f" => (isset($i["fragment"])) ? $i["fragment"] : null
        );
        $t = $i["scheme"];
        require_once sprintf("api/DB.%s.php", $t);
        $n = sprintf("api_%s_Connection", $t);
        $c = new $n($v, $x);
        array_push(self::$conn, $c);
        if ($u) {
            self::$_map[$n] = $c;
        }

        return $c;
    }
    static public function constant($n) {
        if (!isset(self::$cons[$n])) {
            self::$cons[$n] = new StringHelper($n);
        }

        return self::$cons[$n];
    }
}
?>
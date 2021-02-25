<?php

/**
 * Created by PhpStorm.
 * User: Dani
 * Date: 29/06/2019
 * Time: 10:45
 */
class Database extends PDO
{
    private $error;
    private $sql;
    private $bind;
    private $errorCallbackFunction;
    private $errorMsgFormat;

    public function __construct($dsn, $user = "", $passwd = "", $persistent = true)
    {
        $options = array(
            PDO::ATTR_PERSISTENT => $persistent,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        try {
            parent::__construct($dsn, $user, $passwd, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
    }

    private function debug()
    {
        if (!empty($this->errorCallbackFunction)) {
            $error = array("Error" => $this->error);
            if (!empty($this->sql))
                $error["SQL Statement"] = $this->sql;
            if (!empty($this->bind))
                $error["Bind Parameters"] = trim(print_r($this->bind, true));

            $backtrace = debug_backtrace();
            if (!empty($backtrace)) {
                foreach ($backtrace as $info) {
                    if ($info["file"] != __FILE__)
                        $error["Backtrace"] = $info["file"] . " at line " . $info["line"];
                }
            }

            $msg = "";
            if ($this->errorMsgFormat == "html") {
                if (!empty($error["Bind Parameters"]))
                    $error["Bind Parameters"] = "<pre>" . $error["Bind Parameters"] . "</pre>";
                $css = trim(file_get_contents(dirname(__FILE__) . "/error.css"));
                $msg .= '<style type="text/css">' . "\n" . $css . "\n</style>";
                $msg .= "\n" . '<div class="db-error">' . "\n\t<h3>SQL Error</h3>";
                foreach ($error as $key => $val)
                    $msg .= "\n\t<label>" . $key . ":</label>" . $val;
                $msg .= "\n\t</div>\n</div>";
            } elseif ($this->errorMsgFormat == "text") {
                $msg .= "SQL Error\n" . str_repeat("-", 50);
                foreach ($error as $key => $val)
                    $msg .= "\n\n$key:\n$val";
            }

            $func = $this->errorCallbackFunction;
            $func($msg);
        }
    }

    public function delete($table, $where, $bind = "")
    {
        if (!empty($where))
            $where_clause = $this->where_clause($where);
        $sql = "DELETE FROM `" . $table . "`" . $where_clause . ";";
        //echo $sql;
        $this->run($sql, $bind);
    }

    private function filter($table, $info)
    {
        $driver = $this->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver == 'sqlite') {
            $sql = "PRAGMA table_info('" . $table . "');";
            $key = "name";
        } elseif ($driver == 'mysql') {
            $sql = "DESCRIBE " . $table . ";";
            $key = "Field";
        } else {
            $sql = "SELECT column_name FROM information_schema.columns WHERE table_name = '" . $table . "';";
            $key = "column_name";
        }

        if (false !== ($list = $this->run($sql))) {
            $fields = array();
            foreach ($list as $record)
                $fields[] = $record[ $key ];

            return array_values(array_intersect($fields, array_keys($info)));
        }

        return array();
    }

    private function cleanup($bind)
    {
        if (!is_array($bind)) {
            if (!empty($bind))
                $bind = array($bind);
            else
                $bind = array();
        }

        return $bind;
    }

    public function insert($table, $info)
    {
        $fields = $this->filter($table, $info);
        $sql = "INSERT INTO " . $table . " (" . implode($fields, ", ") . ") VALUES (:" . implode($fields, ", :") . ");";
        $bind = array();
        foreach ($fields as $field)
            $bind[":$field"] = $info[ $field ];

        //echo $sql;
        return $this->run($sql, $bind);
    }

    public function replace($table, $columns, $search = null, $replace = null, $bind = "", $where = null)
    {
        $sql = "";
        if (is_array($columns)) {
            $replace_query = array();

            foreach ($columns as $column => $replacements) {
                foreach ($replacements as $replace_search => $replace_replacement) {
                    $replace_query[] = $column . ' = REPLACE(' . $this->field_quote($column) . ', ' . $replace_search . ', ' . $replace_replacement . ')';
                }
            }

            $replace_query = implode(', ', $replace_query);
            $where = $search;
        } else {
            if (is_array($search)) {
                $replace_query = array();

                foreach ($search as $replace_search => $replace_replacement) {
                    $replace_query[] = $columns . ' = REPLACE(' . $this->field_quote($columns) . ', ' . $replace_search . ', ' . $replace_replacement . ')';
                }

                $replace_query = implode(', ', $replace_query);
                $where = $replace;
            } else {
                $replace_query = $columns . ' = REPLACE(' . $this->field_quote($columns) . ', ' . $search . ', ' . $replace . ')';
            }
        }

        $sql .= 'UPDATE `' . $table . '` SET ' . $replace_query;
        if (!empty($where))
            $sql .= $this->where_clause($where);
        $sql .= ";";

        //echo $sql;
        return $this->run($sql, $bind);
    }

    public function run($sql, $bind = "")
    {
        $this->sql = trim($sql);
        $this->bind = $this->cleanup($bind);
        $this->error = "";

        try {
            $pdostmt = $this->prepare($this->sql);
            if ($pdostmt->execute($this->bind) !== false) {
                if (preg_match("/^(" . implode("|", array("select", "describe", "pragma")) . ") /i", $this->sql))
                    return $pdostmt->fetchAll(PDO::FETCH_ASSOC);
                elseif (preg_match("/^(" . implode("|", array("delete", "insert", "update")) . ") /i", $this->sql))
                    return $pdostmt->rowCount();
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            $this->debug();

            return false;
        }
    }

    public function select($table, $fields = "*", $where = "", $bind = "", $join = "")
    {
        if ($fields != "*") {
            $fields = $this->fields_format($fields);
        }
        preg_match('/([a-zA-Z0-9_\-\.]*)\s*\(([a-zA-Z0-9_\-]*)\)/i', $table, $match);
        if (count($match)) {
            $table_alias = $match[2];
            $table = '`' . $match[1] . '` ' . 'AS' . ' `' . $table_alias . '` ';
        } else {
            $table_without_alias = $table;
            $table = "`" . $table . "`";
        }
        $sql = "SELECT " . $fields . " FROM " . $table;

        if (!empty($join)) {
            if (isset($table_alias))
                $sql .= $this->create_table_join($table_alias, $join);
            else
                $sql .= $this->create_table_join($table_without_alias, $join);
        }
        if (!empty($where))
            $sql .= $this->where_clause($where);
        $sql .= ";";

        //echo "<br>" . $sql;

        return $this->run($sql, $bind);
    }

    protected function where_clause($where)
    {
        $where_clause = '';

        if (is_array($where)) {
            $where_keys = array_keys($where);
            $where_AND = preg_grep("/^AND\s*#?$/i", $where_keys);
            $where_OR = preg_grep("/^OR\s*#?$/i", $where_keys);

            $single_condition = array_diff_key($where, array_flip(
                explode(' ', 'AND OR GROUP ORDER HAVING LIMIT LIKE MATCH')
            ));

            if ($single_condition != array()) {
                $where_clause = ' WHERE ' . $this->data_implode($single_condition, '');
            }

            if (!empty($where_AND)) {
                $value = array_values($where_AND);
                $where_clause = ' WHERE ' . $this->data_implode($where[ $value[0] ], ' AND');
            }

            if (!empty($where_OR)) {
                $value = array_values($where_OR);
                $where_clause = ' WHERE ' . $this->data_implode($where[ $value[0] ], ' OR');
            }

            if (isset($where['LIKE'])) {
                $LIKE = $where['LIKE'];

                if (is_array($LIKE)) {
                    $is_OR = isset($LIKE['OR']);
                    $clause_wrap = array();

                    if ($is_OR || isset($LIKE['AND'])) {
                        $connector = $is_OR ? 'OR' : 'AND';
                        $LIKE = $is_OR ? $LIKE['OR'] : $LIKE['AND'];
                    } else {
                        $connector = 'AND';
                    }

                    foreach ($LIKE as $column => $keyword) {
                        $keyword = is_array($keyword) ? $keyword : array($keyword);

                        foreach ($keyword as $key) {
                            preg_match('/(%?)([a-zA-Z0-9_\-\.]*)(%?)((\[!\])?)/', $column, $column_match);

                            $clause_wrap[] =
                                $this->field_quote($column_match[2]) .
                                ($column_match[4] != '' ? ' NOT' : '') . ' LIKE ' . $key;
                        }
                    }
                    $where_clause .= ($where_clause != '' ? ' AND ' : ' WHERE ') . '(' . implode($clause_wrap, ' ' . $connector . ' ') . ')';
                }
            }

            if (isset($where['MATCH'])) {
                $MATCH = $where['MATCH'];

                if (is_array($MATCH) && isset($MATCH['columns'], $MATCH['keyword'])) {
                    $where_clause .= ($where_clause != '' ? ' AND ' : ' WHERE ') . ' MATCH (`' . str_replace('.', '`.`', implode($MATCH['columns'], '`, `')) . '`) AGAINST (' . $MATCH['keyword'] . ')';
                }
            }

            if (isset($where['GROUP'])) {
                $where_clause .= ' GROUP BY ' . $this->field_quote($where['GROUP']);
            }

            if (isset($where['ORDER'])) {
                $rsort = '/(^[a-zA-Z0-9_\-\.]*)(\s*(DESC|ASC))?/';
                $ORDER = $where['ORDER'];

                if (is_array($ORDER)) {
                    if (
                        isset($ORDER[1]) &&
                        is_array($ORDER[1])
                    ) {
                        $where_clause .= ' ORDER BY FIELD(' . $this->field_quote($ORDER[0]) . ', ' . $this->array_quote($ORDER[1]) . ')';
                    } else {
                        $stack = array();

                        foreach ($ORDER as $column) {
                            preg_match($rsort, $column, $order_match);

                            array_push($stack, '`' . str_replace('.', '`.`', $order_match[1]) . '`' . (isset($order_match[3]) ? ' ' . $order_match[3] : ''));
                        }

                        $where_clause .= ' ORDER BY ' . implode($stack, ',');
                    }
                } else {
                    preg_match($rsort, $ORDER, $order_match);

                    $where_clause .= ' ORDER BY `' . str_replace('.', '`.`', $order_match[1]) . '`' . (isset($order_match[3]) ? ' ' . $order_match[3] : '');
                }
            }
            if (isset($where['HAVING'])) {
                $where_clause .= ' HAVING ' . $this->data_implode($where['HAVING'], '');
            }
            if (isset($where['LIMIT'])) {
                $LIMIT = $where['LIMIT'];

                if (is_numeric($LIMIT)) {
                    $where_clause .= ' LIMIT ' . $LIMIT;
                }

                if (
                    is_array($LIMIT) &&
                    is_numeric($LIMIT[0]) &&
                    is_numeric($LIMIT[1])
                ) {
                    $where_clause .= ' LIMIT ' . $LIMIT[0] . ',' . $LIMIT[1];
                }
            }
        } else {
            if ($where != null) {
                $where_clause .= ' ' . $where;
            }
        }

        return $where_clause;
    }

    protected function data_implode($data, $conjunctor)
    {
        $wheres = array();
        foreach ($data as $key => $value) {
            $type = gettype($value);
            if (
                preg_match("/^(AND|OR)\s*#?/i", $key, $relation_match) &&
                $type == 'array'
            ) {
                $wheres[] = 0 !== count(array_diff_key($value, array_keys(array_keys($value)))) ?
                    '(' . $this->data_implode($value, ' ' . $relation_match[1]) . ')' : '';
            } else {
                preg_match('/(#?)([\w\.]+)(\[(\>|\>\=|\<|\<\=|\!|\<\>|\>\<)\])?/i', $key, $match);
                $column = $this->field_quote($match[2]);
                if (isset($match[4])) {
                    if ($match[4] == '!') {
                        switch ($type) {
                            case 'NULL':
                                $wheres[] = $column . ' IS NOT NULL';
                                break;

                            case 'array':
                                $wheres[] = $column . ' NOT IN (' . $this->array_quote($value) . ')';
                                break;

                            case 'integer':
                            case 'double':
                                $wheres[] = $column . ' != ' . $value;
                                break;

                            case 'boolean':
                                $wheres[] = $column . ' != ' . ($value ? '1' : '0');
                                break;

                            case 'string':
                                $wheres[] = $column . ' != ' . $this->fn_quote($key, $value);
                                break;
                        }
                    } else {
                        if ($match[4] == '<>' || $match[4] == '><') {
                            if ($type == 'array') {
                                if ($match[4] == '><') {
                                    $column .= ' NOT';
                                }
                                $wheres[] = '(' . $column . ' BETWEEN ' . $value[0] . ' AND ' . $value[1] . ')';
                            }
                        } else {
                            if (is_numeric($value)) {

                                $wheres[] = $column . ' ' . $match[4] . ' ' . $value;
                            } else {
                                $datetime = strtotime($value);
                                if ($datetime) {
                                    $wheres[] = $column . ' ' . $match[4] . ' ' . $this->quote(date('Y-m-d H:i:s', $datetime));
                                } else {
                                    if (strpos($key, '#') === 0) {
                                        $wheres[] = $column . ' ' . $match[4] . ' ' . $this->fn_quote($key, $value);
                                    } else {
                                        $wheres[] = $column . ' ' . $match[4] . ' ' . $value;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if (is_int($key)) {
                        $wheres[] = $this->quote($value);
                    } else {
                        switch ($type) {
                            case 'NULL':
                                $wheres[] = $column . ' IS NULL';
                                break;

                            case 'array':
                                $wheres[] = $column . ' IN (' . $this->array_quote($value) . ')';
                                break;

                            case 'integer':
                            case 'double':
                                $wheres[] = $column . ' = ' . $value;
                                break;

                            case 'boolean':
                                $wheres[] = $column . ' = ' . ($value ? '1' : '0');
                                break;

                            case 'string':
                                $wheres[] = $column . ' = ' . $this->fn_quote($key, $value);
                                break;
                        }
                    }
                }
            }
        }

        return implode($conjunctor . ' ', $wheres);
    }

    protected function create_table_join($table, $join)
    {
        $join_key = is_array($join) ? array_keys($join) : null;
        if (isset($join_key[0]) && strpos($join_key[0], '[') === 0) {
            $table_join = array();
            $join_array = array('>' => 'LEFT', '<' => 'RIGHT', '<>' => 'FULL', '><' => 'INNER');
            foreach ($join as $sub_table => $relation) {
                preg_match('/([a-zA-Z0-9_\-\.]*)\s*\(([a-zA-Z0-9_\-]*)\)/i', $sub_table, $match);

                if (count($match)) {
                    $table_for_alias = $match[1];
                    $alias = $match[2];
                }
                preg_match('/(\[(\<|\>|\>\<|\<\>)\])?([a-zA-Z0-9_\-]*)/', $sub_table, $match);

                if ($match[2] != '' && $match[3] != '') {
                    if (is_string($relation)) {
                        $relation = 'USING (`' . $relation . '`)';
                    }

                    if (is_array($relation)) {
                        // For ['column1', 'column2']
                        if (isset($relation[0])) {
                            $relation = 'USING (`' . implode($relation, '`, `') . '`)';
                        } // For ['column1' => 'column2']
                        else {
                            if ($alias == '')
                                $relation = 'ON `' . $table . '`.`' . key($relation) . '` = `' . $match[3] . '`.`' . current($relation) . '`';
                            else
                                $relation = 'ON `' . $table . '`.`' . key($relation) . '` = `' . $alias . '`.`' . current($relation) . '`';
                        }
                    }
                    if ($alias == '') {
                        $table_join[] = $join_array[ $match[2] ] . ' JOIN `' . $match[3] . '` ' . $relation;

                    } else {
                        $table_join[] = $join_array[ $match[2] ] . ' JOIN `' . $table_for_alias . '` ' . 'AS' . ' `' . $alias . '` ' . $relation;
                    }
                }
                $table_for_alias = "";
                $alias = "";
            }

            return ' ' . implode($table_join, ' ');
        }

        return '';
    }

    protected function fields_format($fields)
    {
        if (is_string($fields)) {
            $fields = array($fields);
        }
        $stack = array();
        foreach ($fields as $key => $value) {
            preg_match('/([a-zA-Z0-9_\-\.]*)\s*\(([a-zA-Z0-9_\-]*)\)/i', $value, $match);
            if (isset($match[1], $match[2])) {
                array_push($stack, $this->field_quote($match[1]) . ' AS ' . $this->field_quote($match[2]));
            } else {
                array_push($stack, $this->field_quote($value));
            }
        }

        return implode($stack, ',');
    }

    protected function array_quote($array)
    {
        return implode($array, ',');
    }

    protected function fn_quote($column, $string)
    {
        return (strpos($column, '#') === 0 && preg_match('/^[A-Z0-9\_]*\([^)]*\)$/', $string)) ?

            $string :

            $string;
    }

    protected function field_quote($string)
    {
        return '`' . str_replace('.', '`.`', preg_replace('/(^#|\(JSON\))/', '', $string)) . '`';
    }

    public function setErrorCallbackFunction($errorCallbackFunction, $errorMsgFormat = "html")
    {
        //Variable functions for won't work with language constructs such as echo and print, so these are replaced with print_r.
        if (in_array(strtolower($errorCallbackFunction), array("echo", "print")))
            $errorCallbackFunction = "print_r";

        if (function_exists($errorCallbackFunction)) {
            $this->errorCallbackFunction = $errorCallbackFunction;
            if (!in_array(strtolower($errorMsgFormat), array("html", "text")))
                $errorMsgFormat = "html";
            $this->errorMsgFormat = $errorMsgFormat;
        }
    }

    public function update($table, $info, $where, $bind = "")
    {
        foreach ($info as $key => $value) {
            preg_match('/([\w]+)(\[(\+|\-|\*|\/)\])?/i', $key, $match);
            if (isset($match[1])) {
                $cleanInfo[ $match[1] ] = $value;
            }
            if (isset($match[3])) {
                $operators[] = $match[3];
            }
        }

        $fields = $this->filter($table, $cleanInfo);
        $fieldSize = sizeof($fields);

        $sql = "UPDATE " . $table . " SET ";
        for ($f = 0; $f < $fieldSize; ++$f) {
            if ($f > 0) {
                $sql .= ", ";
            }
            if (!isset($operators)) {
                $sql .= $fields[ $f ] . " = :update_" . $fields[ $f ];
            } else {
                $sql .= $fields[ $f ] . " = " . $fields[ $f ] . ' ' . $operators[ $f ] . ' ' . " :update_" . $fields[ $f ];
            }
        }
        if (!empty($where))
            $sql .= $this->where_clause($where);

        $bind = $this->cleanup($bind);
        foreach ($fields as $field)
            $bind[":update_$field"] = $cleanInfo[ $field ];

        //echo $sql;
        return $this->run($sql, $bind);
    }
} 
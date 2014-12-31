<?php

function _get($key = null)
{
    if ($key === null) {
        return $_GET;
    }
    return isset($_GET[$key]) ? trim($_GET[$key]) : null;
}
function _post($key = null)
{
    if ($key === null) {
        return $_POST;
    }
    return isset($_POST[$key]) ? trim($_POST[$key]) : null;
}

function echo_json($code, $data = null)
{
    $map = [
        0 => 'ok',
    ];
    header('ContentT');
    echo json_encode(['code' => $code, 'message' => $map[$code], 'data' => $data]);
    die();
}

function Service($name = null, $value = null)
{
    static $container;
    if ($name === null) {
        return $container;
    }
    $container[$name] = $value;
}

class DB
{
    public $lastSql = '';

    public function __construct($host, $port, $dbname, $username, $password)
    {
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
        $pdo = new Pdo($dsn, $username, $password, array(Pdo::MYSQL_ATTR_INIT_COMMAND => 'set names utf8'));
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo = $pdo;
    }

    function execute($sql, $values = array())
    {
        $param_arr = array_map(function($e){return "'$e'";}, $values);
        array_unshift($param_arr, str_replace('?', '%s', $sql));
        $this->lastSql = call_user_func_array('sprintf', $param_arr);

        $stmt = $this->pdo->prepare($sql);
        if (!$stmt->execute($values)) {
            foreach ($stmt->errorInfo() as $key => $value) {
                echo "$key: $value\n";
            }
            throw new Exception("bad sql", 1);
        }
        return $stmt;
    }
    public function update($table, $set, $where)
    {
        $func = function () {
            return "$field=?";
        };
        $set_str = array_map($func, $set);
        $where_str = array_map($func, $set);
        $sql = "update $table set $set_str where $where_str";
        return $this->execute($sql, array_merge(array_values($set), array_values($where)));
    }
    public function insert($table, $values)
    {
        $keys = array_keys($values);
        $columns = implode(',', $keys);
        $value_str = implode(',', array_map(function($field){
            return ":$field";
        }, $keys));
        $sql = "insert into $table ($columns)values($value_str)";
        return $this->execute($sql, $values);
    }
    public function __call($name, $args)
    {
        return call_user_func_array([$this->pdo, $name], $args);
    }
}

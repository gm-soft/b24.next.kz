<?php

/**
 * Created by PhpStorm.
 * User: Next
 * Date: 04.12.2016
 * Time: 17:05
 */
class MysqlHelper
{
    private $db_name = "nextevents";
    private $table_users = "users";

    private $context = null;


    function __construct($username, $password, $db_name) {
        $db_host = "localhost";

        $this->context = mysqli_connect($db_host, $username, $password, $db_name);

        if (!$this->context){
            $this->context = null;
            return mysqli_connect_error();

        } else {
            $this->context->set_charset("utf8");
            //mysqli_set_charset($this->context, "utf8");
            return true;
        }
    }

    /**
     * Функция возврата пользователя из базы данных по искомому значению.
     * Возвращает массив с полями result и data. Если запрос был успешен, то
     * result равен true. Если пользователь был найден, то data будет содержать этот объект,
     * иначе null
     *
     * @param $searchable - Искомое значение
     * @param string $field - названеи поля, по которому осуществлять поиск
     * @return array|null
     */
    public function getUser($searchable, $field = "username") {

        if ($field == "id") $query = "select * from users where ".$field."=".$searchable;
        else $query = "select * from users where ".$field."='".$searchable."'";
        $data = $this->executeQuery($query);
        if ($data["result"] != true || is_null($data["data"])) return $data;

        $data["data"] = User::fromDatabase($data["data"]);

        return $data;
    }

    public function addUser($user){

        $query = "insert into `".$this->table_users."` (".
            "`username`, ".
            "`password`, ".
            "`hash`".
            ") values (".
            "'".$user->getUsername()."',".
            "'".$user->getPassword()."',".
            "'".$user->getHash()."'".
            ")";
        $query_result = $this->executeQuery($query);
        if ($query_result["result"] != true) return $query_result;
        $query_result["data"] = mysqli_insert_id($this->context);;
        //$id = mysqli_insert_id($this->context);
        return $query_result;
    }

    public function updateUser($user){
        $query = "update `".$this->table_users."` set ".
            "`username`='".$user->getUsername()."', ".
            "`password`='".$user->getPassword()."', ".
            "`usergroup`='".$user->getUsergroup()."', ".
            "`hash`='".$user->getHash()."'".
            " where id=".$user->getId();
        $query_result = $this->executeQuery($query);
        if ($query_result["result"] != true) return $query_result;
        //$query_result["data"] = mysqli_insert_id($this->context);;
        //$id = mysqli_insert_id($this->context);
        return $query_result;
    }
}
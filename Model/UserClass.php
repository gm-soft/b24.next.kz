<?php

require_once ("../include/helper.php");

class User
{
	private $id;
    private $username;
    private $password;
    private $hash;
    private $usergroup;
    private $createdAt;

    function __construct()
    {
        $this->id = -1;
        $this->username = null;
        $this->password = null;
        $this->usergroup = null;
        $this->createdAt = time();
        $this->hash = md5(User::generateCode());
    }

    protected function fill( array $row ) {
        $this->id = $row["id"];
        $this->username = $row["username"];
        $this->password = $row["password"];
        $this->usergroup = $row["usergroup"];
        $this->createdAt = $row["created_at"];
        $this->hash = $row["hash"];
    }

    public static function fromDatabase(array $databaseRow)
    {
        $instance = new self();
        $instance->fill( $databaseRow );
        return $instance;
    }

    public static function withUserdata($username, $password, $hash = null)
    {
        $instance = new self();
        $instance->username = $username;
        $instance->password = $password;
        $instance->hash = $hash;
        return $instance;
    }

    public static function generateCode($length=6) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
        $code = "";
        $clen = strlen($chars) - 1;
        while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0,$clen)];
        }

        return $code;

    }


    /**
     * @return string
     */
    public function getUsername() { return $this->username; }

    /**
     * @return string
     */
    public function getPassword() { return $this->password; }

    /**
     * @return int
     */
    public function getUsergroup() { return $this->usergroup; }

    /**
     * @return int
     */
    public function getId() { return $this->id; }

    /**
     * @return int
     */
    public function getCreatedAt() {return $this->created_at;}

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return null
     */
    public function getHash() { return $this->hash; }

    /**
     * @param null $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @param null $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @param null $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }
}
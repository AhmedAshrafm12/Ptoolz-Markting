<?php

namespace modules;



use PDO;

use PDOException;



class database

{



    private $conn, $db_name='factor' , $HostName , $user_name = 'root', $password='1234';

                                



    public function connect()

    {

        $this->HostName = 'localhost';

        $dsn="mysql:host=".$this->HostName.";dbname=".$this->db_name;


        

        try {

            $this->conn = new PDO($dsn, $this->user_name, $this->password);

            // $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $this->conn;

        } catch (PDOException $e) {

            echo "Connection failed: " . $e->getMessage();

        }



    }





}
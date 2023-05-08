<?php

class Person
{
    private $id;
    private string $first_name;
    private string $last_name;
    private string $birth_date;
    private int $gender;
    private string $birth_city;
    private PDO $pdo;

    public function __construct(string $first_name, string $last_name, string $birth_date, int $gender, string $birth_city, int $id = null)
    {
        $this->id = $id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->birth_date = $birth_date;
        $this->gender = $gender;
        $this->birth_city = $birth_city;
        $this->pdo = require 'db_connection.php';
    }
}

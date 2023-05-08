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

        if ($this->id !== null) {
            $stmt = $this->pdo->prepare('SELECT * FROM people WHERE id = :id');
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();

            if ($row !== false) {
                $this->first_name = $row['first_name'];
                $this->last_name = $row['last_name'];
                $this->birth_date = $row['birth_date'];
                $this->gender = $row['gender'];
                $this->birth_city = $row['birth_city'];
            } else {
                throw new InvalidArgumentException('Person not found.');
            }
        }
    }

    private function bindParams($stmt)
    {
        $stmt->bindParam(':first_name', $this->first_name, PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $this->last_name, PDO::PARAM_STR);
        $stmt->bindParam(':birth_date', $this->birth_date, PDO::PARAM_STR);
        $stmt->bindParam(':gender', $this->gender, PDO::PARAM_INT);
        $stmt->bindParam(':birth_city', $this->birth_city, PDO::PARAM_STR);
    }

    public function save()
    {
        $this->pdo->beginTransaction();

        try {
            if ($this->id === null) {
                $stmt = $this->pdo->prepare('INSERT INTO people (first_name, last_name, birth_date, gender, birth_city) VALUES (:first_name, :last_name, :birth_date, :gender, :birth_city)');
            } else {
                $stmt = $this->pdo->prepare('UPDATE people SET first_name = :first_name, last_name = :last_name, birth_date = :birth_date, gender = :gender, birth_city = :birth_city WHERE id = :id');
                $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            }

            $this->bindParams($stmt);
            $stmt->execute();

            if ($this->id === null) {
                $this->id = $this->pdo->lastInsertId();
            }

            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollback();
            throw $e;
        }
    }

    public function delete()
    {
        if ($this->id !== null) {
            $stmt = $this->pdo->prepare('DELETE FROM people WHERE id = :id');
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
            $this->id = null;
        }
    }

    public static function ageFromDate(string $birth_date): int
    {
        $now = new DateTime();
        $diff = $now->diff(new DateTime($birth_date));
        return $diff->y;
    }

    public static function genderToString(int $gender): string
    {
        if ($gender === 1) {
            return 'муж';
        }
        if ($gender === 0) {
            return 'жен';
        }
        return 'Неизвестно';
    }

    public function format(bool $age = true, bool $gender = true): stdClass
    {
        $result = new stdClass();
        $result->id = $this->id;
        $result->first_name = $this->first_name;
        $result->last_name = $this->last_name;
        $result->birth_date = $this->birth_date;
        $result->gender = $gender ? self::genderToString($this->gender) : $this->gender;
        $result->birth_city = $this->birth_city;
        $result->age = $age ? self::ageFromDate($this->birth_date) : null;
        $result->gender_string = $gender ? self::genderToString($this->gender) : null;
        return $result;
    }
}

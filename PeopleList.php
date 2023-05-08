<?php

require_once 'Person.php';

if (!class_exists('Person')) {
    echo 'Error: The Person class is not defined';
    exit;
}

class PeopleList {
    private $pdo;
    private $peopleIds;

    public function __construct($conditions) {
        $this->pdo = require 'db_connection.php';

        $query = 'SELECT id FROM people WHERE 1=1';
        $params = array();

        foreach ($conditions as $field => $value) {
            if ($value === null) {
                $query .= " AND $field IS NULL";
            } else if (is_array($value)) {
                if (count($value) === 1) {
                    $query .= " AND $field = ?";
                    $params[] = reset($value);
                } else {
                    $placeholders = implode(',', array_fill(0, count($value), '?'));
                    $query .= " AND $field IN ($placeholders)";
                    $params = array_merge($params, array_values($value));
                }
            } else {
                $query .= " AND $field = ?";
                $params[] = $value;
            }
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $this->peopleIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getPeople() {
        $people = array();

        foreach ($this->peopleIds as $id) {
            $person = new Person('', '', '', 0, '', $id);
            $people[] = $person;
        }

        return $people;
    }

    public function deletePeople() {
        foreach ($this->peopleIds as $id) {
            $person = new Person('', '', '', 0, '', $id);
            $person->delete();
        }
    }
}

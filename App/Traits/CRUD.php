<?php

namespace APP\Traits;

trait CRUD
{

    public function add($data = [])
    {
        if (count($data)) {
            $keys = array_keys($data);
            $values = '';
            $binder = 1;
            foreach ($data as $field) {
                $values .= '?';
                if ($binder < count($data)) {
                    $values .= ', ';
                }
                $binder++;
            }
            $sqlQuery = "insert into `{$this->tbl}` (`" . implode('`, `', $keys) . "`) VALUES ({$values})";
            $stmt = $this->db->connection()->prepare($sqlQuery);
            $binder = 1;
            foreach ($data as $para) {
                $stmt->bindValue($binder, $para);
                $binder++;
            }
            if ($stmt->executeQuery()) {
                return true;
            }
        }
        return false;
    }

    public function update($id, $fields)
    {
        $set = '';
        $binder = 1;
        foreach ($fields as $name => $value) {
            $set .= "{$name} = ?";
            if ($binder < count($fields)) {
                $set .= ', ';
            }
            $binder++;
        }
        $sqlQuery = "UPDATE {$this->tbl} SET {$set} WHERE id = {$id}";
        $stmt = $this->db->connection()->prepare($sqlQuery);
        $binder = 1;
        foreach ($fields as $para) {
            $stmt->bindValue($binder, $para);
            $binder++;
        }
        if ($stmt->executeQuery()) {
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        try {
            $sqlQuery = "delete from {$this->tbl} where id = {$id}";
            $stmt = $this->db->connection()->prepare($sqlQuery);
            $result = $stmt->executeQuery();
            return true;
        } catch (\Exception $ex) {
            die($ex->getMessage());
        }
        return false;
    }

    public function getTableName()
    {
        return $this->tbl;
    }

    public function lastinsertid()
    {
        return $this->db->lastInsertId();
    }
}

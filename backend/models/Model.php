<?php

abstract class Model
{
    protected PDO    $db;
    protected string $table;
    protected int    $id;
    protected string $createdAt;

    public function __construct()
    {
        $this->db = Database::getInstance();
        
        // Force PDO à utiliser de vraies requêtes préparées et non une émulation
        $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        // Force PDO à lever des exceptions en cas d'erreur SQL
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function findAll(): array
    {
        $secureTable = preg_replace('/[^a-zA-Z0-9_]/', '', $this->table);

        $stmt = $this->db->query("SELECT * FROM `{$secureTable}`");
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $secureTable = preg_replace('/[^a-zA-Z0-9_]/', '', $this->table);
        
        $stmt = $this->db->prepare("SELECT * FROM `{$secureTable}` WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function delete(int $id): void
    {
        $secureTable = preg_replace('/[^a-zA-Z0-9_]/', '', $this->table);
        
        $stmt = $this->db->prepare("DELETE FROM `{$secureTable}` WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}
<?php

abstract class AbstractManager {
    protected PDO $db;
    protected string $table;
    protected string $entityClass;

    public function __construct() {
        $this->db = Database::getInstance();
        
        $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    protected function getSecureTable(): string {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $this->table);
    }

    public function findAll(): array {
        $table = $this->getSecureTable();
        $stmt = $this->db->query("SELECT * FROM `{$table}`");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $entities = [];
        foreach ($results as $data) {
            $entities[] = $this->hydrate($data);
        }

        return $entities;
    }

    public function findById(int $id): object|false {
        $table = $this->getSecureTable();
        $stmt = $this->db->prepare("SELECT * FROM `{$table}` WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return false;
        }

        return $this->hydrate($data);
    }

    public function delete(int $id): void {
        $table = $this->getSecureTable();
        $stmt = $this->db->prepare("DELETE FROM `{$table}` WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    protected function hydrate(array $data): object {
        $className = $this->entityClass;
        
        // Instanciation sécurisée sans déclencher le constructeur de l'entité
        $reflection = new ReflectionClass($className);
        $entity = $reflection->newInstanceWithoutConstructor();

        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));

            if (method_exists($entity, $method)) {
                if (($key === 'created_at' || str_contains($key, '_at')) && !empty($value)) {
                    $value = new DateTime($value);
                }
                
                $entity->$method($value);
            }
        }

        return $entity;
    }
}
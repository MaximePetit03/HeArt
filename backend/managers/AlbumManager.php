<?php
class AlbumManager extends AbstractManager {
    protected string $table = 'album';
    protected string $entityClass = 'Album';
    
    public function findAll(): array {
        $query = $this->db->prepare("SELECT * FROM album");
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        
        $albums = [];

        foreach($result as $item) {
            $album = new Album(
                $item["title"],
                $item["description"] ?? '',
                $item["visibility"],
                (int)$item["user_id"]
            );
            
            $album->setId((int)$item["id"]);
            $album->setCreatedAt($item["created_at"]);
            $albums[] = $album;
        }
        return $albums;
    }

    public function findById(int $id): object|false {
        $sql = "SELECT album.*, user.username
                FROM album
                LEFT JOIN user ON album.user_id = user.id
                WHERE album.id = :id";
                
        $query = $this->db->prepare($sql);
        $query->execute(['id' => $id]);
        $item = $query->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            return false;
        }

        $album = new Album(
            $item["title"],
            $item["description"] ?? '',
            $item["visibility"],
            (int)$item["user_id"]
        );

        $album->setId((int)$item["id"]);
        $album->setCreatedAt($item["created_at"]);
        $album->setUsername($item["username"] ?? 'Utilisateur inconnu');

        return $album;
    }
    
    public function create(string $title, ?string $description, string $visibility, int $userId): int {
        $sql = "INSERT INTO album (title, description, visibility, user_id, created_at)
                VALUES (:title, :description, :visibility, :user_id, NOW())";
                
        $query = $this->db->prepare($sql);
        
        $query->execute([
            'title'       => $title,
            'description' => $description,
            'visibility'  => $visibility,
            'user_id'     => $userId
        ]);

        return (int)$this->db->lastInsertId();
    }
}
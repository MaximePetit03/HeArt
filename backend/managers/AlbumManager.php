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

    public function findByUserId(int $userId): array {
        $query = $this->db->prepare(
            "SELECT * FROM album WHERE user_id = :user_id ORDER BY created_at DESC"
        );
        $query->execute(['user_id' => $userId]);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        $albums = [];
        foreach ($result as $item) {
            $album = new Album(
                $item['title'],
                $item['description'] ?? '',
                $item['visibility'],
                (int)$item['user_id']
            );
            $album->setId((int)$item['id']);
            $album->setCreatedAt($item['created_at']);
            $albums[] = $album;
        }

        return $albums;
    }

    public function findAllPublic(): array {
        // On fait une jointure pour récupérer le nom de l'auteur
        $sql = "SELECT album.*, user.username
                FROM album
                JOIN user ON album.user_id = user.id
                WHERE album.visibility = 'public'
                ORDER BY RAND()";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_CLASS, 'Album');
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

    public function updateVisibility(int $id, string $visibility): bool {
        // 1. Log pour vérifier ce qui arrive réellement
        error_log("Tentative de maj Album ID $id avec visibilité '$visibility'");

        $query = $this->db->prepare("UPDATE album SET visibility = :visibility WHERE id = :id");
        $result = $query->execute([
            ':visibility' => $visibility,
            ':id' => $id
        ]);

        // 2. Vérifier si une ligne a bien été modifiée
        $count = $query->rowCount();
        error_log("Lignes modifiées : " . $count);

        return $result && $count > 0;
    }

    public function addTagToAlbum(int $albumId, int $tagId): void {
        $sql = "INSERT INTO album_tag (album_id, tag_id) VALUES (:album_id, :tag_id)";
        $query = $this->db->prepare($sql);
        $query->execute([':album_id' => $albumId, ':tag_id' => $tagId]);
    }

    public function findAllTags(): array {
        return $this->db->query("SELECT * FROM tag ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTagsByAlbumId(int $albumId): array {
        $sql = "SELECT t.* FROM tag t
                JOIN album_tag at ON t.id = at.tag_id
                WHERE at.album_id = :album_id";
                
        $query = $this->db->prepare($sql);
        $query->execute([':album_id' => $albumId]);
        
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $tags = [];
        foreach ($results as $data) {
            $tag = new Tag();
            $tag->setId($data['id']);
            $tag->setName($data['name']);
            $tags[] = $tag;
        }
        return $tags;
    }

    public function removeTagsFromAlbum(int $albumId): void {
        $sql = "DELETE FROM album_tag WHERE album_id = :album_id";
        $query = $this->db->prepare($sql);
        $query->execute([':album_id' => $albumId]);
    }

    public function deleteAlbum(int $albumId): void {
        $query = $this->db->prepare("SELECT filename FROM photo WHERE album_id = :album_id");
        $query->execute([':album_id' => $albumId]);
        $filenames = $query->fetchAll(PDO::FETCH_COLUMN);

        $uploadDir = realpath(__DIR__ . '/public/uploads/albums/');

        foreach ($filenames as $filename) {
            $safeFilename = basename($filename);
            $filePath = $uploadDir . DIRECTORY_SEPARATOR . $safeFilename;

            if (file_exists($filePath) && strpos(realpath($filePath), $uploadDir) === 0) {
                unlink($filePath);
            }
        }

        $this->db->prepare("DELETE FROM album_tag WHERE album_id = :album_id")->execute([':album_id' => $albumId]);
        $this->db->prepare("DELETE FROM photo WHERE album_id = :album_id")->execute([':album_id' => $albumId]);
        $this->db->prepare("DELETE FROM album WHERE id = :album_id")->execute([':album_id' => $albumId]);
    }

    public function isOwner(int $albumId, int $userId): bool {
        $sql = "SELECT user_id FROM album WHERE id = :album_id";
        $query = $this->db->prepare($sql);
        $query->execute([':album_id' => $albumId]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        return $result && (int)$result['user_id'] === $userId;
    }
}
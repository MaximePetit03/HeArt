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
        $query = $this->db->prepare("SELECT album.*, user.username FROM album
                LEFT JOIN user ON album.user_id = user.id
                WHERE album.id = :id");
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
        $album->setSharingToken($item["sharing_token"] ?? null);

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
        $sql = "SELECT album.*, user.username
                FROM album
                JOIN user ON album.user_id = user.id
                WHERE album.visibility = 'public'
                ORDER BY RAND()";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_CLASS, 'Album');
    }
    
    public function create(string $title, ?string $description, string $visibility, int $userId): int {
        $sharingToken = bin2hex(random_bytes(16));

        $query = $this->db->prepare("INSERT INTO album (title, description, visibility, user_id, sharing_token, created_at)
                VALUES (:title, :description, :visibility, :user_id, :sharing_token, NOW())");
        
        $query->execute([
            'title'         => $title,
            'description'   => $description,
            'visibility'    => $visibility,
            'user_id'       => $userId,
            'sharing_token' => $sharingToken
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateVisibility(int $id, string $visibility): bool {
        // Log pour vérifier ce qui arrive réellement
        error_log("Tentative de maj Album ID $id avec visibilité '$visibility'");

        $query = $this->db->prepare("UPDATE album SET visibility = :visibility WHERE id = :id");
        $result = $query->execute([
            ':visibility' => $visibility,
            ':id' => $id
        ]);

        // Vérifier si une ligne a bien été modifiée
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

    public function isGuest(int $albumId, int $userId): bool {
        $query = $this->db->prepare("SELECT COUNT(*) FROM album_invitation WHERE album_id = ? AND user_id = ?");
        $query->execute([$albumId, $userId]);
        return (int)$query->fetchColumn() > 0;
    }

    public function isInvited(int $albumId, int $userId): bool {
        $query = $this->db->prepare("SELECT COUNT(*) FROM album_invitation
                WHERE album_id = :album_id AND user_id = :user_id");
        $query->execute([
            'album_id' => $albumId,
            'user_id'  => $userId
        ]);
        
        return (int)$query->fetchColumn() > 0;
    }

    public function getInvitations(int $albumId): array {
        $query = $this->db->prepare("SELECT u.id, u.username
                                    FROM user u
                                    JOIN album_invitation ai ON u.id = ai.user_id
                                    WHERE ai.album_id = :album_id");
        $query->execute(['album_id' => $albumId]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addInvitation(int $albumId, int $userId): void {
        error_log("DEBUG: Tentative d'insertion invitation - Album: $albumId, User: $userId");

        $query = $this->db->prepare("INSERT INTO album_invitation (album_id, user_id, permission)
                                    VALUES (:album_id, :user_id, 'view')");
        
        try {
            $query->execute(['album_id' => $albumId, 'user_id' => $userId]);
        } catch (PDOException $e) {
            error_log("DEBUG: ERREUR SQL - " . $e->getMessage());
        }
    }

    public function removeInvitation(int $albumId, int $userId): void {
        $query = $this->db->prepare("DELETE FROM album_invitation 
                                    WHERE album_id = :album_id AND user_id = :user_id");
        $query->execute(['album_id' => $albumId, 'user_id' => $userId]);
    }

    public function findAllForUser(int $userId): array {
        $sql = "
            SELECT album.*, 'owner' AS access_type
            FROM album
            WHERE album.user_id = :uid1

            UNION

            SELECT album.*, 'guest' AS access_type
            FROM album
            JOIN album_invitation ON album.id = album_invitation.album_id
            WHERE album_invitation.user_id = :uid2

            ORDER BY created_at DESC
        ";

        $query = $this->db->prepare($sql);
        $query->execute([':uid1' => $userId, ':uid2' => $userId]);
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

    public function generateSharingToken(int $albumId): string {
        $token = bin2hex(random_bytes(16));// Génère une chaîne unique
        $query = $this->db->prepare("UPDATE album SET sharing_token = ? WHERE id = ?");
        $query->execute([$token, $albumId]);
        return $token;
    }

    public function findByToken(string $token): ?Album {
        $query = $this->db->prepare("SELECT * FROM album WHERE sharing_token = ?");
        $query->execute([$token]);
        $item = $query->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            return null;
        }

        $album = new Album(
            $item["title"],
            $item["description"] ?? '',
            $item["visibility"],
            (int)$item["user_id"]
        );

        $album->setId((int)$item["id"]);
        $album->setSharingToken($item["sharing_token"]);

        return $album;
    }
}
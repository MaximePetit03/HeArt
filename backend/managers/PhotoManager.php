<?php

class PhotoManager extends AbstractManager {
    protected string $table = 'photo';
    protected string $entityClass = 'Photo';

    public function create(string $filename, int $albumId, int $userId): int {
        $sql = "INSERT INTO photo (
                    title,
                    filename,
                    taken_at,
                    album_id,
                    user_id,
                    created_at
                ) VALUES (
                    :title,
                    :filename,
                    NULL,
                    :album_id,
                    :user_id,
                    NOW()
                )";
                
        $query = $this->db->prepare($sql);
        $query->execute([
            'title'       => 'Sans titre',
            'filename'    => $filename,
            'album_id'    => $albumId,
            'user_id'     => $userId
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function findByAlbumId(int $albumId): array {
        $query = $this->db->prepare("SELECT * FROM photo WHERE album_id = :id");
        $query->execute([':id' => $albumId]);
        
        return $query->fetchAll(PDO::FETCH_CLASS, 'Photo');
    }

    public function toggleVisibility(int $id): bool {
        $query = $this->db->prepare("UPDATE photo SET is_visible = NOT is_visible WHERE id = :id");
        return $query->execute([':id' => $id]);
    }
    
    public function isOwnerOfPhoto(int $photoId, int $userId): bool {
        $query = $this->db->prepare("
            SELECT p.id
            FROM photo p
            JOIN album a ON p.album_id = a.id
            WHERE p.id = :photo_id AND a.user_id = :user_id
        ");
        $query->execute([':photo_id' => $photoId, ':user_id' => $userId]);
        return (bool)$query->fetch();
    }

    public function deletePhoto(int $photoId): void {
        $query = $this->db->prepare("SELECT filename FROM photo WHERE id = :id");
        $query->execute([':id' => $photoId]);
        $filename = $query->fetchColumn();

        if ($filename) {
            $uploadDir = realpath(__DIR__ . '/public/uploads/albums/');
            $filePath = $uploadDir . DIRECTORY_SEPARATOR . basename($filename);
            
            if (!file_exists($filePath)) {
                error_log("DEBUG: Fichier introuvable à l'adresse : " . $filePath);
                error_log("DEBUG: Le dossier existe ? " . (file_exists($uploadDir) ? 'OUI' : 'NON'));
            } else {
                unlink($filePath);
            }
        }

        $query = $this->db->prepare("DELETE FROM photo WHERE id = :id");
        $query->execute([':id' => $photoId]);
    }

    public function findById(int $id): object|false {
        $query = $this->db->prepare("SELECT * FROM photo WHERE id = :id");
        $query->execute([':id' => $id]);
        
        $query->setFetchMode(PDO::FETCH_CLASS, 'Photo');
        
        return $query->fetch();
    }

    public function toggleTag(int $photoId, int $tagId): bool {
        $query = $this->db->prepare("SELECT COUNT(*) FROM photo_tag WHERE photo_id = ? AND tag_id = ?");
        $query->execute([$photoId, $tagId]);

        if ($query->fetchColumn() > 0) {
            $query = $this->db->prepare("DELETE FROM photo_tag WHERE photo_id = ? AND tag_id = ?");
        } else {
            $query = $this->db->prepare("INSERT INTO photo_tag (photo_id, tag_id) VALUES (?, ?)");
        }

        return $query->execute([$photoId, $tagId]);
    }

    public function getTagsByPhotoId(int $photoId): array {
        $query = $this->db->prepare("SELECT t.id, t.name FROM tag t JOIN photo_tag pt
                ON t.id = pt.tag_id
                WHERE pt.photo_id = :photo_id");
        $query->execute([':photo_id' => $photoId]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
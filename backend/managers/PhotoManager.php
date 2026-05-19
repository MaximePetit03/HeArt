<?php

class PhotoManager extends AbstractManager {
    protected string $table = 'photo';
    protected string $entityClass = 'Photo';

    public function create(string $filename, int $albumId, int $userId): int {
        $sql = "INSERT INTO photo (
                    title,
                    description,
                    filename,
                    taken_at,
                    album_id,
                    user_id,
                    created_at
                ) VALUES (
                    :title,
                    :description,
                    :filename,
                    NULL,
                    :album_id,
                    :user_id,
                    NOW()
                )";
                
        $query = $this->db->prepare($sql);
        $query->execute([
            'title'       => 'Sans titre',
            'description' => '',
            'filename'    => $filename,
            'album_id'    => $albumId,
            'user_id'     => $userId
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function findByAlbumId(int $albumId): array {
        $sql = "SELECT * FROM photo WHERE album_id = :album_id ORDER BY created_at DESC";
        
        $query = $this->db->prepare($sql);
        $query->execute(['album_id' => $albumId]);
        
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
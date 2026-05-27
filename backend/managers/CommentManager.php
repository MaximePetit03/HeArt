<?php

class CommentManager extends AbstractManager {
    
    public function findByPhotoId(int $photoId): array {
        $query = $this->db->prepare("SELECT c.*, u.username
                FROM comment c
                JOIN user u ON c.user_id = u.id
                WHERE c.photo_id = :photo_id
                ORDER BY c.created_at ASC");
        $query->execute(['photo_id' => $photoId]);
        
        return $query->fetchAll(PDO::FETCH_CLASS, 'Comment');
    }

    public function add(int $photoId, int $userId, string $content): bool {
        $query = $this->db->prepare("INSERT INTO comment (photo_id, user_id, content, created_at)
                VALUES (:photo_id, :user_id, :content, NOW())");
        return $query->execute([
            'photo_id' => $photoId,
            'user_id'  => $userId,
            'content'  => $content
        ]);
    }

    public function findById(int $id): object|false {
        $query = $this->db->prepare("SELECT * FROM comment WHERE id = :id");
        $query->execute(['id' => $id]);
        
        $query->setFetchMode(PDO::FETCH_CLASS, 'Comment');
        
        return $query->fetch();
    }

    public function delete(int $id): void {
        $query = $this->db->prepare("DELETE FROM comment WHERE id = :id");
        $query->execute(['id' => $id]);
    }

    public function update(int $id, string $content): void {
        $query = $this->db->prepare("UPDATE comment SET content = :content WHERE id = :id");

        $query->execute([
            'content' => $content,
            'id'      => $id
        ]);
    }
}
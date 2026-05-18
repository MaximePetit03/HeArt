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
                $item["description"],
                (int)$item["visibility"],
                (int)$item["user_id"]
            );
            
            $album->setId((int)$item["id"]);
            $album->setCreatedAt($item["created_at"]);
            $albums[] = $album;
        }
        return $albums;
    }
}
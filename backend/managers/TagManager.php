<?php

class TagManager extends AbstractManager {
    protected string $table = 'tag';
    protected string $entityClass = 'Tag';

    public function findAll(): array {
        $query = $this->db->query("SELECT * FROM `{$this->table}` ORDER BY name ASC");
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        
        $tags = [];
        foreach ($results as $data) {
            $tags[] = $this->hydrate($data);
        }
        return $tags;
    }

    public function findByAlbumId(int $albumId): array {
        $sql = "SELECT t.* FROM tag t
                JOIN album_tag at ON t.id = at.tag_id
                WHERE at.album_id = :album_id";
        
        $query = $this->db->prepare($sql);
        $query->execute([':album_id' => $albumId]);
        
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        
        $tags = [];
        foreach ($results as $item) {
            $tag = new Tag();
            $tag->setId((int)$item['id']);
            $tag->setName($item['name']);
            $tags[] = $tag;
        }
        return $tags;
    }
}
<?php

declare(strict_types=1);

namespace ZauberCMS\Media;

use PDO;

final class MediaRepository
{
    public function __construct(private readonly PDO $database)
    {
    }

    public function create(array $media): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO media (original_name, stored_name, path, mime_type, extension, size_bytes, width, height, title, alt_text)
             VALUES (:original_name, :stored_name, :path, :mime_type, :extension, :size_bytes, :width, :height, :title, :alt_text)'
        );
        $statement->execute([
            'original_name' => $media['original_name'],
            'stored_name' => $media['stored_name'],
            'path' => $media['path'],
            'mime_type' => $media['mime_type'],
            'extension' => $media['extension'],
            'size_bytes' => $media['size_bytes'],
            'width' => $media['width'] ?? null,
            'height' => $media['height'] ?? null,
            'title' => $media['title'] ?? null,
            'alt_text' => $media['alt_text'] ?? null,
        ]);

        return (int) $this->database->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $statement = $this->database->prepare('SELECT * FROM media WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $result = $statement->fetch();

        return $result !== false ? $result : null;
    }

    public function search(string $query = '', ?string $mimePrefix = null, int $limit = 100): array
    {
        $sql = 'SELECT * FROM media WHERE 1=1';
        $params = [];

        if ($query !== '') {
            $sql .= ' AND (original_name LIKE :query OR title LIKE :query OR alt_text LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        if ($mimePrefix !== null && $mimePrefix !== '') {
            $sql .= ' AND mime_type LIKE :mime';
            $params['mime'] = $mimePrefix . '%';
        }

        $sql .= ' ORDER BY created_at DESC LIMIT ' . max(1, min($limit, 500));
        $statement = $this->database->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll() ?: [];
    }

    public function delete(int $id): bool
    {
        $statement = $this->database->prepare('DELETE FROM media WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }
}

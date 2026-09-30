<?php

declare(strict_types=1);

namespace ZauberCMS\Content;

use PDO;

final class PageRepository
{
    public function __construct(private readonly PDO $database)
    {
    }

    public function all(): array
    {
        $statement = $this->database->query('SELECT * FROM pages ORDER BY updated_at DESC, id DESC');
        return $statement ? $statement->fetchAll() : [];
    }

    public function findBySlug(string $slug, bool $publishedOnly = false): ?array
    {
        $sql = 'SELECT * FROM pages WHERE slug = :slug';
        if ($publishedOnly) {
            $sql .= " AND status = 'published'";
        }
        $sql .= ' LIMIT 1';

        $statement = $this->database->prepare($sql);
        $statement->execute(['slug' => $slug]);
        $page = $statement->fetch();

        return is_array($page) ? $page : null;
    }

    public function save(array $data, ?int $id = null): int
    {
        $payload = [
            'title' => trim((string) ($data['title'] ?? '')),
            'slug' => trim((string) ($data['slug'] ?? '')),
            'content' => (string) ($data['content'] ?? ''),
            'status' => in_array(($data['status'] ?? 'draft'), ['draft', 'published'], true) ? $data['status'] : 'draft',
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
        ];

        if ($id === null) {
            $statement = $this->database->prepare('INSERT INTO pages (title, slug, content, status, seo_title, seo_description) VALUES (:title, :slug, :content, :status, :seo_title, :seo_description)');
            $statement->execute($payload);
            return (int) $this->database->lastInsertId();
        }

        $payload['id'] = $id;
        $statement = $this->database->prepare('UPDATE pages SET title=:title, slug=:slug, content=:content, status=:status, seo_title=:seo_title, seo_description=:seo_description WHERE id=:id');
        $statement->execute($payload);

        return $id;
    }
}

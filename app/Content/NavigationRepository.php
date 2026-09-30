<?php

declare(strict_types=1);

namespace ZauberCMS\Content;

use PDO;

final class NavigationRepository
{
    public function __construct(private readonly PDO $database)
    {
    }

    public function menu(string $location): ?array
    {
        $statement = $this->database->prepare('SELECT * FROM menus WHERE location = :location LIMIT 1');
        $statement->execute(['location' => $location]);
        $menu = $statement->fetch();

        if (!is_array($menu)) {
            return null;
        }

        $items = $this->database->prepare('SELECT mi.*, p.slug AS page_slug FROM menu_items mi LEFT JOIN pages p ON p.id = mi.page_id WHERE mi.menu_id = :menu_id ORDER BY mi.position ASC, mi.id ASC');
        $items->execute(['menu_id' => $menu['id']]);
        $menu['items'] = $items->fetchAll();

        return $menu;
    }

    public function createMenu(string $name, string $location): int
    {
        $statement = $this->database->prepare('INSERT INTO menus (name, location) VALUES (:name, :location)');
        $statement->execute(['name' => trim($name), 'location' => trim($location)]);
        return (int) $this->database->lastInsertId();
    }

    public function addItem(int $menuId, string $label, ?int $pageId = null, ?string $url = null, int $position = 0): int
    {
        $statement = $this->database->prepare('INSERT INTO menu_items (menu_id, page_id, label, url, position) VALUES (:menu_id, :page_id, :label, :url, :position)');
        $statement->execute([
            'menu_id' => $menuId,
            'page_id' => $pageId,
            'label' => trim($label),
            'url' => $url,
            'position' => max(0, $position),
        ]);

        return (int) $this->database->lastInsertId();
    }
}

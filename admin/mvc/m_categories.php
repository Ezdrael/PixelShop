<?php
// ===================================================================
// Файл: mvc/m_categories.php 🆕
// ===================================================================

class M_Categories {
    private $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    /**
     * Отримує всі категорії з сумарною кількістю товарів
     * (включаючи товари з усіх вкладених підкатегорій).
     * @return array
     */
    public function getAll() {
        // Цей складний рекурсивний запит вирішує ваше завдання
        $sql = "
            WITH RECURSIVE CategoryHierarchy AS (
                -- Початковий набір: всі категорії та їх прямі товари
                SELECT 
                    id, 
                    id as root_id, 
                    (SELECT COUNT(*) FROM goods WHERE category_id = categories.id) as direct_goods_count
                FROM 
                    categories

                UNION ALL

                -- Рекурсивна частина: спускаємось до дочірніх категорій
                SELECT 
                    c.id, 
                    ch.root_id, 
                    (SELECT COUNT(*) FROM goods WHERE category_id = c.id) as direct_goods_count
                FROM 
                    categories c
                INNER JOIN 
                    CategoryHierarchy ch ON c.parent_id = ch.id
            )
            -- Фінальний результат: групуємо за категорією і сумуємо товари
            SELECT 
                c.*, 
                SUM(ch.direct_goods_count) as goods_count
            FROM 
                categories c
            LEFT JOIN 
                CategoryHierarchy ch ON c.id = ch.root_id
            GROUP BY 
                c.id, c.name, c.parent_id
            ORDER BY 
                c.name ASC;
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $sql = "SELECT c.*, p.name as parent_name 
                FROM categories c 
                LEFT JOIN categories p ON c.parent_id = p.id 
                WHERE c.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function getChildren($parentId) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE parent_id = ? ORDER BY name ASC");
        $stmt->execute([(int)$parentId]);
        return $stmt->fetchAll();
    }

    public function add($data) {
        $parent_id = !empty($data['parent_id']) ? $data['parent_id'] : null;
        $stmt = $this->db->prepare("INSERT INTO categories (name, is_active, parent_id) VALUES (?, ?, ?)");
        return $stmt->execute([$data['name'], $data['is_active'], $parent_id]);
    }

    public function update($id, $data) {
        $parent_id = !empty($data['parent_id']) ? $data['parent_id'] : null;
        $stmt = $this->db->prepare("UPDATE categories SET name = ?, is_active = ?, parent_id = ? WHERE id = ?");
        return $stmt->execute([$data['name'], $data['is_active'], $parent_id, (int)$id]);
    }

    public function deleteById($id) {
        // 1. Перевіряємо наявність підкатегорій
        $stmt = $this->db->prepare("SELECT id FROM categories WHERE parent_id = ? LIMIT 1");
        $stmt->execute([(int)$id]);
        if ($stmt->fetch()) {
            return ['success' => false, 'reason' => 'children'];
        }

        // 2. Перевіряємо наявність товарів у цій категорії
        // (Припускаємо, що у вас є таблиця 'products' з колонкою 'category_id')
        try {
            $stmt = $this->db->prepare("SELECT id FROM products WHERE category_id = ? LIMIT 1");
            $stmt->execute([(int)$id]);
            if ($stmt->fetch()) {
                return ['success' => false, 'reason' => 'products'];
            }
        } catch (PDOException $e) {
            // Продовжуємо, навіть якщо таблиці 'products' не існує
        }
        
        // 3. Якщо все чисто, видаляємо категорію
        $deleteStmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        if ($deleteStmt->execute([(int)$id])) {
            return ['success' => true];
        }

        return ['success' => false, 'reason' => 'unknown'];
    }

    /**
     * Будує дерево категорій з плаского масиву
     * @param array $categories - плаский масив з бази даних
     * @return array - деревоподібний масив
     */
    public function buildTree(array $categories, $parentId = null) {
        $branch = [];
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $children = $this->buildTree($categories, $category['id']);
                if ($children) {
                    $category['children'] = $children;
                }
                $branch[] = $category;
            }
        }
        return $branch;
    }

    /**
     * Рекурсивно збирає ID всіх дочірніх категорій.
     * @param int $parentId - ID батьківської категорії
     * @param array $allCategories - плаский масив всіх категорій
     * @return array - масив, що містить ID батька та всіх його нащадків
     */
    public function getDescendantIds($parentId, array $allCategories) {
        $ids = [$parentId]; // Починаємо з ID самої батьківської категорії
        foreach ($allCategories as $category) {
            if ($category['parent_id'] == $parentId) {
                // Якщо знайшли дочірню, рекурсивно шукаємо її дочірні
                $ids = array_merge($ids, $this->getDescendantIds($category['id'], $allCategories));
            }
        }
        return $ids;
    }

    /**
     * Отримує всіх предків для заданої категорії.
     *
     * @param int $categoryId ID категорії, для якої шукаємо предків
     * @return array Масив предків, відсортований від найвищого до прямого батька
     */
    public function getAncestors(int $categoryId): array
    {
        $ancestors = [];
        $currentCategory = $this->getById($categoryId);

        // Ітеративно піднімаємось вгору по дереву, поки є батьківський ID
        while (isset($currentCategory['parent_id']) && $currentCategory['parent_id'] != 0) {
            $parentCategory = $this->getById($currentCategory['parent_id']);
            if ($parentCategory) {
                // Додаємо предка на початок масиву, щоб зберегти ієрархію
                array_unshift($ancestors, $parentCategory);
                $currentCategory = $parentCategory;
            } else {
                // Якщо батька не знайдено, зупиняємо цикл
                break;
            }
        }
        
        return $ancestors;
    }
}
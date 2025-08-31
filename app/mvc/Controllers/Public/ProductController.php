<?php
// app/Mvc/Controllers/Public/ProductController.php
namespace App\Mvc\Controllers\Public;

use App\Mvc\Models\Goods;

class ProductController extends PublicBaseController
{
    /**
     * Показує сторінку одного товару.
     */
    public function showAction()
    {
        $id = (int)($this->params['id'] ?? 0);
        if ($id <= 0) {
            return $this->notFoundAction();
        }

        $mGoods = new Goods();
        $good = $mGoods->getById($id);

        // Якщо товар не знайдено або він неактивний - показуємо 404
        if (!$good || !$good['is_active']) {
            return $this->notFoundAction();
        }

        $this->title = $good['name'] . ' | PixelShop';

        // Тут можна додати логіку для отримання фотографій товару, характеристик і т.д.

        $this->render('product.html.twig', [
            'good' => $good
        ]);
    }
}
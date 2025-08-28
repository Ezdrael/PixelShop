<?php
// app/Mvc/Controllers/Public/HomeController.php
namespace App\Mvc\Controllers\Public;

use App\Mvc\Models\Goods;

class HomeController extends PublicBaseController
{
    public function indexAction()
    {
        $this->title = 'Головна сторінка | PixelShop';

        // ✅ ВИПРАВЛЕНО: Шлях до CSS тепер вказує на папку з темою
        $this->addCSS(PROJECT_URL . '/themes/default/assets/css/home.css');

        $mGoods = new Goods();
        $newestGoods = $mGoods->getNewest(8);

        $this->render('home.html.twig', [
            'newestGoods' => $newestGoods
        ]);
    }
}
<?php
// app/Mvc/Controllers/Admin/CouponsController.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Coupons;

class CouponsController extends BaseController
{
    protected $mCoupons;

    public function __construct($params)
    {
        parent::__construct($params);
        if (!$this->hasPermission('coupons', 'v')) {
            $this->showAccessDenied();
            exit();
        }
        $this->mCoupons = new Coupons();
    }

    public function indexAction()
    {
        $this->title = 'Промокоди';
        $this->render('v_coupons_list');
    }
    
    public function addAction()
    {
        if (!$this->hasPermission('coupons', 'a')) return $this->showAccessDenied();
        $this->title = 'Новий промокод';
        $this->render('v_coupons_add');
    }

    public function editAction()
    {
        if (!$this->hasPermission('coupons', 'e')) return $this->showAccessDenied();
        $this->title = 'Редагування промокоду';
        $this->render('v_coupons_edit');
    }

    public function watchAction()
    {
        $this->title = 'Перегляд промокоду';
        $this->render('v_coupons_single');
    }
}
<?php
// app/Mvc/Controllers/Admin/DiscountsController.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Discounts;

class DiscountsController extends BaseController
{
    protected $mDiscounts;

    public function __construct($params)
    {
        parent::__construct($params);
        if (!$this->hasPermission('discounts', 'v')) {
            $this->showAccessDenied();
            exit();
        }
        $this->mDiscounts = new Discounts();
    }

    public function indexAction()
    {
        $this->title = 'Знижки';
        $this->render('v_discounts_list');
    }
    
    public function addAction()
    {
        if (!$this->hasPermission('discounts', 'a')) return $this->showAccessDenied();
        $this->title = 'Нова знижка';
        $this->render('v_discounts_add');
    }

    public function editAction()
    {
        if (!$this->hasPermission('discounts', 'e')) return $this->showAccessDenied();
        $this->title = 'Редагування знижки';
        $this->render('v_discounts_edit');
    }

    public function watchAction()
    {
        $this->title = 'Перегляд знижки';
        $this->render('v_discounts_single');
    }
}
<?php
// app/Mvc/Controllers/Admin/SalesController.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Sales;

class SalesController extends BaseController
{
    protected $mSales;

    public function __construct($params)
    {
        parent::__construct($params);
        if (!$this->hasPermission('sales', 'v')) {
            $this->showAccessDenied();
            exit();
        }
        $this->mSales = new Sales();
    }

    public function indexAction()
    {
        $this->title = 'Акції';
        $this->render('v_sales_list');
    }
    
    public function addAction()
    {
        if (!$this->hasPermission('sales', 'a')) return $this->showAccessDenied();
        $this->title = 'Нова акція';
        $this->render('v_sales_add');
    }

    public function editAction()
    {
        if (!$this->hasPermission('sales', 'e')) return $this->showAccessDenied();
        $this->title = 'Редагування акції';
        $this->render('v_sales_edit');
    }

    public function watchAction()
    {
        $this->title = 'Перегляд акції';
        $this->render('v_sales_single');
    }
}
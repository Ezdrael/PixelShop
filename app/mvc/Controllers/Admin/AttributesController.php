<?php
// app/Mvc/Controllers/Admin/AttributesController.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Attributes;

class AttributesController extends BaseController
{
    protected $mAttributes;

    public function __construct($params)
    {
        parent::__construct($params);
        if (!$this->hasPermission('attributes', 'v')) {
            $this->showAccessDenied();
            exit();
        }
        $this->mAttributes = new Attributes();
    }

    public function indexAction()
    {
        $this->title = 'Характеристики';
        $this->render('v_attributes_list');
    }

    public function addAction()
    {
        if (!$this->hasPermission('attributes', 'a')) return $this->showAccessDenied();
        $this->title = 'Нова характеристика';
        $this->render('v_attributes_add');
    }
}
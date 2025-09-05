<?php
// app/Mvc/Controllers/Admin/BonusPointsController.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\BonusPoints;

class BonusPointsController extends BaseController
{
    protected $mBonusPoints;

    public function __construct($params)
    {
        parent::__construct($params);
        if (!$this->hasPermission('bonus_points', 'v')) {
            $this->showAccessDenied();
            exit();
        }
        $this->mBonusPoints = new BonusPoints();
    }

    public function indexAction()
    {
        $this->title = 'Бонусні бали';
        $this->render('v_bonus_points_list');
    }
    
    public function addAction()
    {
        if (!$this->hasPermission('bonus_points', 'a')) return $this->showAccessDenied();
        $this->title = 'Нарахування / Списання балів';
        $this->render('v_bonus_points_add');
    }

    public function watchAction()
    {
        $this->title = 'Історія операцій';
        $this->render('v_bonus_points_single');
    }
}
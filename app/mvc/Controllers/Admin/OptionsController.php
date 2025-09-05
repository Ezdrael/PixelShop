<?php
// app/Mvc/Controllers/Admin/OptionsController.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Options;

class OptionsController extends BaseController
{
    protected $mOptions;

    public function __construct($params)
    {
        parent::__construct($params);
        if (!$this->hasPermission('options', 'v')) {
            $this->showAccessDenied();
            exit();
        }
        $this->mOptions = new Options();
    }

    public function indexAction()
    {
        $this->title = 'Опції товарів';
        $this->render('v_options_list');
    }
}
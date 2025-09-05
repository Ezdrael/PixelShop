<?php
// app/Mvc/Controllers/Admin/DashboardController.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Dashboard;

class DashboardController extends BaseController
{
    protected $mDashboard;

    public function __construct($params)
    {
        parent::__construct($params);
        if (!$this->hasPermission('dashboard', 'v')) {
            $this->showAccessDenied();
            exit();
        }
        $this->mDashboard = new Dashboard();
    }

    public function indexAction()
    {
        $this->title = 'Дашборд';
        $this->addJS('https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js');
        
        // Отримуємо всі дані для віджетів
        $widgets = $this->mDashboard->getWidgets($this->currentUser['id']);
        $stats = $this->mDashboard->getGlobalStats();
        $recentEvents = $this->mDashboard->getRecentCalendarEvents();
        $recentOperations = $this->mDashboard->getRecentStockOperations();
        $lowStockGoods = $this->mDashboard->getLowStockGoods();
        $marketplaceNews = $this->mDashboard->getMarketplaceNews(); // <-- ДОДАНО

        $this->render('v_dashboard', [
            'widgets' => $widgets,
            'stats' => $stats,
            'recentEvents' => $recentEvents,
            'recentOperations' => $recentOperations,
            'lowStockGoods' => $lowStockGoods,
            'marketplaceNews' => $marketplaceNews // <-- ДОДАНО
        ]);
    }
}
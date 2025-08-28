<?php
// app/Core/View.php
namespace App\Core;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class View
{
    private $twig;

    public function __construct(string $theme = 'default')
    {
        // ✅ ВИПРАВЛЕНО: Шлях тепер веде до папки public/themes/
        $templatesPath = BASE_PATH . '/public/themes/' . $theme . '/templates';
        
        $adminTemplatesPath = BASE_PATH . '/app/Mvc/Views/admin';

        $loader = new FilesystemLoader([$templatesPath, $adminTemplatesPath]);

        $this->twig = new Environment($loader, [
            // 'cache' => BASE_PATH . '/cache',
        ]);
    }

    public function render(string $template, array $data = [])
    {
        echo $this->twig->render($template, $data);
    }
}
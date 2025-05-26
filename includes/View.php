<?php

class View
{
    private static $instance = null;
    private $templateDir;
    private $layout = 'default';
    private $data = [];

    private function __construct()
    {
        $this->templateDir = __DIR__ . '/../templates/';
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    public function assign($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function assignMultiple($data)
    {
        $this->data = array_merge($this->data, $data);
    }

    public function render($template, $data = [])
    {
        // Fusionner les données
        $data = array_merge($this->data, $data);

        // Extraire les variables pour la vue
        extract($data);

        // Démarrer la capture de sortie
        ob_start();

        // Inclure le template
        $templateFile = $this->templateDir . $template . '.php';
        if (!file_exists($templateFile)) {
            throw new Exception("Template not found: $template");
        }

        include $templateFile;

        // Récupérer le contenu
        $content = ob_get_clean();

        // Si un layout est défini, l'utiliser
        if ($this->layout) {
            $layoutFile = $this->templateDir . 'layouts/' . $this->layout . '.php';
            if (!file_exists($layoutFile)) {
                throw new Exception("Layout not found: {$this->layout}");
            }

            // Le contenu sera disponible dans la variable $content
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    public function partial($template, $data = [])
    {
        // Fusionner les données
        $data = array_merge($this->data, $data);

        // Extraire les variables
        extract($data);

        // Inclure le partial
        $templateFile = $this->templateDir . 'partials/' . $template . '.php';
        if (!file_exists($templateFile)) {
            throw new Exception("Partial not found: $template");
        }

        include $templateFile;
    }

    public function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    public function formatDate($date, $format = 'd/m/Y')
    {
        return date($format, strtotime($date));
    }

    public function truncate($string, $length = 100, $append = '...')
    {
        if (strlen($string) <= $length) {
            return $string;
        }

        $string = substr($string, 0, $length);
        $string = substr($string, 0, strrpos($string, ' '));

        return $string . $append;
    }

    public function generatePagination($totalItems, $itemsPerPage, $currentPage, $baseUrl)
    {
        $pagination = new Pagination($totalItems, $itemsPerPage, $currentPage, $baseUrl);
        return $pagination->render();
    }

    public function renderAlert($message, $type = 'info')
    {
        $types = [
            'success' => 'alert-success',
            'info' => 'alert-info',
            'warning' => 'alert-warning',
            'danger' => 'alert-danger'
        ];

        $class = $types[$type] ?? $types['info'];

        return sprintf(
            '<div class="alert %s alert-dismissible fade show" role="alert">
                %s
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>',
            $class,
            $this->escape($message)
        );
    }
}
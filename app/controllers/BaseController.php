<?php
class BaseController {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    protected function render_view($view, $data = []) {
        extract($data);
        ob_start();
        include __DIR__ . "/../views/$view.php";
        return ob_get_clean();
    }
}
?>

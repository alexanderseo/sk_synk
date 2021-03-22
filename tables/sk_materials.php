<?php


class sk_materials extends bootstrap {
    private static $instance;

    private $log;

    private $materials;

    public function __construct() {
        parent::__construct();

        $this->log = [];
        $this->materials = [];
    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get() {
        global $wordpress;

        foreach ($wordpress['term_taxonomy'] as $taxonomy) {
            if ($taxonomy['taxonomy'] == 'pa_material') {
                $this->set_id($taxonomy);
                $this->set_taxonomy($taxonomy);
                $this->set_description($taxonomy);
                $this->set_name($taxonomy);
                $this->set_slug($taxonomy);
                $this->set_material_image($taxonomy);
                $this->set_material_type($taxonomy);
                $this->set_material_available($taxonomy);
            }
        }

        $this->set_log($this->log);
//        var_dump($this->materials);

        return $this->materials;
    }

    private function get_name($taxonomy) {
        global $wordpress;

        return  $wordpress['terms'][$taxonomy['term_taxonomy_id']]['name'];
    }

    private function get_slug($taxonomy) {
        global $wordpress;

        return  $wordpress['terms'][$taxonomy['term_taxonomy_id']]['slug'];
    }

    private function get_material_image($taxonomy) {
        global $wordpress;
        $images = [];

        $image_id = isset($wordpress['termmeta'][$taxonomy['term_taxonomy_id']]['material-image']) ? $wordpress['termmeta'][$taxonomy['term_taxonomy_id']]['material-image'] : "";

        if (!$image_id) {
            return serialize($images);
        }

        $images = $wordpress['attachments'][$image_id]['w300'];

        return serialize($images);
    }

    private function get_material_type($taxonomy) {
        global $wordpress;

        $material_type = isset($wordpress['termmeta'][$taxonomy['term_taxonomy_id']]['material-type']) ? $wordpress['termmeta'][$taxonomy['term_taxonomy_id']]['material-type'] : "";

        return $material_type;
    }

    private function get_material_available($taxonomy) {
        global $wordpress;

        $material_available = isset($wordpress['termmeta'][$taxonomy['term_taxonomy_id']]['material-sample-available']) ? $wordpress['termmeta'][$taxonomy['term_taxonomy_id']]['material-sample-available'] : "";

        return $material_available;
    }

    private function set_id($taxonomy) {
        $this->materials[$taxonomy['term_taxonomy_id']]['id'] = $taxonomy['term_taxonomy_id'];
    }

    private function set_taxonomy($taxonomy) {
        $this->materials[$taxonomy['term_taxonomy_id']]['taxonomy'] = $taxonomy['taxonomy'];
    }

    private function set_description($taxonomy) {
        $this->materials[$taxonomy['term_taxonomy_id']]['description'] = $taxonomy['description'];
    }

    private function set_name($taxonomy) {
        $this->materials[$taxonomy['term_taxonomy_id']]['name'] = $this->get_name($taxonomy);
    }

    private function set_slug($taxonomy) {
        $this->materials[$taxonomy['term_taxonomy_id']]['slug'] = $this->get_slug($taxonomy);
    }

    private function set_material_image($taxonomy) {
        $this->materials[$taxonomy['term_taxonomy_id']]['material_image'] = $this->get_material_image($taxonomy);
    }

    private function set_material_type($taxonomy) {
        $this->materials[$taxonomy['term_taxonomy_id']]['material_type'] = $this->get_material_type($taxonomy);
    }

    private function set_material_available($taxonomy) {
        $this->materials[$taxonomy['term_taxonomy_id']]['material_available'] = $this->get_material_available($taxonomy);
    }

}
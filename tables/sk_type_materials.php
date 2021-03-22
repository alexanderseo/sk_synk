<?php

class sk_type_materials extends bootstrap {
    private static $instance;

    private $log;

    private $type_materials;

    public function __construct() {
        parent::__construct();

        $this->log = [];
        $this->type_materials = [];
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
            if ($taxonomy['taxonomy'] == 'materials') {
                $this->set_id($taxonomy);
                $this->set_taxonomy($taxonomy);
                $this->set_description($taxonomy);
                $this->set_name($taxonomy);
                $this->set_slug($taxonomy);
            }
        }

        $this->set_log($this->log);
//        var_dump($this->type_materials);

        return $this->type_materials;
    }

    private function get_name($taxonomy) {
        global $wordpress;

        return  $wordpress['terms'][$taxonomy['term_taxonomy_id']]['name'];
    }

    private function get_slug($taxonomy) {
        global $wordpress;

        return  $wordpress['terms'][$taxonomy['term_taxonomy_id']]['slug'];
    }

    private function set_id($taxonomy) {
        $this->type_materials[$taxonomy['term_taxonomy_id']]['id'] = $taxonomy['term_taxonomy_id'];
    }

    private function set_taxonomy($taxonomy) {
        $this->type_materials[$taxonomy['term_taxonomy_id']]['taxonomy'] = $taxonomy['taxonomy'];
    }

    private function set_description($taxonomy) {
        $this->type_materials[$taxonomy['term_taxonomy_id']]['description'] = $taxonomy['description'];
    }

    private function set_name($taxonomy) {
        $this->type_materials[$taxonomy['term_taxonomy_id']]['name'] = $this->get_name($taxonomy);
    }

    private function set_slug($taxonomy) {
        $this->type_materials[$taxonomy['term_taxonomy_id']]['slug'] = $this->get_slug($taxonomy);
    }

}
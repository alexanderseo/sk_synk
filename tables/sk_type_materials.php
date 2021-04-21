<?php

class sk_type_materials extends bootstrap {
    private static $instance;

    private $type_materials;
    private $term_taxonomy;
    private $terms;

    public function __construct() {
        parent::__construct();

        global $wordpress;

        $this->type_materials = [];
        $this->terms = $wordpress['terms'];
        $this->term_taxonomy = $wordpress['term_taxonomy'];

    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get() {

        foreach ($this->term_taxonomy as $taxonomy) {
            if ($taxonomy['taxonomy'] == 'materials') {

                $this->set_id($taxonomy);
                $this->set_taxonomy($taxonomy);
                $this->set_description($taxonomy);
                $this->set_name($taxonomy, $this->terms);
                $this->set_slug($taxonomy, $this->terms);

            }
        }
//        var_dump($this->type_materials);

        return $this->type_materials;
    }

    private function get_name($taxonomy, $terms): string {
        return  $terms[$taxonomy['term_taxonomy_id']]['name'] ?? "";
    }

    private function get_slug($taxonomy, $terms): string {
        return  $terms[$taxonomy['term_taxonomy_id']]['slug'] ?? "";
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

    private function set_name($taxonomy, $terms) {
        $this->type_materials[$taxonomy['term_taxonomy_id']]['name'] = $this->get_name($taxonomy, $terms);
    }

    private function set_slug($taxonomy, $terms) {
        $this->type_materials[$taxonomy['term_taxonomy_id']]['slug'] = $this->get_slug($taxonomy, $terms);
    }

}
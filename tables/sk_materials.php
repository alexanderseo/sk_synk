<?php


class sk_materials extends bootstrap {
    private static $instance;

    private $log;
    private $materials;
    private $term_taxonomy;
    private $terms;
    private $termmeta;
    private $attachments;

    public function __construct() {
        parent::__construct();

        global $wordpress;

        $this->log = [];
        $this->materials = [];
        $this->term_taxonomy = $wordpress['term_taxonomy'];
        $this->terms = $wordpress['terms'];
        $this->termmeta = $wordpress['termmeta'];
        $this->attachments = $wordpress['attachments'];
    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get() {

        foreach ($this->term_taxonomy as $taxonomy) {
            if ($taxonomy['taxonomy'] == 'pa_material') {

                $this->set_id($taxonomy);
                $this->set_taxonomy($taxonomy);
                $this->set_description($taxonomy);
                $this->set_name($taxonomy, $this->terms);
                $this->set_slug($taxonomy, $this->terms);
                $this->set_material_image($taxonomy, $this->termmeta, $this->attachments);
                $this->set_material_type($taxonomy, $this->termmeta);
                $this->set_material_available($taxonomy, $this->termmeta);

            }
        }

        $this->set_log($this->log);
//        var_dump($this->materials);

        return $this->materials;
    }

    private function get_name($taxonomy, $terms): string {
        return  $terms[$taxonomy['term_taxonomy_id']]['name'] ?? "";
    }

    private function get_slug($taxonomy, $terms): string {
        return  $terms[$taxonomy['term_taxonomy_id']]['slug'] ?? "";
    }

    private function get_material_image($taxonomy, $termmeta, $attachments): string {
        $images = [];

        $image_id = $termmeta[$taxonomy['term_taxonomy_id']]['material-image'] ?? "";

        if (!$image_id) return serialize($images);

        $images = $attachments[$image_id]['original'];

        return serialize($images);
    }

    private function get_material_type($taxonomy, $termmeta): string {
        return $termmeta[$taxonomy['term_taxonomy_id']]['material-type'] ?? "";
    }

    private function get_material_available($taxonomy, $termmeta): string {
        return $termmeta[$taxonomy['term_taxonomy_id']]['material-sample-available'] ?? "";
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

    private function set_name($taxonomy, $terms) {
        $this->materials[$taxonomy['term_taxonomy_id']]['name'] = $this->get_name($taxonomy, $terms);
    }

    private function set_slug($taxonomy, $terms) {
        $this->materials[$taxonomy['term_taxonomy_id']]['slug'] = $this->get_slug($taxonomy, $terms);
    }

    private function set_material_image($taxonomy, $termmeta, $attachments) {
        $this->materials[$taxonomy['term_taxonomy_id']]['material_image'] = $this->get_material_image($taxonomy, $termmeta, $attachments);
    }

    private function set_material_type($taxonomy, $termmeta) {
        $this->materials[$taxonomy['term_taxonomy_id']]['material_type'] = $this->get_material_type($taxonomy, $termmeta);
    }

    private function set_material_available($taxonomy, $termmeta) {
        $this->materials[$taxonomy['term_taxonomy_id']]['material_available'] = $this->get_material_available($taxonomy, $termmeta);
    }

}
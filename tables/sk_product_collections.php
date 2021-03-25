<?php


class sk_product_collections extends bootstrap {

    private static $instance;
    private $product_collections;
    private $term_taxonomy;
    private $terms;

    public function __construct() {
        parent::__construct();

        global $wordpress;

        $this->term_taxonomy = $wordpress['term_taxonomy'];
        $this->terms = $wordpress['terms'];
        $this->product_collections = [];

    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get() {
        $collections_ids = $this->get_ids($this->term_taxonomy);

        foreach ($collections_ids as $term_id) {
            $this->set_id('id', $term_id);
            $this->set_name('name', $term_id, $this->terms);
            $this->set_slug('slug', $term_id, $this->terms);
            $this->set_count('count', $term_id, $this->term_taxonomy);
        }

        return $this->product_collections;
    }

    /**
     * @param $taxonomies
     * @return array
     * Получаем все ids категорий из таксономий
     */
    private function get_ids($taxonomies) {
        $data = [];

        foreach ($taxonomies as $value) {
            if ($value['taxonomy'] == 'collection-product') {
                $data[] = $value['term_id'];
            }
        }

        return $data;
    }

    private function get_id(int $id): int {
        return $id;
    }

    private function get_name(int $id, array $terms): string {
        $name = "";

        if (isset($terms[$id])) {
//            $name = mb_convert_encoding($terms[$id]['name'], "UTF-8");
            $name = $terms[$id]['name'];
        }

//        var_dump('===========', $name);

        return $name;
    }

    private function get_slug(int $id, array $terms): string {
        $slug = "";

        if (isset($terms[$id])) {
            $slug = $terms[$id]['slug'];
        }

        return $slug;
    }

    private function get_count(int $id, array $taxonomy): string {
        $count = "0";

        if (isset($taxonomy[$id])) {
            $count = $taxonomy[$id]['count'];
        }

        return $count;
    }

    private function set_id(string $key, int $id): void {
        $this->product_collections[$id][$key] = $this->get_id($id);
    }

    private function set_name(string $key, int $id, array $terms): void {
        $this->product_collections[$id][$key] = $this->get_name($id, $terms);
    }

    private function set_slug(string $key, int $id, array $terms): void {
        $this->product_collections[$id][$key] = $this->get_slug($id, $terms);
    }

    private function set_count(string $key, int $id, array $taxonomy): void {
        $this->product_collections[$id][$key] = $this->get_count($id, $taxonomy);
    }
}
<?php


class sk_interiors extends bootstrap {

    private static $instance;
    private $interiors_data;
    private $interiors;
    private $ids_interiors;
    private $postmeta;
    private $attachments;
    private $terms;
    private $termmeta;
    private $mapplic;

    public function __construct() {
        parent::__construct();

        global $wordpress;

        $this->interiors = $wordpress['posts_interiors'];
        $this->postmeta = $wordpress['postmeta'];
        $this->attachments = $wordpress['attachments'];
        $this->ids_interiors = $wordpress['posts_ids']['interiors'];
        $this->terms = $wordpress['terms'];
        $this->termmeta = $wordpress['termmeta'];
        $this->mapplic = $wordpress['mapplic'];
        $this->interiors_data = [];

    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get() {

        foreach ($this->ids_interiors as $id) {
//            $id = 192387;
            $post_interiors = $this->interiors[$id];
            $this_postmeta = $this->postmeta[$id];

            $this->set_id('id', $id);
            $this->set_name('name', $id, $post_interiors);
            $this->set_slug('slug', $id, $post_interiors);
            $this->set_content('content', $id, $post_interiors);
            $this->set_status('status', $id, $post_interiors);
            $this->set_modified_unix('modified_unix', $id, $post_interiors);
            $this->set_interior_description('description', $id, $this_postmeta);
            $this->set_gallery('gallery', $id, $this_postmeta, $this->attachments);
            $this->set_ideas_items('ideas_items', $id, $this_postmeta);
            $this->set_ideas_colors('ideas_colors', $id, $this_postmeta, $this->terms, $this->termmeta);
            $this->set_thumbnail('thumbnail', $id, $this_postmeta, $this->attachments);
            $this->set_interior('interior', $id, $this_postmeta, $this->mapplic);
            $this->set_location('location', $id, $this_postmeta, $this->terms);
        }

//        var_dump('-----------', $this->interiors_data);

        return $this->interiors_data;
    }

    private function get_id($id) {
        return $id;
    }

    private function get_name($interiors) {
        return $interiors['post_title'] ?? "";
    }

    private function get_slug($interiors) {
        return $interiors['post_name'] ?? "";
    }

    private function get_content($interiors) {
        return $interiors['post_content'] ?? "";
    }

    private function get_status($interiors) {
        return $interiors['post_status'] ?? "";
    }

    private function get_modified_unix($interiors) {
        return isset($interiors['post_modified']) ? strtotime($interiors['post_modified']) : "";
    }

    private function get_interior_description($postmeta) {
        return $postmeta['interior-ideas-description'] ?? "";
    }

    private function get_gallery($postmeta, $attachments) {
        $data = [];

        if (isset($postmeta['interior-ideas-gallery'])) {
            if (!empty(unserialize($postmeta['interior-ideas-gallery']))) {
                foreach (unserialize($postmeta['interior-ideas-gallery']) as $id_img) {
                    if (isset($attachments[$id_img])) {
                        $data[] = [
                            'original' => $attachments[$id_img]['original'],
                            'w300' => $attachments[$id_img]['w300']
                        ];
                    }
                }
            }
        }

        return serialize($data);
    }

    private function get_ideas_items($postmeta) {
        $items = [];

        if (isset($postmeta['interior-ideas-items'])) {
            foreach(unserialize($postmeta['interior-ideas-items']) as $item) {
                $items[] = $item;
            }
        }

        $str_items = implode(',', $items);

        return $str_items;
    }

    private function get_ideas_colors($postmeta, $terms, $termmeta) {
        $color = [];

        if (isset($postmeta['interior-ideas-colors'])) {
            if (isset($terms[$postmeta['interior-ideas-colors']])) {
                $color = [
                    'name' => $terms[$postmeta['interior-ideas-colors']]['name'],
                    'slug' => $terms[$postmeta['interior-ideas-colors']]['slug'],
                    'hex' => $this->get_hex($postmeta['interior-ideas-colors'], $termmeta)
                ];
            }
        }

        return serialize($color);
    }

    private function get_hex($id_color, $termmeta): string {
        $color_hex = "";

        if (isset($termmeta[$id_color])) {
            if (isset($termmeta[$id_color]['color-hex-code'])) {
                $color_hex = $termmeta[$id_color]['color-hex-code'];
            }
        }

        return $color_hex;
    }

    private function get_thumbnail($postmeta, $attachments) {
        $data = [];

        if (isset($postmeta['_thumbnail_id'])) {
            $data['original'] = $attachments[$postmeta['_thumbnail_id']]['original'] ?? "";
        }

        return serialize($data);
    }

    private function get_interior($this_postmeta, $mapplic) {
        $interior = [];

        if (isset($this_postmeta['interior'])) {

            if (!empty(unserialize($this_postmeta['interior']))) {
                $id_mapplic = unserialize($this_postmeta['interior']);
                if (isset($mapplic[$id_mapplic[0]])) {
                    $interior = [
                        'content' => $mapplic[$id_mapplic[0]]['post_content']
                    ];
                }
            }
        }

        return serialize($interior);
    }

    private function get_location($postmeta, $terms, $callback) {
        $location = [];

        if ($location = $terms[$postmeta['location']] ?? []) {
            $location = [
                'name' => $location['name'],
                'slug' => $location['slug']
            ];
        }

        $callback($location['slug'] ?? "");

        return serialize($location);
    }

    private function set_id($key, $id) {
        $this->interiors_data[$id][$key] = $this->get_id($id);
    }

    private function set_name($key, $id, $post_interiors) {
        $this->interiors_data[$id][$key] = $this->get_name($post_interiors);
    }

    private function set_slug($key, $id, $post_interiors) {
        $this->interiors_data[$id][$key] = $this->get_slug($post_interiors);
    }

    private function set_content($key, $id, $post_interiors) {
        $this->interiors_data[$id][$key] = $this->get_content($post_interiors);
    }

    private function set_status($key, $id, $post_interiors) {
        $this->interiors_data[$id][$key] = $this->get_status($post_interiors);
    }

    private function set_modified_unix($key, $id, $post_interiors): void {
        $this->interiors_data[$id][$key] = $this->get_modified_unix($post_interiors);
    }

    private function set_interior_description($key, $id, $this_postmeta) {
        $this->interiors_data[$id][$key] = $this->get_interior_description($this_postmeta);
    }

    private function set_gallery($key, $id, $this_postmeta, $attachments) {
        $this->interiors_data[$id][$key] = $this->get_gallery($this_postmeta, $attachments);
    }

    private function set_ideas_items($key, $id, $this_postmeta) {
        $this->interiors_data[$id][$key] = $this->get_ideas_items($this_postmeta);
    }

    private function set_ideas_colors($key, $id, $this_postmeta, $terms, $termmeta) {
        $this->interiors_data[$id][$key] = $this->get_ideas_colors($this_postmeta, $terms, $termmeta);
    }

    private function set_thumbnail($key, $id, $this_postmeta, $attachments) {
        $this->interiors_data[$id][$key] = $this->get_thumbnail($this_postmeta, $attachments);
    }

    private function set_interior($key, $id, $this_postmeta, $mapplic) {
        $this->interiors_data[$id][$key] = $this->get_interior($this_postmeta, $mapplic);
    }

    /**
     * @param $key
     * @param $id
     * @param $this_postmeta
     * @param $terms
     * Чтобы меньше бегать циклом
     */
    private function set_location($key, $id, $this_postmeta, $terms) {
        $this->interiors_data[$id][$key] = $this->get_location(
            $this_postmeta,
            $terms,
            function($slug) use ($id) {
                $this->interiors_data[$id]['location_slug'] = $slug;
            });
    }

}
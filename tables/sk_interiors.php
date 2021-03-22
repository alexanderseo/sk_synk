<?php


class sk_interiors extends bootstrap {

    private static $instance;
    private $interiors_data;
    private $interiors;
    private $ids_interiors;
    private $postmeta;
    private $attachments;
    private $terms;

    public function __construct() {
        parent::__construct();

        global $wordpress;

        $this->interiors = $wordpress['posts_interiors'];
        $this->postmeta = $wordpress['postmeta'];
        $this->attachments = $wordpress['attachments'];
        $this->ids_interiors = $wordpress['posts_ids']['interiors'];
        $this->terms = $wordpress['terms'];
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
            $this->set_ideas_colors('ideas_colors', $id, $this_postmeta, $this->terms);

        }

//        var_dump('-----------', $this->interiors_data);

        return $this->interiors_data;
    }

    private function get_id($id) {
        return $id;
    }

    private function get_name($interiors) {
        return isset($interiors['post_title']) ? $interiors['post_title'] : "";
    }

    private function get_slug($interiors) {
        return isset($interiors['post_name']) ? $interiors['post_name'] : "";
    }

    private function get_content($interiors) {
        return isset($interiors['post_content']) ? $interiors['post_content'] : "";
    }

    private function get_status($interiors) {
        return isset($interiors['post_status']) ? $interiors['post_status'] : "";
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
            foreach (unserialize($postmeta['interior-ideas-gallery']) as $id_img) {
                if (isset($attachments[$id_img])) {
                    $data['original'] = $attachments[$id_img]['original'];
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

    private function get_ideas_colors($postmeta, $terms) {
        $color = [];

        if (isset($postmeta['interior-ideas-colors'])) {
            if (isset($terms[$postmeta['interior-ideas-colors']])) {
                $color = [
                    'name' => $terms[$postmeta['interior-ideas-colors']]['name'],
                    'slug' => $terms[$postmeta['interior-ideas-colors']]['slug']
                ];
            }
        }

        return serialize($color);
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

    private function set_ideas_colors($key, $id, $this_postmeta, $terms) {
        $this->interiors_data[$id][$key] = $this->get_ideas_colors($this_postmeta, $terms);
    }

}
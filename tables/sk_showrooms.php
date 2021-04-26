<?php
class sk_showrooms extends bootstrap {
    private static $instance;

    private $log;
    private $ids_showrooms;
    private $posts;
    private $postmeta;
    private $terms;
    private $termmeta;
    private $attachments;
    private $data;

    public function __construct() {
        parent::__construct();

        global $wordpress;

        $this->log = array();
        $this->ids_showrooms = $wordpress['posts_ids']['showrooms'];
        $this->posts = $wordpress['posts'];
        $this->postmeta = $wordpress['postmeta'];
        $this->terms = $wordpress['terms'];
        $this->termmeta = $wordpress['termmeta'];
        $this->attachments = $wordpress['attachments'];
        $this->data = array();
    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get() {

        foreach ($this->ids_showrooms as $id) {
            $posts = $this->posts[$id];
            $postmeta = $this->postmeta[$id];

            if ($this->filter_sklad($postmeta)) {
                $this->set_id($id);
                $this->set_slug($id, $posts);
                $this->set_name($id, $posts);
                $this->set_group($id, $postmeta, $this->terms, $this->termmeta);
                $this->set_cover($id, $postmeta, $this->attachments);
                $this->set_address($id, $postmeta);
                $this->set_coordinates($id, $postmeta);
                $this->set_working_hours($id, $postmeta);
                $this->set_description($id, $postmeta);
                $this->set_city($id, $postmeta, $this->terms);
                $this->set_subway($id, $postmeta);
                $this->set_area($id, $postmeta);
                $this->set_services($id, $postmeta);
                $this->set_buttons($id, $postmeta, $this->attachments);
                $this->set_order($id, $postmeta);
                $this->set_hidden($id, $postmeta);
                $this->set_contact_form($id, $postmeta);
                $this->set_blocking($id, $postmeta);
                $this->set_gallery($id, $postmeta, $this->attachments);
            }
        }

//        var_dump($this->data);

//        $this->set_log($this->log);

        return $this->data;
    }

    private function filter_sklad($postmeta): bool {
        if (isset($postmeta['showroom-is-warehouse'])) {
            if ($postmeta['showroom-is-warehouse'] == '1') {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    private function get_slug($post): string {
        return $post['post_name'] ?? "";
    }

    private function get_name($post): string {
        return $post['post_title'] ?? "";
    }

    private function get_group($postmeta, $terms, $termmeta) {
        $data = [];

        if (isset($postmeta['showroom-group'])) {
            if (empty($postmeta['showroom-group'])) {
                return serialize($data);
            }
        }

        foreach (unserialize($postmeta['showroom-group']) as $value) {
            $terms = $terms[$value];
            $termmeta = $termmeta[$value];

            $data[$value]['id'] = $value;
            $data[$value]['name'] = $terms['name'] ?? 0;
            $data[$value]['subtitle'] = $termmeta['showroom-list-subtitle'] ?? 0;
            $data[$value]['order'] = $termmeta['showroom-list-order'] ?? 0;
            $data[$value]['icon'] = $termmeta['showroom-list-icon'] ?? 0;

        }

        return serialize($data);
    }

    private function get_cover($postmeta, $attachments): string {
        $data = [];

        if (!isset($postmeta['showroom-cover'])) return serialize($data);

        $data = $attachments[$postmeta['showroom-cover']] ?? "";

        return serialize($data);
    }

    private function get_address($postmeta): string {
        return $postmeta['showroom-address'] ?? 0;
    }

    private function get_coordinates($postmeta): string {
        return $postmeta['showroom-coordinates'] ?? 0;
    }

    private function get_working_hours($postmeta): string {
        return $postmeta['showroom-working-hours'] ?? 0;
    }

    private function get_desription($postmeta): string {
        return $postmeta['showroom-description'] ?? 0;
    }

    private function get_city($postmeta, $terms): string {
        $data = [];

        if (!isset($postmeta['showroom-city'])) return serialize($data);

        $data['name'] = $terms[$postmeta['showroom-city']]['name'] ?? "";
        $data['slug'] = $terms[$postmeta['showroom-city']]['slug'] ?? "";

        return serialize($data);
    }

    private function get_subway($postmeta): string {
        $data = [];

        $data['station'] = $postmeta['showroom-subway_showroom-subway-station'] ?? 0;
        $data['color'] = $postmeta['showroom-subway_showroom-subway-color'] ?? 0;
        $data['distance'] = $postmeta['showroom-subway_showroom-subway-distance'] ?? 0;

        return serialize($data);
    }

    private function get_area($postmeta): string {
        return $postmeta['showroom-subgroup-1_showroom-area'] ?? 0;
    }

    private function get_services($postmeta): string {
        return $postmeta['showroom-subgroup-1_showroom-services'] ?? 0;
    }

    private function get_buttons($postmeta, $attachments): string {
        $data = [];

        $keys = preg_grep('/^showroom-waypoints_/', array_keys($postmeta));

        if (!$keys) return serialize($data);

        foreach ($keys as $key) {
            if (strpos($key, 'title') !== false) {
                $exploded_key = explode('_', $key);

                $data[$exploded_key[1]]['title'] = $postmeta[$key];
            }

            if (strpos($key, 'image') !== false) {
                $exploded_key = explode('_', $key);

                $data[$exploded_key[1]]['image'] = serialize($attachments[$postmeta[$key]]);
            }
        }

        return serialize($data);
    }

    private function get_order($postmeta) {
        return $postmeta['showroom-order'] ?? 0;
    }

    private function get_hidden($postmeta): string {
        return $postmeta['showroom-hidden'] ?? 0;
    }

    private function get_contact_form($postmeta): string {
        return $postmeta['showroom-contact-form'] ?? 0;
    }

    private function get_blocking($postmeta): string {
        $data = [];

        $data['disabled'] = $postmeta['showroom-state_showroom-disabled'] ?? 0;
        $data['reason'] = $postmeta['showroom-state_showroom-disabled-reason'] ?? 0;

        return serialize($data);
    }

    private function get_gallery($postmeta, $attachments): string {
        $gallery = [];

        if (isset($postmeta['showroom-gallery'])) {
            foreach (unserialize($postmeta['showroom-gallery']) as $id_img) {
                $gallery[] = $attachments[$id_img] ?? "";
            }
        }
        $data = [];
        foreach ($gallery as $item) {
            $data[] = [
                'original' => $item['original'],
                'w300' => $item['w300'],
                'w150' => $item['w150'],
            ];
        }

        return serialize($data);
    }

    private function set_id($id) {
        $this->data[$id]['id'] = (int) $id;
    }

    private function set_slug($id, $post): void {
        $this->data[$id]['slug'] = $this->get_slug($post);
    }

    private function set_name($id, $post): void {
        $this->data[$id]['name'] = $this->get_name($post);
    }

    private function set_group($id, $postmeta, $terms, $termmeta): void {
        $this->data[$id]['group'] = $this->get_group($postmeta, $terms, $termmeta);
    }

    private function set_cover($id, $postmeta, $attachments): void {
        $this->data[$id]['cover'] = $this->get_cover($postmeta, $attachments);
    }

    private function set_address($id, $postmeta): void {
        $this->data[$id]['address'] = $this->get_address($postmeta);
    }

    private function set_coordinates($id, $postmeta): void {
        $this->data[$id]['coordinates'] = $this->get_coordinates($postmeta);
    }

    private function set_working_hours($id, $postmeta): void {
        $this->data[$id]['working_hours'] = $this->get_working_hours($postmeta);
    }

    private function set_description($id, $postmeta): void {
        $this->data[$id]['description'] = $this->get_desription($postmeta);
    }

    private function set_city($id, $postmeta, $terms): void {
        $this->data[$id]['city'] = $this->get_city($postmeta, $terms);
    }

    private function set_subway($id, $postmeta): void {
        $this->data[$id]['subway'] = $this->get_subway($postmeta);
    }

    private function set_area($id, $postmeta): void {
        $this->data[$id]['area'] = $this->get_area($postmeta);
    }

    private function set_services($id, $postmeta): void {
        $this->data[$id]['services'] = $this->get_services($postmeta);
    }

    private function set_buttons($id, $postmeta, $attachments): void {
        $this->data[$id]['buttons'] = $this->get_buttons($postmeta, $attachments);
    }

    private function set_order($id, $postmeta): void {
        $this->data[$id]['order'] = $this->get_order($postmeta);
    }

    private function set_hidden($id, $postmeta): void {
        $this->data[$id]['hidden'] = $this->get_hidden($postmeta);
    }

    private function set_contact_form($id, $postmeta): void {
        $this->data[$id]['contact_form'] = $this->get_contact_form($postmeta);
    }

    private function set_blocking($id, $postmeta): void {
        $this->data[$id]['blocking'] = $this->get_blocking($postmeta);
    }

    private function set_gallery($id, $postmeta, $attachments): void {
        $this->data[$id]['gallery'] = $this->get_gallery($postmeta, $attachments);
    }

}
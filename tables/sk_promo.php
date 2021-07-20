<?php


class sk_promo extends bootstrap {

    private static $instance;
    private $data;
    private $posts;
    private $postmeta;
    private $attachments;

    public function __construct() {
        parent::__construct();
        global $wordpress;

        $this->data = [];
        $this->posts = $wordpress['posts'];
        $this->postmeta = $wordpress['postmeta'];
        $this->attachments = $wordpress['attachments'];

    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get() {
        foreach ($this->posts as $key => $promo) {
            if ($promo['post_type'] == 'promo') {

                $postmeta = $this->get_meta($key, $this->postmeta);

                $this->set_id('id', $key);
                $this->set_modified_unix('modified_unix', $key, $promo);
                $this->set_name('name', $key, $promo);
                $this->set_slug('slug', $key, $promo);
                $this->set_text_content('text', $key, $promo);
                $this->set_description('description', $key, $postmeta);
                $this->set_cover('cover', $key, $postmeta, $this->attachments);
                $this->set_target_link('target_link', $key, $postmeta);
                $this->set_date_start('date_start', $key, $postmeta);
//                $this->set_color('color', $key, $postmeta);
//                $this->set_title_1('title_1', $key, $postmeta);
//                $this->set_title_2('title_2', $key, $postmeta);
                $this->set_slider('slider', $key, $postmeta);
//                $this->set_lead('lead_name', $key, $postmeta);
//                $this->set_anchor('anchor', $key, $postmeta);
                $this->set_type('type', $key, $postmeta);
                $this->set_notification('notification', $key, $postmeta);
                $this->set_discount('discount', $key, $postmeta);
            }
        }

        return $this->data;
    }

    private function get_meta(int $id, array $postmeta) {
        return $postmeta[$id] ?? '';
    }

    private function get_id(int $id): int {
        return (int) $id;
    }

    private function get_modified_unix(int $id, array $promo): string {
        return strtotime($promo['post_modified']) . $id;
    }

    private function get_name(array $promo): string {
        return $promo['post_title'] ?? '';
    }

    private function get_slug(array $promo): string {
        return $promo['post_name'] ?? '';
    }

    private function get_text(array $promo): string {
        return $promo['post_content'] ?? '';
    }

    private function get_description(array $postmeta): string {
        return $postmeta['description'] ?? '';
    }

    private function get_cover(array $postmeta, array $attachments): string {
        $data = [];

        $cover_id = $postmeta['cover'] ?? '';
        if (empty($cover_id)) return serialize($data);

        if (isset($attachments[$cover_id])) {
            $data[] = serialize([
                'original' => $attachments[$cover_id]['original'],
                'w300' => $attachments[$cover_id]['w300']
            ]);
        }

        return serialize($data);
    }

    private function get_target_link(array $postmeta): string {
        return $postmeta['target'] ?? '';
    }

    private function get_date_start(array $postmeta): string {
        return $postmeta['date'] ?? '';
    }

//    private function get_color(array $postmeta): string {
//        return $postmeta['color'] ?? '';
//    }

//    private function get_title_1(array $postmeta): string {
//        return $postmeta['title-1'] ?? '';
//    }

//    private function get_title_2(array $postmeta): string {
//        return $postmeta['title-2'] ?? '';
//    }

    private function get_slider(array $postmeta): string {

        $count = $postmeta['sliders'] ?? '';
        if (empty($count)) return serialize([]);

        $custom_size = 10;
        $str_slider = [];
        for ($i = 0; $i < $custom_size; $i++) {
            foreach ($postmeta as $key => $item) {
                if (stripos($key, 'sliders_' . $i) === 0) {
                    $str_slider[$i][] = $item;
                }
            }
        }

        return serialize($str_slider);
    }

//    private function get_lead(array $postmeta): string {
//        return $postmeta['lead'] ?? '';
//    }

//    private function get_anchor(array $postmeta): string {
//        return $postmeta['anchor'] ?? '';
//    }

    private function get_type(array $postmeta): string {
        return $postmeta['type'] ?? '';
    }

    private function get_notification(array $postmeta): string {
        return $postmeta['notification'] ?? '';
    }

    private function get_discount(array $postmeta): string {
        return $postmeta['discount'] ?? '';
    }

    private function set_id(string $key, int $id): void {
        $this->data[$id][$key] = $this->get_id($id);
    }

    private function set_modified_unix(string $key, int $id, array $promo): void {
        $this->data[$id][$key] = $this->get_modified_unix($id, $promo);
    }

    private function set_name(string $key, int $id, array $promo): void {
        $this->data[$id][$key] = $this->get_name($promo);
    }

    private function set_slug(string $key, int $id, array $promo): void {
        $this->data[$id][$key] = $this->get_slug($promo);
    }

    private function set_text_content(string $key, int $id, array $promo): void {
        $this->data[$id][$key] = $this->get_text($promo);
    }

    private function set_description(string $key, int $id, array $postmeta): void {
        $this->data[$id][$key] = $this->get_description($postmeta);
    }

    private function set_cover(string $key, int $id, array $postmeta, array $attachments): void {
        $this->data[$id][$key] = $this->get_cover($postmeta, $attachments);
    }

    private function set_target_link(string $key, int $id, array $postmeta): void {
        $this->data[$id][$key] = $this->get_target_link($postmeta);
    }

    private function set_date_start(string $key, int $id, array $postmeta): void {
        $this->data[$id][$key] = $this->get_date_start($postmeta);
    }

//    private function set_color(string $key, int $id, array $postmeta): void {
//        $this->data[$id][$key] = $this->get_color($postmeta);
//    }

//    private function set_title_1(string $key, int $id, array $postmeta): void {
//        $this->data[$id][$key] = $this->get_title_1($postmeta);
//    }

//    private function set_title_2(string $key, int $id, array $postmeta): void {
//        $this->data[$id][$key] = $this->get_title_2($postmeta);
//    }

    private function set_slider(string $key, int $id, array $postmeta): void {
        $this->data[$id][$key] = $this->get_slider($postmeta);
    }

//    private function set_lead(string $key, int $id, array $postmeta): void {
//        $this->data[$id][$key] = $this->get_lead($postmeta);
//    }

//    private function set_anchor(string $key, int $id, array $postmeta): void {
//        $this->data[$id][$key] = $this->get_anchor($postmeta);
//    }

    private function set_type(string $key, int $id, array $postmeta): void {
        $this->data[$id][$key] = $this->get_type($postmeta);
    }

    private function set_notification(string $key, int $id, array $postmeta): void {
        $this->data[$id][$key] = $this->get_notification($postmeta);
    }

    private function set_discount(string $key, int $id, array $postmeta): void {
        $this->data[$id][$key] = $this->get_discount($postmeta);
    }
}
<?php


class sk_photo_pokupatelei extends bootstrap {
    private static $instance;

    private $data;
    private $customer_photos;
    private $postmeta;
    private $attachments;
    private $terms;


    public function __construct() {
        parent::__construct();

        global $wordpress;

        $this->customer_photos = $wordpress['customer_photos'];
        $this->postmeta = $wordpress['postmeta'];
        $this->attachments = $wordpress['attachments'];
        $this->terms = $wordpress['terms'];

        $this->data = [];

    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get(): array {

        foreach ($this->customer_photos as $customer) {
            $id = $customer['ID'];
            $meta_array = $this->get_meta($customer, $this->postmeta);

            $this->set_id('id', $customer);
            $this->set_slug('slug', $customer);
            $this->set_title('title', $customer);
            $this->set_collection('collection', $customer, $meta_array);
            $this->set_photos('photos', $customer, $meta_array, $this->attachments);
            $this->set_category('category', $id, $meta_array, $this->terms);
        }

        return $this->data;
    }

    private function get_meta($page, $postmeta): array {
        $data = [];

        if (isset($postmeta[$page['ID']])) {
            $data = $postmeta[$page['ID']];
        }

        return $data;
    }

    private function get_id(array $customer): int {
        return (int) $customer['ID'];
    }

    private function get_slug(array $customer): string {
        return $customer['post_name'] ?? '';
    }

    private function get_title(array $customer): string {
        return $customer['post_title'] ?? '';
    }

    private function get_collection(array $postmeta): string {
        return $postmeta['collection'] ?? '';
    }

    private function get_photos(array $postmeta, array $attachments): string {
        $data = [];
        $array_indexes = ['0', '1', '2', '3', '4', '5', '6'];

        foreach ($array_indexes as $index) {
            $key = 'photos_' . $index . '_gallery';
            if (isset($postmeta[$key])) {
                if (!empty($postmeta[$key])) {
                    $data = unserialize($postmeta[$key]);
                }
            }
        }

        $attachments_data = [];
        if (!empty($data)) {
            foreach ($data as $item) {
                if (isset($attachments[$item])) {
                    $attachments_data[] = $attachments[$item];
                }
            }
        }

        return serialize($attachments_data);
    }

    public function get_category(array $postmeta, array $terms) {
        $data = [];
        $array_indexes = ['0', '1', '2', '3', '4', '5', '6'];

        foreach ($array_indexes as $index) {
            $key = 'photos_' . $index . '_category';
            if (isset($postmeta[$key])) {
                $meta_value = $postmeta[$key];
                if (!empty($meta_value)) {
                    foreach ($terms as $k => $term) {
                        if ($term['slug'] == $meta_value) {
                            $data[] = $term;
                        }
                    }
                }
            }
        }

        return serialize($data);
    }

    private function set_id(string $key, array $customer): void {
        $this->data[$customer['ID']][$key] = $this->get_id($customer);
    }

    private function set_slug(string $key, array $customer): void {
        $this->data[$customer['ID']][$key] = $this->get_slug($customer);
    }

    private function set_title(string $key, array $customer): void {
        $this->data[$customer['ID']][$key] = $this->get_title($customer);
    }

    private function set_collection(string $key, array $customer, array $postmeta): void {
        $this->data[$customer['ID']][$key] = $this->get_collection($postmeta);
    }

    private function set_photos(string $key, array $customer, array $postmeta, array $attachments): void {
        $this->data[$customer['ID']][$key] = $this->get_photos($postmeta, $attachments);
    }

    private function set_category(string $key, string $id, array $postmeta, array $terms): void {
        $this->data[$id][$key] = $this->get_category($postmeta, $terms);
    }
}
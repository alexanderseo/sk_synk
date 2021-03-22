<?php

class sk_fabrics extends bootstrap {
    private static $instance;

    private $log;

    private $data;
    private $ids_fabrics;
    private $posts;
    private $postmeta;
    private $terms;
    private $termmeta;
    private $attachments;
    private $posts_by_post_name;
    private $term_taxonomy;

    public function __construct() {
        parent::__construct();

        global $wordpress;

        $this->log = [];
        $this->data = [];
        $this->ids_fabrics = $wordpress['posts_ids']['fabrics'];
        $this->posts = $wordpress['fabric'];
        $this->postmeta = $wordpress['postmeta'];
        $this->terms = $wordpress['terms'];
        $this->termmeta = $wordpress['termmeta'];
        $this->attachments = $wordpress['attachments'];
        $this->posts_by_post_name = $wordpress['posts_by_post_name'];
        $this->term_taxonomy = $wordpress['term_taxonomy'];
    }

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get() {

        foreach ($this->ids_fabrics as $id) {
            $posts = $this->posts[$id];
            $postmeta = $this->postmeta[$id];

            $this->set_id($id);
            $this->set_key_posts('post_title', $id, $posts, 'name', 'Товар');
            $this->set_key_posts('post_name', $id, $posts, 'slug', 'Товар');
            $this->set_key_posts('hide', $id, $postmeta, 'hide', 'Товар');
            $this->set_collection('collection', $id, $postmeta, $this->terms, $this->termmeta, $this->attachments, 'collection', 'Ткань');
            $this->set_color('color', $id, $postmeta, $this->terms, $this->termmeta, 'color', 'Ткань');
            $this->set_image('image', $id, $postmeta, $this->attachments, 'image', 'Ткань');
            $this->set_gallery('gallery', $id, $postmeta, $this->attachments, 'gallery', 'Ткань');
            $this->set_video('video', $id, $postmeta, $this->attachments, 'video','Ткань');
            $this->set_category($id, $postmeta, $this->termmeta);
            $this->set_properties($id, $this->posts_by_post_name, $postmeta, $this->termmeta);
            $this->set_description($id, $postmeta, $this->term_taxonomy);
            $this->set_material($id, $this->posts_by_post_name, $postmeta, $this->termmeta);
        }

        $this->set_log($this->log);

//        var_dump('fabric--------', $this->data);

        return $this->data;
    }

    private function get_key_posts($post, $search_key) {
        return $post[$search_key];
    }

    /**
     * @param $search_key
     * @param $id
     * @param $postmeta
     * @param $terms
     * @param $termmeta
     * @param $attachments
     * @param $setting_key
     * @param $type_product
     * @return string
     * Получаем коллекцию ткани и формируем тело в JSON
     * смена размера изображений возможна - но осторожно
     */
    private function get_collection($search_key, $id, $postmeta, $terms, $termmeta, $attachments, $setting_key, $type_product) {
        $data = [];

        $data['id'] = $postmeta[$search_key];
        $data['slug'] = $terms[$postmeta[$search_key]]['slug'];
        $data['name'] = $terms[$postmeta[$search_key]]['name'];

        if (isset($termmeta[$postmeta['collection']]['gallery']) && !empty($termmeta[$postmeta['collection']]['gallery'])) {
            $gallery = array();

            foreach (unserialize($termmeta[$postmeta['collection']]['gallery']) as $value) {
                $gallery[] = [
                    'original' => $attachments[$value]['original'],
                    'w150' => $attachments[$value]['w150'],
                ];
            }

            $data['gallery'] = serialize($gallery);
        } else {
            $data['gallery'] = 0;

            $this->log[] = array('Коллекция (ткань)', $id, 'gallery', 'EMPTY');
        }

        if (isset($termmeta[$postmeta['collection']]['care-advice']) && !empty($termmeta[$postmeta['collection']]['care-advice'])) {
            $care_advice = [];
            $care_advice_images = [];

            $care_advice_images[] = $attachments[$termmeta[$postmeta['collection']]['care-advice']];

            foreach ($care_advice_images as $size_image) {
                $care_advice['original'] = $size_image['original'];
            }

            $data['care_advice'] = serialize($care_advice);

        } else {
            $data['care_advice'] = 0;

            $this->log[] = array('Коллекция (ткань)', $id, 'care_advice', 'EMPTY');
        }

        return serialize($data);
    }

    private function get_color($postmeta, $terms, $termmeta, $search_key) {
        $data = [];

        $data['id'] = $postmeta[$search_key];
        $data['slug'] = $terms[$postmeta[$search_key]]['slug'];
        $data['name'] = $terms[$postmeta[$search_key]]['name'];
        $data['hex'] = $termmeta[$postmeta[$search_key]]['color-hex-code'];

        return serialize($data);
    }

    private function get_image($attachments, $postmeta, $search_key) {
        return serialize($attachments[$postmeta[$search_key]]);
    }

    private function get_gallery($attachments, $postmeta, $search_key) {
        $data = [];

        foreach (unserialize($postmeta[$search_key]) as $value) {
            $data[] = serialize([
                'original' => $attachments[$value]['original'],
                'w500' => $attachments[$value]['w500'],
                'w300' => $attachments[$value]['w300'],
                'w100' => $attachments[$value]['w100'],
            ]);
        }

        return serialize($data);
    }

    private function get_video($attachments, $postmeta, $search_key) {
        return $attachments[$postmeta[$search_key]]['url'];
    }

    private function set_id($id) {
        $this->data[$id]['id'] = (int) $id;
    }

    /**
     * @param $search_key
     * @param $id
     * @param $post
     * @param $setting_key
     * @param $type_product
     * Установка ключа по таблице wp_posts
     * $search_key - имя поля в wp_posts
     * $setting_key - имя, которое улетает в таблицу fabrics
     * $type_product имя для логирования
     */
    private function set_key_posts($search_key, $id, $post, $setting_key, $type_product) {
        if ($this->has_key($search_key, $id, $post, $setting_key, $type_product)) {
            $this->data[$id][$setting_key] = $this->get_key_posts($post, $search_key);
        }
    }

    /**
     * @param $search_key
     * @param $id
     * @param $postmeta
     * @param $terms
     * @param $termmeta
     * @param $attachments
     * @param $setting_key
     * @param $type_product
     * Установка коллекции для ткани
     * $search_key - имя поля в wp_posts
     * $setting_key - имя, которое улетает в таблицу fabrics
     * $type_product имя для логирования
     */
    private function set_collection($search_key, $id, $postmeta, $terms, $termmeta, $attachments, $setting_key, $type_product) {
        if ($this->has_meta_key($search_key, $id, $postmeta, $setting_key, $type_product)) {
            $type_product = 'Коллекция (ткань)';
            $this->data[$id][$setting_key] = $this->get_collection($search_key, $id, $postmeta, $terms, $termmeta, $attachments, $setting_key, $type_product);
        }
    }

    /**
     * @param $search_key
     * @param $id
     * @param $postmeta
     * @param $terms
     * @param $setting_key
     * @param $type_product
     * Установка цвета для ткани
     * $search_key - имя поля в wp_postmeta
     * $setting_key - имя, которое улетает в таблицу fabrics
     * $type_product имя для логирования
     */
    private function set_color($search_key, $id, $postmeta, $terms, $termmeta, $setting_key, $type_product) {
        if ($this->has_meta_key($search_key, $id, $postmeta, $setting_key, $type_product)) {
            $this->data[$id][$setting_key] = $this->get_color($postmeta, $terms, $termmeta, $search_key);
        }
    }

    private function set_image($search_key, $id, $postmeta, $attachments, $setting_key, $type_product) {
        if ($this->has_meta_key($search_key, $id, $postmeta, $setting_key, $type_product)) {
            $this->data[$id][$setting_key] = $this->get_image($attachments, $postmeta, $search_key);
        }
    }

    private function set_gallery($search_key, $id, $postmeta, $attachments, $setting_key, $type_product) {
        if ($this->has_meta_key($search_key, $id, $postmeta, $setting_key, $type_product)) {
            $this->data[$id][$setting_key] = $this->get_gallery($attachments, $postmeta, $search_key);
        }
    }

    private function set_video($search_key, $id, $postmeta, $attachments, $setting_key, $type_product) {
        if ($this->has_meta_key($search_key, $id, $postmeta, $setting_key, $type_product)) {
            $this->data[$id][$setting_key] = $this->get_video($attachments, $postmeta, $search_key);
        }
    }

    private function set_category($id, $postmeta, $termmeta) {
        if (isset($postmeta['collection'])) {
            if (!isset($termmeta[$postmeta['collection']]['category'])) {
                $this->data[$id]['category'] = 0;

                $this->log[] = array('Ткань', $id, 'category', 'EMPTY');
            } else {
                $this->data[$id]['category'] = $termmeta[$postmeta['collection']]['category'];
            }
        } else {
            $this->data[$id]['category'] = 0;

            $this->log[] = array('Ткань', $id, 'collection', 'NOT_EXIST');
        }
    }

    private function set_properties($id, $posts_by_post_name, $postmeta, $termmeta) {
        $choices = unserialize($posts_by_post_name[$termmeta[$postmeta['collection']]['_properties']]['post_content'])['choices'];

        if (isset($termmeta[$postmeta['collection']]['properties'])) {
            if (empty($termmeta[$postmeta['collection']]['properties'])) {
                $this->data[$id]['properties'] = 0;

                $this->log[] = array('Коллекция (ткань)', $id, 'properties', 'EMPTY');
            } else {
                $data = array();

                foreach (unserialize($termmeta[$postmeta['collection']]['properties']) as $property) {
                    $data[] = array(
                        'slug' => $property,
                        'name' => $choices[$property]
                    );
                }

                $this->data[$id]['properties'] = serialize($data);
            }
        } else {
            $this->data[$id]['properties'] = 0;

            $this->log[] = array('Коллекция (ткань)', $id, 'properties', 'NOT_EXIST');
        }
    }

    private function set_description($id, $postmeta, $term_taxonomy) {
        if (isset($term_taxonomy[$postmeta['collection']]['description'])) {
            if (empty($term_taxonomy[$postmeta['collection']]['description'])) {
                $this->data[$id]['description'] = 0;

                $this->log[] = array('Коллекция (ткань)', $id, 'description', 'EMPTY');
            } else {
                $this->data[$id]['description'] = $term_taxonomy[$postmeta['collection']]['description'];
            }
        } else {
            $this->data[$id]['description'] = 0;

            $this->log[] = array('Коллекция (ткань)', $id, 'description', 'NOT_EXIST');
        }
    }

    private function set_material($id, $posts_by_post_name, $postmeta, $termmeta) {
        $choices = unserialize($posts_by_post_name[$termmeta[$postmeta['collection']]['_material']]['post_content'])['choices'];

        if (isset($termmeta[$postmeta['collection']]['material'])) {
            if (empty($termmeta[$postmeta['collection']]['material'])) {
                $this->data[$id]['material'] = 0;

                $this->log[] = array('Коллекция (ткань)', $id, 'material', 'EMPTY');
            } else {
                $this->data[$id]['material'] = serialize(array(
                    'slug' => $termmeta[$postmeta['collection']]['material'],
                    'name' => $choices[$termmeta[$postmeta['collection']]['material']]
                ));
            }
        } else {
            $this->data[$id]['material'] = 0;

            $this->log[] = array('Коллекция (ткань)', $id, 'material', 'NOT_EXIST');
        }
    }

    /**
     * @param $search_key
     * @param $id
     * @param $post
     * @param $setting_key
     * Проверка наличия ключа в wp_posts и запись в лог
     */
    private function has_key($search_key, $id, $post, $setting_key, $type_product) {
        if (isset($post[$search_key])) {
            if (!empty($post[$search_key])) {
                return true;
            } else {
                $this->data[$id][$setting_key] = 0;

                $this->log[] = array($type_product, $id, $search_key, 'EMPTY');

                return false;
            }
        } else {
            $this->log[] = array($type_product, $id, $search_key, 'NOT_EXIST');

            return false;
        }
    }

    /**
     * @param $search_key
     * @param $id
     * @param $postmeta
     * @param $setting_key
     * @param $type_product
     * @return bool
     * Проверка наличия ключа в wp_postmeta и запись в лог
     */
    private function has_meta_key($search_key, $id, $postmeta, $setting_key, $type_product) {
        if (isset($postmeta[$search_key])) {
            if (empty($postmeta[$search_key])) {
                $this->data[$id][$search_key] = 0;

                $this->log[] = array($type_product, $id, $search_key, 'EMPTY');

                return false;
            } else {
                return true;
            }
        } else {
            $this->data[$id][$search_key] = 0;

            $this->log[] = array($type_product, $id, $search_key, 'NOT_EXIST');

            return false;
        }
    }

    /**
     * @param $search_key
     * @param $id
     * @param $postmeta
     * @param $termmeta
     * @param $type_product
     * @return bool
     * Проверка наличия ключа в termmeta для collection
     */
    private function has_termmeta($search_key, $id, $postmeta, $termmeta, $type_product) {

        if (isset($postmeta['collection'])) {
            if (empty($termmeta[$postmeta['collection']][$search_key])) {
                $this->data[$id][$search_key] = 0;

                $this->log[] = array($type_product, $id, $search_key, 'EMPTY');

                return false;
            } else {
                return true;
            }
        } else {
            $this->data[$id][$search_key] = 0;

            $this->log[] = array($type_product, $id, 'collection', 'NOT_EXIST');

            return false;
        }
    }
}
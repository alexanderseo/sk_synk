<?php


trait variations_helpers {

    public function get_id($post) {
        return $post['ID'];
    }

    /**
     * @param $postmeta
     * @return mixed|string
     * Получаем _regular_price из postmeta для определенного товара
     */
    public function get_price($postmeta) {
        return isset($postmeta['_regular_price']) ? $postmeta['_regular_price'] : "";
    }

    /**
     * @param $postmeta
     * @return mixed|string
     * Получаем _sale_price из postmeta для определенного товара
     */
    public function get_sale_price($postmeta) {
        return isset($postmeta['_sale_price']) ? $postmeta['_sale_price'] : "";
    }

    /**
     * @param $postmeta
     * @return mixed|string
     * Получаем _product_discount из postmeta для определенного товара
     */
    public function get_discount($postmeta) {
        return isset($postmeta['_product_discount']) ? $postmeta['_product_discount'] : "";
    }

    /**
     * @param $postmeta
     * @return mixed|string
     * Получаем _sku из postmeta для определенного товара
     */
    public function get_sku($postmeta) {
        return isset($postmeta['_sku']) ? $postmeta['_sku'] : "";
    }

    private function get_thumbnail($postmeta, $attachments) {
        $data = [];

        if (isset($postmeta['_thumbnail_id'])) {
            if (isset($attachments[$postmeta['_thumbnail_id']])) {
                $data['original'] = $attachments[$postmeta['_thumbnail_id']]['original'];
                $data['w500'] = $attachments[$postmeta['_thumbnail_id']]['w500'];
                $data['w300'] = $attachments[$postmeta['_thumbnail_id']]['w300'];
            }
        }

        return serialize($data);
    }

    private function get_gallery($postmeta, $attachments) {
        $attachments_ids = isset($postmeta['_product_additional_photos']) ? $postmeta['_product_additional_photos'] : "";
        $data = [];

        if (empty($attachments_ids)) {
            return serialize($data);
        }

        foreach (explode(',', $attachments_ids) as $id) {
            if (isset($attachments[$id])) {
                $data[] = serialize([
                    'original' => $attachments[$id]['original'],
                    'w500' => $attachments[$id]['w500'],
                    'w300' => $attachments[$id]['w300'],
                ]);
            }
        }

        return serialize($data);
    }

    private function get_drawing($postmeta, $attachments) {
        $drawing_id = isset($postmeta['_product_drawing']) ? $postmeta['_product_drawing'] : "";
        $data = [];

        if (empty($drawing_id)) {
            return serialize($data);
        }

        if (!isset($attachments[$drawing_id])) {
            return serialize($data);
        }

        foreach ($attachments[$drawing_id] as $size => $url) {
            if ($size == 'original') {
                $data[$size] = $url;
            }
        }

        return serialize($data);
    }

    public function get_attributes_variations($fabrics, $postmeta, $posts_by_post_name, $terms_by_slug, $attribute_taxonomies, $terms, $all_postmeta) {
        $data = [];

        foreach ($this->get_attributes_keys($postmeta) as $key) {
            if (isset($attribute_taxonomies[str_replace('attribute_pa_', '', $key)]) && isset($terms_by_slug[$postmeta[$key]]['name'])) {
                $data[$key]['taxonomy_slug'] = str_replace('attribute_', '', $key);
                $data[$key]['taxonomy_name'] = $attribute_taxonomies[str_replace('attribute_pa_', '', $key)];
                $data[$key]['term_slug'] = $postmeta[$key];
                $data[$key]['term_name'] = $terms_by_slug[$postmeta[$key]]['name'];

                if ($key == 'attribute_pa_fabric') {
                    $data[$key]['details'] = $this->get_attributes_fabric_details($key, $posts_by_post_name, $postmeta, $terms, $fabrics, $all_postmeta);
                }
            }
        }

        return serialize(array_values($data));
    }

    private function get_attributes_keys($postmeta) {
        return preg_grep('/^attribute_/', array_keys($postmeta));
    }

    private function get_attributes_fabric_details($key, $posts, $postmeta, $terms, $fabrics, $all_postmeta) {
        $data = [];

        if (isset($posts[$postmeta[$key]])) {
            $fabric = $fabrics[$posts[$postmeta[$key]]['ID']];
            $fabric_images = [];

            $data['id'] = $fabric['id'];
            $data['name'] = $fabric['name'];

            if ($fabric['collection'] === 0) {
                $data['collection'] = 0;
            } else {

                $data['collection']['id'] = unserialize($fabric['collection'])['id'];
                $data['collection']['slug'] = unserialize($fabric['collection'])['slug'];
                $data['collection']['name'] = unserialize($fabric['collection'])['name'];
//                $data['collection']['gallery'] = $this->attributes_fabric_details_size_gallery(unserialize($fabric['collection'])['gallery']);

            }

            $data['category'] = array(
                'name' => (int)substr($fabric['category'], 0, 1) + 1,
                'rate' => (int)$fabric['category']
            );

            $postmeta = $all_postmeta[$fabric['id']];

            $data['image'] = $this->attributes_fabric_detail_size_image($fabric['image']);
            $data['gallery'] = $this->attributes_fabric_details_size_gallery($fabric['gallery']);
            $data['color']['id'] = $terms[$postmeta['color']]['term_id'];
            $data['color']['slug'] = $terms[$postmeta['color']]['slug'];
            $data['color']['name'] = $terms[$postmeta['color']]['name'];
        }

        return $data;
    }

    private function attributes_fabric_detail_size_image($string_image) {
        $data = [];

        if (!empty($string_image)) {
            foreach (unserialize($string_image) as $size => $url) {
                if ($size == 'w100') {
                    $data[$size] = $url;
                }
            }
        }

        return $data;
    }

    private function attributes_fabric_details_size_gallery($string_gallery) {
        $data = [];

        if (!empty($string_gallery)) {
            foreach (self::_unserialize_recursive($string_gallery) as $item) {
                foreach ($item as $size => $url) {
                    if ($size == 'w100') {
                        $data[][$size] = $url;
                    }
                }
            }
        }

        return $data;
    }

    static function _unserialize_recursive($val) {
        //$pattern = "/.*\{(.*)\}/";
        if(self::_is_serialized($val)){
            $val = trim($val);
            $ret = unserialize($val);
            if (is_array($ret)) {
                foreach($ret as &$r) $r = self::_unserialize_recursive($r);
            }
            return $ret;
        } elseif (is_array($val)) {
            foreach($val as &$r) $r = self::_unserialize_recursive($r);
            return $val;
        } else { return $val; }
    }

    static function _is_serialized($val) {
        if (!is_string($val)) return false;
        if (trim($val) == "") return false;
        $val = trim($val);
        if (preg_match('/^(i|s|a|o|d):.*{/si', $val) > 0) return true;
        return false;
    }
}
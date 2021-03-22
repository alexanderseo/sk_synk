<?php


trait default_attributes {

    public function get_default_attributes($postmeta, $woocommerce_attribute_taxonomies, $terms_by_slug) {
        $data = [];

        if (!isset($postmeta['_default_attributes'])) {
            return serialize($data);
        }

        $default_attributes = unserialize($postmeta['_default_attributes']);
        if (empty($default_attributes)) {
            return serialize($data);
        }

        foreach ($default_attributes as $key => $value) {

            if (isset($woocommerce_attribute_taxonomies[str_replace('pa_', '', $key)]) && isset($terms_by_slug[$value]['name'])) {
                $data[$key]['taxonomy_slug'] = $key;
                $data[$key]['taxonomy_name'] = $woocommerce_attribute_taxonomies[str_replace('pa_', '', $key)];
                $data[$key]['term_slug'] = $value;
                $data[$key]['term_name'] = $terms_by_slug[$value]['name'];
            }
        }

        return serialize(array_values($data));
    }
}
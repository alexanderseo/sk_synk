<?php


trait variable_attributes {

    private function get_variable_attributes($terms_ids, $attributes, $fabrics, $taxonomies, $terms, $woocommerce_attribute_taxonomies, $postmeta_all, $termmeta, $materials) {
        $data = [];
        $taxonomy_options = [];

        foreach ($terms_ids as $term_id) {
//            $taxonomy = $taxonomies[$term_id['term_taxonomy_id']]['taxonomy'];
            $taxonomy = $this->get_taxonomy($term_id, $taxonomies);

            if (isset($attributes['variable'][$taxonomy])) {
//                $term = $terms[$taxonomies[$term_id['term_taxonomy_id']]['term_id']];
                $term = $this->get_term($term_id, $taxonomies, $terms);


//                $taxonomy_options[$taxonomy][$term['slug']]['taxonomy_slug'] = $taxonomy;
//                $taxonomy_options[$taxonomy][$term['slug']]['taxonomy_name'] = $wordpress['woocommerce_attribute_taxonomies'][str_replace('pa_', '', $taxonomy)];
                $taxonomy_options[$taxonomy][$term['slug']]['term_id'] = $term['term_id'];
                $taxonomy_options[$taxonomy][$term['slug']]['term_slug'] = $term['slug'];
                $taxonomy_options[$taxonomy][$term['slug']]['term_name'] = $term['name'];

                if ($taxonomy == 'pa_fabric') {
                    foreach ($fabrics as $fabric) {
                        if ($term['slug'] == $fabric['slug']) {
                            $taxonomy_options[$taxonomy][$term['slug']]['color']['slug'] = $terms[$postmeta_all[$fabric['id']]['color']]['slug'];
                            $taxonomy_options[$taxonomy][$term['slug']]['color']['hex'] = $termmeta[$postmeta_all[$fabric['id']]['color']]['color-hex-code'];

                            break;
                        }
                    }
                }

                if ($taxonomy == 'pa_material') {

                    $taxonomy_options[$taxonomy][$term['slug']]['image'] = unserialize($materials[$term['term_id']]['material_image']);
                }

                $taxonomy_id = $taxonomies[$term_id['term_taxonomy_id']]['term_taxonomy_id'];

                $data[$taxonomy]['taxonomy_id'] = $taxonomy_id;
                $data[$taxonomy]['taxonomy_slug'] = $taxonomy;
                $data[$taxonomy]['taxonomy_name'] = $woocommerce_attribute_taxonomies[str_replace('pa_', '', $taxonomy)];
                $data[$taxonomy]['taxonomy_options'] = array_values($taxonomy_options[$taxonomy]);
            }
        }

        return serialize(array_values($data));
//        return array_values($data);
    }

    private function get_taxonomy($term_id, $taxonomies) {
        return $taxonomies[$term_id['term_taxonomy_id']]['taxonomy'];
    }

    private function get_term($term_id, $taxonomies, $terms) {
        return $terms[$taxonomies[$term_id['term_taxonomy_id']]['term_id']];
    }
}
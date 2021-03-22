<?php


trait static_attributes {

    public function get_static_attributes($terms_ids, $attributes, $taxonomies, $terms, $woocommerce_attribute_taxonomies) {
        $data = [];

        foreach ($terms_ids as $term_id) {
            $taxonomy = $taxonomies[$term_id['term_taxonomy_id']]['taxonomy'];

            if (isset($attributes['static'][$taxonomy])) {
                $term = $terms[$taxonomies[$term_id['term_taxonomy_id']]['term_id']];
                $taxonomy_id = $taxonomies[$term_id['term_taxonomy_id']]['term_taxonomy_id'];

                $data[$taxonomy]['taxonomy_slug'] = $taxonomy;
                $data[$taxonomy]['taxonomy_name'] = $woocommerce_attribute_taxonomies[str_replace('pa_', '', $taxonomy)];
                $data[$taxonomy]['term_slug'] = $term['slug'];
                $data[$taxonomy]['term_name'] = $term['name'];
            }
        }

        return serialize(array_values($data));
    }
}
<?php


trait static_attributes {

    public function get_static_attributes_old($terms_ids, $attributes, $taxonomies, $terms, $woocommerce_attribute_taxonomies) {
        $data = [];

        foreach ($terms_ids as $term_id) {
            $taxonomy = $taxonomies[$term_id['term_taxonomy_id']]['taxonomy'];;

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

    public function get_static_attributes($terms_ids, $attributes, $taxonomies, $terms, $woocommerce_attribute_taxonomies) {
        $data = [];
        $taxonomy_options = [];

        foreach ($terms_ids as $term_id) {
            $taxonomy = $this->get_taxonomy_s($term_id, $taxonomies);

            if (isset($attributes['static'][$taxonomy])) {
                $term = $this->get_term_s($term_id, $taxonomies, $terms);

                $taxonomy_options[$taxonomy][$term['slug']]['term_id'] = $term['term_id'];
                $taxonomy_options[$taxonomy][$term['slug']]['term_slug'] = $term['slug'];
                $taxonomy_options[$taxonomy][$term['slug']]['term_name'] = $term['name'];


                $taxonomy_id = $taxonomies[$term_id['term_taxonomy_id']]['term_taxonomy_id'];

                $data[$taxonomy]['taxonomy_id'] = $taxonomy_id;
                $data[$taxonomy]['taxonomy_slug'] = $taxonomy;
                $data[$taxonomy]['taxonomy_name'] = $woocommerce_attribute_taxonomies[str_replace('pa_', '', $taxonomy)];
                $data[$taxonomy]['taxonomy_options'] = array_values($taxonomy_options[$taxonomy]);
            }
        }

        return serialize(array_values($data));
    }

    private function get_taxonomy_s($term_id, $taxonomies) {
        return $taxonomies[$term_id['term_taxonomy_id']]['taxonomy'];
    }

    private function get_term_s($term_id, $taxonomies, $terms) {
        return $terms[$taxonomies[$term_id['term_taxonomy_id']]['term_id']];
    }
}
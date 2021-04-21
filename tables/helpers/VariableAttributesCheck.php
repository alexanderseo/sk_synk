<?php


class VariableAttributesCheck {

    private $data;

    public function __construct() {

        $this->data = [];

    }

    public function complect_attributes($id, $variations) {
        $variations_array = [];

        foreach ($variations as $key => $variation) {
            if (isset($variation['parent_id'])) {
                if ($variation['parent_id'] == $id) {
                    $variations_array[] = $variation;
                }
            }
        }

        $not_union_attributes = $this->get_actuality_attributes($variations_array);

        $group_attributes = $this->group_attributes($not_union_attributes);

        return $group_attributes;
    }

    private function get_actuality_attributes($variations_array) {
        $data = [];

        foreach ($variations_array as $key => $value) {
            if (isset($value['attributes'])) {
                $data[] = $this->select_attributes($value['attributes']);
            }
        }

        return $data;
    }

    private function select_attributes($attributes) {
        $select_array = [];

        foreach(unserialize($attributes) as $item) {
            if (isset($item['taxonomy_slug'])) {
                $select_array[$item['taxonomy_slug']]['term_slug'] = $item['term_slug'];
            }
        }

        return $select_array;
    }

    private function group_attributes($attributes) {
        $data = [];
        $result = [];

        foreach ($attributes as $key => $item) {
            foreach ($item as $name_key => $value) {
                $data[$name_key][] = $value;

                $result[$name_key] = array_unique($data[$name_key], SORT_REGULAR);
            }
        }

        return $result;
    }



}
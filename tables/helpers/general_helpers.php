<?php


trait general_helpers {

    /**
     * @param $id
     * @param $products
     * @return mixed
     * Назначаем статичный массив из $wordpress['posts'], который совпал по переданному id
     * Этот массив уже используется для поиска необходимых ключей данного товара
     */
    public function set_products_array_by_id($id, $products) {
        return $products[$id];
    }

    public function set_relashions_array_by_id($id, $relashionships) {
        if (array_key_exists($id, $relashionships)) {
            return $relashionships[$id];
        }
    }

    public function set_postmeta_array_by_id($id, $postmeta) {
        return $postmeta[$id];
    }

    /**
     * @param $id
     * @return array|mixed
     * Получаем id категории
     * Изначально отбирается дочерняя категория. Если у товара нет дочерней категории, тогда берется только родительская.
     */
    public function get_category_id($id, $relationships, $taxonomy) {
        $array = [];

        foreach ($relationships as $relationship) {
            if ($taxonomy[$relationship['term_taxonomy_id']]['taxonomy'] == 'product_cat') {
                $array[] = $taxonomy[$relationship['term_taxonomy_id']];
            }
        }

        $category_id = [];

        $tmp_array = [];

        foreach ($array as $key_array => $value_array) {
            $tmp_array[$key_array]['term_id'] = $value_array['term_id'];
            $tmp_array[$key_array]['parent'] = $value_array['parent'];
        }


        if (count($tmp_array) == 1) {
            foreach ($tmp_array as $item_array) {
                $category_id = $item_array['term_id'];
            }
        } else {
            foreach ($tmp_array as $item) {
                if ($item['parent'] !== '0') {
                    $category_id = $item['term_id'];
                }
            }
        }

        return $category_id;
    }

    public function get_category($category_id, $categories) {
        if (!empty($category_id)) {
            if (isset($categories[$category_id])) {
                return $categories[$category_id];
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    /**
     * @param $array
     * @return string
     * Очищает массив от дублей и пустых значений и превращает в строку (значения через запятую)
     */
    public function unique_array_to_string($array) {

        $data_out = array_unique($array);
        $ids_array = array_diff($data_out, array("null", ""));
        $data_string = implode(',', $ids_array);

        return $data_string;
    }

    /**
     * @param $id
     * @param $terms
     * @return array
     * Получаем данные о коллекции по ее id
     */
    public function get_collection_by_id($id, $terms) {
        $array = [];

        if (isset($terms[$id])) {
            $array = [
                'term_id' => $terms[$id]['term_id'],
                'name' => $terms[$id]['name'],
                'slug' => $terms[$id]['slug']
            ];
        }

        return $array;
    }
}
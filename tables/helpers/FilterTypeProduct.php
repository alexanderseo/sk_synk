<?php


class FilterTypeProduct {
    private $relaishionships;
    private $taxonomy;
    private $terms;

    public function __construct($relaishionships, $taxonomy, $terms) {
        $this->relaishionships = $relaishionships;
        $this->taxonomy = $taxonomy;
        $this->terms = $terms;
    }

    /**
     * @param $id
     * @param $relaishionships
     * @param $taxonomy
     * @param $terms
     * @return bool
     * Фильтруем товары с типом simple
     * Они должны потом исчезнуть
     */
    public function filter_simple_product($id) {

        if (isset($this->relaishionships[$id])) {
            foreach ($this->relaishionships[$id] as $relationship) {
                if ($this->taxonomy[$relationship['term_taxonomy_id']]['taxonomy'] == 'product_type') {
                    if ($this->terms[$relationship['term_taxonomy_id']]['slug'] == 'simple') {
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        } else {
            return false;
        }



    }

    /**
     * @param $id
     * @param $relaishionships
     * @param $taxonomy
     * @param $terms
     * @return bool
     * Фильтруем стоковые товары
     */
    public function filter_stock_product($id) {
        $status = true;

        foreach ($this->relaishionships[$id] as $relationship) {
            if ($this->taxonomy[$relationship['term_taxonomy_id']]['taxonomy'] == 'product_cat') {
                if ($this->terms[$relationship['term_taxonomy_id']]['term_id'] == "7681") {
                    $status = false;

                    break;
                }
            }
        }

        return $status;
    }

    public function filter_expo_product($id) {
        $status = true;

        foreach ($this->relaishionships[$id] as $relationship) {
            if ($this->taxonomy[$relationship['term_taxonomy_id']]['taxonomy'] == 'product_cat') {
                if ($this->terms[$relationship['term_taxonomy_id']]['term_id'] == "7986") {
                    $status = false;

                    break;
                }
            }
        }

        return $status;
    }
}
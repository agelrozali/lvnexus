<?php

namespace UkrSolution\BarcodesDigital;

class Dimensions
{
    public function get($isAjax = true)
    {
        Request::ajaxRequestAccess();
        global $wpdb;

        $tableDimension = $wpdb->prefix . Database::$tableDimension;

        $dimensions = $wpdb->get_results("SELECT * FROM `{$tableDimension}`", ARRAY_A);

        if ($isAjax) {
            uswbg_a4bJsonResponse(array("dimensions" => $dimensions));
        } else {
            return $dimensions;
        }
    }
    public function getActive()
    {
        global $wpdb;

        $tableDimension = Database::$tableDimension;

        $dimension = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}{$tableDimension} WHERE `is_default` = 1;");

        $id = 3; 


        return $id;
    }
}

<?php
namespace App;

class Dates {
    public static function getTodaysDate(){
        return $date = date('Y-m-d');
    }

    public static function getMinDate(){
        $dummy = date('Y-m-d');
        return $date = date('Y-m-d', strtotime("-6 months", strtotime($dummy)));
    }

    public static function getMaxDate(){
        $dummy = date('Y-m-d');
        return $date = date('Y-m-d', strtotime("+6 months", strtotime($dummy)));
    }
}
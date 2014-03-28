<?php
/**
 * Created by PhpStorm.
 * User: stanislas
 * Date: 27/03/14
 * Time: 13:58
 */

class Tools {




    static function getDropDown($name , $id , $field = array() , $value = null , $onChange = null){

        echo "<SELECT name = ".$name." id=".$id."  onChange=".$onChange.">";
        for ($i = 0; $i <= count($field)-1; $i++) {
            if($value == $field[$i]) echo "<OPTION  id=".$i."  selected='selected' >".$field[$i]."</OPTION>";
            else echo "<OPTION id=".$i.">".$field[$i]."</OPTION>";
        }
        echo "</SELECT>";

    }



}
<?php


class MSGController {

    function sendMessage($action, $itemArray, $code) {
        switch($action){
            case "addToCart":
                $date = date('d-m-y h:i:s');
                echo $date." User added ".$itemArray[$code]["qty"]." units of ".$itemArray[$code]["name"]." to cart.";
                break;
            case "removeFromCart":
                $date = date('d-m-y h:i:s');
                echo $date." User removed ".$itemArray["qty"]." units of ".$itemArray["name"]." from cart.";
                break;
        }
    }

    function sendOtherMessage($action) {
        switch($action){
            case "empty":
                $date = date('d-m-y h:i:s');
                echo $date." User emptied whole cart.";
                break;
        }
    }
}
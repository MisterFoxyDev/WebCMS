<?php

function tokenRandomString($len = 20)
{
    $str = "0123456789abcdefghijklmnopqrstuvwxyz";
    $token = "";
    for ($i = 0; $i < $len; $i++) {
        $token .= $str[rand(0, strlen($str) - 1)];
    }
    return $token;
}

$token = tokenRandomString(25);
$imgToken = tokenRandomString(6);
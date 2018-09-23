<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//$phnumber = '+13107389088';
$phnumber = '0951709276';

//$regex = '/(0|\+?\d{2})(\d{7,9})/';


$regex = '/((0|\+?1)(\d{10}))|((0|\+?380)(\d{9}))/';
preg_match($regex, $phnumber, $matches);

print_r($matches);

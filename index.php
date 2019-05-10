<?php
/**
 * Created by PhpStorm.
 * User: Rafal Feliga
 * Date: 09/05/2019
 * Time: 07:18
 */

    require "App\Hoover.php";


    $hoover= new \App\Hoover();

    $hoover->read_input_json('./Input/input.json');

    $hoover->read_moves();

    $hoover->json_payload_POST('localhost\yoti_hoove\index.php');

    var_dump($hoover->json_output);
?>



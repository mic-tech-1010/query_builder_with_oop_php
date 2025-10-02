<?php

require "config.php";
require "autoloader.php";

$QB = new QueryBuilder;

// $result  = $QB->table('user')
//               ->select('email', 'id')
//               ->where("id", "=", 1)
//               ->limit(10)
//               ->getAll();

//echo $QB->table('user')->where("id", "=", 7)->delete();

// echo $QB->table('user')->insert([
//     "username" => "fade",
//     "email" => "fade@gmail.com",
//     "password" => "fade00",
//     "gender" => "male",
//     "date" => date("Y-m-d H:i:s")
// ]);

echo "<pre>";
print_r($QB->raw("update user set username = 'ayomipo1' where id = 2"));
//print_r($result);
<?php

//     include('config.php');

//     $mysqli = mysqli_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

// // 	$mysqli = mysqli_connect('localhost', 'etranzde_garteopencart', 'Berlin_2022!!', 'etranzde_gartendek_opencart');

// 	$mysqli -> set_charset("utf8");

// 	$lang_id_ru = '2';

// 	$lang_id_ro = '3';

//     $orders="SELECT * FROM `oc_order` LIMIT 1";

//     $result=mysqli_query($mysqli,$orders);

//     $result_array=[];

//     while($result_row=mysqli_fetch_assoc($result)){

//         $result_array[]=$result_row;

//     }

//     echo "<pre>";

//     print_r($result_array);

//     echo "</pre>";







$ch=curl_init();

$headers = array(

    'Content-Type:application/json',

    'Authorization: Basic '. base64_encode("Alex_fiverr:6dfab82de40a715dc20930481e"),

    'X-Billbee-Api-Key: 4A6868BB-13D2-4D70-918E-B499412C0528'

);

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

curl_setopt($ch, CURLOPT_HEADER, 0);

// curl_setopt($ch, CURLOPT_USERPWD, "Alex_fiverr:6dfab82de40a715dc20930481e" );

curl_setopt($ch, CURLOPT_URL, 'https://api.billbee.io/api/v1/orders');

// curl_setopt($ch, CURLOPT_POST, 1);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$server_output = curl_exec($ch);

$output=json_decode($server_output,1);

echo "<pre>";

print_r($output['Data'][0]); 

echo "</pre>";

curl_close($ch);





?>

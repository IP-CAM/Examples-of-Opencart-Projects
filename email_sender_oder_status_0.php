<?php

//Including files for database connection.
    include('config.php');
//creating database connection
    $mysqli = mysqli_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
	$mysqli -> set_charset("utf8");
// Setting email addresses
    $to="smolenschialexan@gmail.com";
    $from="info@gtendek.de";
    $subject="New order with status 0";
//fetching orders with status=0.
    $orders_query="SELECT * FROM `oc_order` where order_status_id=0 AND emailed=0 LIMIT 1";
    $result=mysqli_query($mysqli,$orders_query);
    $orders=[];
    while($result_row=mysqli_fetch_assoc($result)){
        $orders[]=$result_row;
    }
    foreach($orders as $order)
    {
        $body="New order was found with status=0. Below are the details:<br>";
        foreach($order as $i=>$o)
        {
            $body.="<strong>".normalize($i)." :</strong> <span>".normalize($o)."</span><br>";
        }   
        $email=send_email($to,$from,$subject,$body);
        if($email)
        {
            $update_query="UPDATE `oc_order` set emailed=1 WHERE order_id={$order['order_id']}";
            mysqli_query($mysqli,$update_query);
        }
    }
    
    function send_email($to,$from,$subject,$body)
    {
        $header = "From:".$from." \r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html\r\n";
        $mail=mail($to,$subject,$body,$header);
        return $mail;
    }
    function normalize($text)
    {
        $Text=ucwords(str_replace("_"," ",$text));
        return $Text;
    }

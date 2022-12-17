<?php

//Including files for database connection and countries list for country code.
    include('config.php');include('countries.php'); include('billbee_config.php');
//creating database connection
    $mysqli = mysqli_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
	$mysqli -> set_charset("utf8");
//fetching orders which have not been sent to billbee.
    $orders_query="SELECT * FROM `oc_order` where billbee=0";
    $result=mysqli_query($mysqli,$orders_query);
    $orders=[];
    while($result_row=mysqli_fetch_assoc($result)){
        $orders[]=$result_row;
    }
    foreach($orders as $o)
    {
        //Fetching shipment info against each order.
        $shipment_query="SELECT * FROM `oc_order_shipment` WHERE order_id={$o['order_id']} LIMIT 1";
        $result_sh=mysqli_query($mysqli,$shipment_query);
        $shipments=[];
        while($result_rowsh=mysqli_fetch_assoc($result_sh)){
            $shipments[]=$result_rowsh;
        }
        $s=$shipments[0];
        //Fetching transaction id against each order.
        $transaction_query="SELECT * FROM `oc_order_history` WHERE order_id={$o['order_id']} LIMIT 1";
        $result_tr=mysqli_query($mysqli,$transaction_query);
        $transaction=[];
        while($result_rowtr=mysqli_fetch_assoc($result_tr)){
            $transaction[]=$result_rowtr;
        }
        $t=explode("Transaction ID:",$transaction[0]['comment']);if(!isset($t[1]))$t[1]=null;
        //Filling data in the provided billbee format.
        $order_data='{
          "RebateDifference": 0,
          "ShippingIds": [
            {
              "BillbeeId": 0,
              "ShippingId": "'.$s["order_shipment_id"].'",
              "Shipper": "N/A",
              "Created": "'.$s["date_added"].'",
              "TrackingUrl": "'.$s["tracking_number"].'",
              "ShippingProviderId": "'.$s["shipping_courier_id"].'",
              "ShippingProviderProductId": 0,
              "ShippingCarrier": 0,
              "ShipmentType": 0
            }
          ],
          "AcceptLossOfReturnRight": true,
          "Id": "'.$o["order_id"].'",
          "OrderNumber": "'.$o["order_id"].'",
          "State": 1,
          "VatMode": 0,
          "CreatedAt": "'.$o["date_added"].'",
          "ShippedAt": "",
          "ConfirmedAt": "",
          "PayedAt": "",
          "SellerComment": "'.$o["comment"].'",
          "Comments": [
          ],
          "InvoiceNumberPrefix": "",
          "InvoiceNumberPostfix": "",
          "InvoiceNumber": "",
          "InvoiceDate": "",
          "InvoiceAddress": { 
            "FirstName": "'.$o["payment_firstname"].'",
            "LastName": "'.$o["payment_lastname"].'",
            "Email": "'.$o["email"].'",
            "Phone": "'.$o["telephone"].'",
            "Company": "'.$o["payment_company"].'",
            "Zip": "'.$o["payment_postcode"].'",
            "City": "'.$o["payment_city"].'",
            "Country": "'.trim($o["payment_country"]).'",
            "State": "'.$o["payment_zone"].'",
            "Street": "'.$o["payment_address_1"].'"
          },
          "ShippingAddress": {
            "BillbeeId": 0,
            "FirstName": "'.$o["shipping_firstname"].'",
            "LastName": "'.$o["shipping_lastname"].'",
            "Company": "'.$o["shipping_company"].'",
            "NameAddition": "",
            "Street": "'.$o["shipping_address_1"].'",
            "HouseNumber": "",
            "Zip": "'.$o["shipping_postcode"].'",
            "City": "'.$o["shipping_city"].'",
            "CountryISO2": "",
            "Country": "'.trim($o["shipping_country"]).'",
            "Line2": "",
            "Email": "",
            "State": "'.$o["shipping_zone"].'",
            "Phone": ""
          },
          "PaymentMethod": 1,
          "TotalCost": "'.$o["total"].'",
          "AdjustmentCost": 0,
          "AdjustmentReason": "",
          "Currency": "'.$o["currency_code"].'",
          "Seller": {
            "Platform": "",
            "BillbeeShopName": "'.$o["store_name"].'",
            "BillbeeShopId": 0,
            "Id": "",
            "Nick": "",
            "FirstName": "",
            "LastName": "",
            "FullName": "",
            "Email": ""
          },
          "Buyer": {
            "Platform": "",
            "BillbeeShopName": "",
            "BillbeeShopId": 0,
            "Id": "'.$o["customer_id"].'",
            "Nick": "",
            "FirstName": "'.$o["firstname"].'",
            "LastName": "'.$o["lastname"].'",
            "FullName": "'.$o["firstname"]." ".$o["lastname"].'",
            "Email": "'.$o["email"].'"
          },
          "UpdatedAt": "",
          "TaxRate1": 0,
          "TaxRate2": 0,
          "BillBeeOrderId": 0,
          "BillBeeParentOrderId": 0,
          "VatId": "",
          "Tags": [
            ""
          ],
          "ShipWeightKg": 0,
          "LanguageCode": "de",
          "PaidAmount": 0,
          "ShippingProfileId": "",
          "ShippingProviderId": 0,
          "ShippingProviderProductId": 0,
          "ShippingProviderName": "'.$o["shipping_company"].'",
          "ShippingProviderProductName": "",
          "ShippingProfileName": "'.$o["shipping_company"].'",
          "PaymentInstruction": "'.strtok($o["payment_method"],"<").'",
          "IsCancelationFor": "",
          "PaymentTransactionId": "'.$t[1].'",
          "DistributionCenter": "'.$o["shipping_zone"].'",
          "DeliverySourceCountryCode": "'.array_search($o["shipping_country"],$countries).'",
          "CustomInvoiceNote": "",
          "CustomerNumber": "",
          "Customer": {
            "Name": "'.$o["firstname"]." ".$o["lastname"].'",
            "Email": "'.$o["email"].'",
            "Tel1": "'.$o["telephone"].'",
            "Tel2": "",
            "Number": 0,
            "PriceGroupId": 0,
            "LanguageId": 0
          },
          "PaymentReference": "",
          
          "History": [
            {
              "Created": "'.$o["date_added"].'",
              "EventTypeName": "",
              "Text": "",
              "EmployeeName": "",
              "TypeId": 0
            }
          ],
          "Payments": [
            {
              "BillbeeId": 0,
              "TransactionId": "'.$t[1].'",
              "PayDate": "'.$o["date_added"].'",
              "PaymentType": 0,
              "SourceTechnology": "",
              "SourceText": "",
              "PayValue": 0,
              "Purpose": "",
              "Name": "'.$o["payment_firstname"]." ".$o["payment_lastname"].'"
            }
          ],
          "LastModifiedAt": "'.$o["date_modified"].'",
          "ArchivedAt": "",
          "RestoredAt": "",
          "ApiAccountId": '.API_ACCOUNT_ID.',
          "ApiAccountName": "'.API_ACCOUNT_NAME.'",
          "MerchantVatId": "",
          "CustomerVatId": "",
          "IsFromBillbeeApi": true,';
        
        //Fetching products against current order
        $products_query="SELECT * FROM `oc_order_product` WHERE order_id={$o['order_id']}";
        $result_pr=mysqli_query($mysqli,$products_query);
        $products=[];
        while($result_rowpr=mysqli_fetch_assoc($result_pr))
        {
            $products[]=$result_rowpr;
        }
        //Calculating total shipping cost for all products for current order
        $shippings_query="SELECT * FROM `oc_xshippingpro` LIMIT 1";
        $result_shp=mysqli_query($mysqli,$shippings_query);
        $shippings_result=mysqli_fetch_assoc($result_shp);
        $shippings=[];
        if($shippings_result)
        {
            $shippings=json_decode($shippings_result['method_data'],true);
        }
        $shipping_cost=0;
        foreach($products as $pr)
        {
            if($shippings)
            {
                foreach($shippings['ranges'] as $sh_product)
                {
                    if($sh_product['product_id']==$pr['product_id'])
                    {
                        $total_cost=bcmul(str_replace(",",".",$sh_product['cost']),$pr['quantity']);
                        $shipping_cost+=$total_cost;
                    }
                }
            }
        }
        $order_data.='
             "ShippingCost":'.'"'.$shipping_cost.'",';
        $pp='[';
        
        //Adding products in order data
        foreach($products as $i=>$p)
        {
            if($i!=0)
            {
                $pp.=",";
            }
            $pp.='
            {
              "BillbeeId": 0,
              "TransactionId": "",
              "Product": {
                "OldId": "",
                "Id": "'.$p["order_product_id"].'",
                "Title": "'.$p["name"].'",
                "Weight": 0,
                "SKU": "'.$p["model"].'",
                "SkuOrId": "",
                "IsDigital": false,
                "EAN": "",
                "PlatformData": "",
                "TARICCode": "",
                "CountryOfOrigin": "",
                "BillbeeId": 0
              },
              "Quantity": "'.$p["quantity"].'",
              "TotalPrice": "'.$p["total"].'",
              "TaxAmount": "'.$p["tax"].'",
              "TaxIndex": 0,
              "Discount": 0,
              "GetPriceFromArticleIfAny": false,
              "IsCoupon": false,
              "ShippingProfileId": "",
              "DontAdjustStock": true,
              "UnrebatedTotalPrice": 0,
              "SerialNumber": "",
              "InvoiceSKU": "'.$p["model"].'",';
            
            // Adding Attributes
            $pp.='
                "Attributes": [';
            $prod_attr_query="SELECT * FROM `oc_order_option` WHERE order_product_id={$p['order_product_id']}";
            $result_prod_attr=mysqli_query($mysqli,$prod_attr_query);
            $prod_attrs=[];
            while($row_prod_attr=mysqli_fetch_assoc($result_prod_attr))
            {
                $prod_attrs[]=$row_prod_attr;
            }
            foreach($prod_attrs as $i=>$p_a)
            {
                if($i!=0)$pp.=",";
                $pp.='
                {
                  "Id": "'.$p_a["order_option_id"].'",
                  "Name": "'.$p_a["name"].'",
                  "Value": "'.$p_a["value"].'"
                }';
            }
            $pp.='
            ]}
            ';
        }
        $pp.=']}';
        $order_data.='"OrderItems":'.$pp;
        
        $data[$o["order_id"]]=$order_data;
        
    }
    //Initiating curl to make connection to remote server
    $ch=curl_init();
    $headers = array(
        'Content-Type:application/json',
        'Authorization: Basic '. base64_encode(API_ACCOUNT_NAME.':'.API_ACCOUNT_PASSWORD),
        'X-Billbee-Api-Key: '.API_ACCOUNT_KEY
    );
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL, 'https://api.billbee.io/api/v1/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    //Sending order data to billbee.
    foreach($data as $i=>$d)
    {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $d);
        $server_output = curl_exec($ch);
        //Writing the server response to log file.
        $logs = fopen(BILLBEE_LOGS_ADDRESS, "a") or die("Unable to open file!");
        fwrite($logs, date('d-M-Y H:i:s',time())."-Order No.:".$i."-".$server_output.PHP_EOL);
        // echo date('d-M-Y H:i:s',time())."-Order No.:".$d_array['Id']."-".$server_output.PHP_EOL;
        fclose($logs);
        $output=json_decode($server_output,1);
        //updating the database after successful completion of record creation on billbee.
        if($output['ErrorCode']==0 || $output['ErrorCode']==9)
        {
            $update="UPDATE `oc_order` set billbee=1 where order_id={$i}";
            mysqli_query($mysqli,$update);
        }
        
    }
    
    curl_close($ch);


?>
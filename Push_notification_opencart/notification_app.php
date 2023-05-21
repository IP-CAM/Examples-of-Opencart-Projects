<?php
session_start(); // Începe sesiunea
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Preiați valorile din formular
    $title = $_POST["title"];
    $body = $_POST["body"];
    $image = $_POST["image"];
    $soundEnabled = isset($_POST["sound_enabled"]) ? true : false;

    // Construiți notificarea
    $notification = array(
        "title" => $title,
        "body" => $body,
        "image" => $image,
        "sound" => $soundEnabled
    );

    // Definește cheia de server FCM
    $serverKey = "AAAAK-zSDvQ:APA91bHMItzzFlSR6UUfeZ8cJnaVHjGTM3zq6irFW2WH1PUbjJnso1y0lfkbP7fT5rY9olDjsbACIJ3fD6qyCw5T4PkpHYI50X7PpQdceaGu5lFANce4e7z2m7Ejv67jeozjwxM3FbTe";

    // Construiți corpul cererii JSON
    $data = array(
        "notification" => $notification,
        "to" => "/topics/your_topic"
    );
    $dataString = json_encode($data);

    // Definește URL-ul API FCM
    $url = "https://fcm.googleapis.com/fcm/send";

    // Configurați și trimiteți cererea Curl
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: key=" . $serverKey,
        "Content-Type: application/json",
        "Content-Length: " . strlen($dataString)
    ));

    $response = curl_exec($ch);
    curl_close($ch);

    // Verificați răspunsul și gestionați-l în consecință
    if ($response === false) {
        $_SESSION["notification_sent"] = false;
    } else {
        $_SESSION["notification_sent"] = true;
    }

    // Redirecționați către aceeași pagină pentru a afișa răspunsul
    header("Location: ".$_SERVER["PHP_SELF"]);
    exit();
}
?>
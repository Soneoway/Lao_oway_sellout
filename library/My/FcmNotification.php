<?php
class FcmNotification {

    public function send_notification($token, $payload_notification, $payload_data) {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $fields = array(
            //'registration_ids' => $token,
            //'condition' => "'logined' in topics || 'news' in topics",
            'to' => '/topics/news',
            'priority' => 'high',
            'notification' => $payload_notification,
            'data' => $payload_data
        );

        $headers = array(
            'Authorization: key=AAAA_uIb4yg:APA91bG3dQX6tHGI009S_eAwpEn_JLP2Mm3vWMpB7KPi2yUPeamAJBd5PdVvvmPdKhVRSpNrQoo9r7dNEuOBlPQw5qnQg_9HX2G9ZaYTCSjMBaGbyAajUqztzIjM-D0B2IgUedRB9hww',
            'Content-Type: application/json'
        );

        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporary
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }

        // Close connection
        curl_close($ch);
        echo $result;
    }
}
?>
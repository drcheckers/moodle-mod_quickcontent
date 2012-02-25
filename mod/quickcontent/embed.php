<?php

   require_once('quicklib.php');
        
   $url = $_GET['url'];
   $width = !empty($_GET['width'])?$_GET['width']:0;
   
   //http://api.embed.ly/1/oembed?url=http%3A%2F%2Fvimeo.com%2F18150336
   $encoded_url = "http://api.embed.ly/1/oembed?url=".rawurlencode($url)."&format=json";
   $curl = curl_init($encoded_url);
   
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
   $return = curl_exec($curl);
   curl_close($curl);
       
   $json_content = json_decode($return, true);
   
   echo parseEmbed($json_content,$width);
               
?>

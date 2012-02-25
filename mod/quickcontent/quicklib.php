<?php

function parseText($content,$x){
    $content = "This is a <b>test</b> http://www.bbc.co.uk <br/><i>http://vimeo.com/18150336</i>');
        http://www.flickr.com/photos/28634332@N05/4741770655/</i>');
        <br/><i>http://www.teachertube.com/viewVideo.php?video_id=30&title=Walkthroughs_and_Learning_Objectives</i>');
        <br/><i>http://www.youtube.com/watch?v=hI-BDR2UcmU</i>');";
    $regexp='#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#'; 
    preg_match_all($regexp,
         $content, $matches);
    foreach($matches[0] as $m){
        $content .= str_replace($m,parseEmbed($m,$x),$content);    
    }
    return $content;
}  

function parseEmbed($content,$w=0){
   // calculate any sizing ration increase/decrease
   if($w==-1){
        $embedcode = $previewcode = "<a href='{$content['url']}'>{$content['url']}</a>";       
   }else{
       $ww = isset($content['width'])?$content['width']:
                        (isset($content['thumbnail_width'])?$content['thumbnail_width']:400);
       $wh = isset($content['height'])?$content['height']:
                        (isset($content['thumbnail_height'])?$content['thumbnail_height']:300);
       if($w>0){
            $nw = $w;
            $nh = floor(($wh * $w)/$ww);    
       }else{
            $nw=$ww; $nh=$wh;
       }

       if(isset($content['html'])){
            // does it come with html already?
            $html = str_replace("width=\"$ww\"","width=\"$nw\"",
                        str_replace("height=\"$wh\"","height=\"$nh\"",
                                $content['html']));
            
            $embedcode = $previewcode = $html;

       }elseif(isset($content['type'])){
           switch ($content['type']) {
               case 'photo':
                    $embedcode = "<img width='$nw' height='$nh' src='{$content['url']}' alt='{$content['title']}'></img>";   
                    $previewcode = $embedcode . "<br/><b>Notes</b><br/>Provider: {$content['provider_name']}<br/>Description: {$content['description']}";   
                    break;
               case 'link':
                    $embedcode = "<iframe width='$nw' height='$nh' src='{$content['url']}'></iframe>";   
                    $previewcode = $embedcode . "<br/><b>Notes</b><br/>Provider: {$content['provider_name']}<br/>Description: {$content['description']}";   
                    break;
               default:
                    $embedcode = $previewcode =  $content['type'] . '<br/>' . $content['description'];
           }
       }else{
             $embedcode = $previewcode = 'No content available to embed';
       }   
   }
       
   return json_encode(array('embedCode'=>$embedcode, 'previewCode'=>$previewcode));
       
}               
?>

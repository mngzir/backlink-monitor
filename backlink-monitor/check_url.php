<?php
require __DIR__ . '/inc/curl/vendor/autoload.php';
use Curl\Curl;

function mngz_backlink_monitor_check_url($url,$domain){
    $curl = new Curl();
    $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
    $curl->get($url);
    if ($curl->error) {
        return [
            "status"=>$curl->errorCode,
            "message"=>'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n"
        ];

    } else {
        $data = mngz_backlink_monitor_get_links($curl->response,$domain);
        $status = $curl->getHttpStatusCode();
         return [
            "status"=>$status,
            "data"=> $data,
            "message"=>''
        ];
    }

}

function mngz_backlink_monitor_get_links($htmlString,$domain){
  //Create a new DOMDocument object.
  $htmlDom = new DOMDocument;

  //Load the HTML string into our DOMDocument object.
  @$htmlDom->loadHTML(mb_convert_encoding($htmlString, 'HTML-ENTITIES', 'UTF-8'));

  //Extract all anchor elements / tags from the HTML.
  $anchorTags = $htmlDom->getElementsByTagName('a');

  //Create an array to add extracted images to.
  $extractedAnchors = array();

  //Loop through the anchors tags that DOMDocument found.
  foreach($anchorTags as $anchorTag){

      //Get the href attribute of the anchor.
      $aHref = $anchorTag->getAttribute('href');

      //Get the title text of the anchor, if it exists.
      $aTitle = $anchorTag->getAttribute('title');
      $rel = $anchorTag->getAttribute('rel');

      $aValue = $anchorTag->nodeValue;
     if (strpos($aHref, $domain) !== false) {
         if(empty(trim($aValue))){
            $aValue = "image";
         }
        //Add the anchor details to $extractedAnchors array.
        $extractedAnchors[] = array(
            'href' => $aHref,
            'title' => $aTitle,
            'value' => $aValue,
            'rel' => $rel
        );
      }

  }

  unset($anchorTags);

  //Extract all anchor elements / tags from the HTML.



  //canonical check
  $linkTags = $htmlDom->getElementsByTagName('link');
  $canonical = "";
  foreach($linkTags as $linkTag){
      $linkAhref = $linkTag->getAttribute('href');
      $rel = $linkTag->getAttribute('rel');
        if ($rel == "canonical") {
            $canonical = $linkAhref;
        }
  }


  //robot check
  $metaTags = $htmlDom->getElementsByTagName('meta');
  $meta = array();
  foreach($metaTags as $metaTag){
      $meta_name = $metaTag->getAttribute('name');
      $meta_content = $metaTag->getAttribute('content');
      $meta[] = array(
        "name"=>$meta_name,
        "content"=>$meta_content
      );

  }


  $titleTags = $htmlDom->getElementsByTagName('title');
  $title = $titleTags[0]->nodeValue;

  $result = [
    "title"=>$title,
    "links"=>$extractedAnchors,
    "canonical"=>$canonical,
    "meta"=>$meta
  ];

  unset($htmlDom);
  //echo "<pre>";
  //print_r our array of anchors.
  return $result;
}

?>
<?php
/*======================================================================*\
|| #################################################################### ||
|| # Copyright &copy;2009 Quoord Systems Ltd. All Rights Reserved.    # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # This file is part of the Tapatalk package and should not be used # ||
|| # and distributed for any other purpose that is not approved by    # ||
|| # Quoord Systems Ltd.                                              # ||
|| # http://www.tapatalk.com | http://www.tapatalk.com/license.html   # ||
|| #################################################################### ||
\*======================================================================*/
defined('IN_MOBIQUO') or exit;
require_once( IPS_ROOT_PATH . 'sources/base/core.php' );
function to_utf8($str)
{
    global $charset;
    return IPSText::convertCharsets($str, $charset, 'utf-8');
}

function to_local($str)
{
    global $charset;
	
    if (empty($str) || preg_match('/utf-?8/si', $charset)) return $str;
    $str = mobiquo_unicodeEntity($str);
    $str = htmlEntityToLocal($str);
    
    return $str;
}

function mobiquo_unicodeEntity( $unicodeString )
{
    $outString = "";
    $stringLength = strlen( $unicodeString );

    for( $charPosition = 0; $charPosition < $stringLength; $charPosition++ ) 
    {
        $char = $unicodeString [$charPosition];
        $asciiChar = ord ($char);

        if ($asciiChar < 128) //1 7 0bbbbbbb (127)
        {
           $outString .= $char; 
        }
        else if ($asciiChar >> 5 == 6) //2 11 110bbbbb 10bbbbbb (2047)
        {
           $firstByte = ($asciiChar & 31);
           $charPosition++;
           $char = $unicodeString [$charPosition];
           $asciiChar = ord ($char);
           $secondByte = ($asciiChar & 63);
           $asciiChar = ($firstByte * 64) + $secondByte;

           $entity = sprintf ( "&#%d;", $asciiChar );
           $outString .= $entity;
        }
        else if ($asciiChar >> 4 == 14)  //3 16 1110bbbb 10bbbbbb 10bbbbbb
        {
            $firstByte = ($asciiChar & 31);
            $charPosition++;
            $char = $unicodeString [$charPosition];
            $asciiChar = ord ($char);
            $secondByte = ($asciiChar & 63);
            $charPosition++;
            $char = $unicodeString [$charPosition];
            $asciiChar = ord ($char);
            $thirdByte = ($asciiChar & 63);
            $asciiChar = ((($firstByte * 64) + $secondByte) * 64) + $thirdByte;
            
            $entity = sprintf ("&#%d;", $asciiChar);
            $outString .= $entity;
        }
        else if ($asciiChar >> 3 == 30) //4 21 11110bbb 10bbbbbb 10bbbbbb 10bbbbbb
        {
            $firstByte = ($asciiChar & 31);
            $charPosition++;
            $char = $unicodeString [$charPosition];
            $asciiChar = ord ($char);
            $secondByte = ($asciiChar & 63);
            $charPosition++;
            $char = $unicodeString [$charPosition];
            $asciiChar = ord ($char);
            $thirdByte = ($asciiChar & 63);
            $charPosition++;
            $char = $unicodeString [$charPosition];
            $asciiChar = ord ($char);
            $fourthByte = ($asciiChar & 63);
            $asciiChar = ((((($firstByte * 64) + $secondByte) * 64) + $thirdByte) * 64) + $fourthByte;
        
            $entity = sprintf ("&#%d;", $asciiChar);
            $outString .= $entity;
        }
      }

    return $outString;
}

function htmlEntityToLocal($str)
{
    global $charset;
    
    if (empty($str) || preg_match('/utf-?8/si', $charset)) return $str;
    
    static $charset_89, $charset_AF, $charset_8F, $charset_chr, $charset_html, $support_mb, $charset_entity, $charset_to;
    
    if (!isset($charset_to))
    {
        include_once('./lib/charset.php');
        
        $charset_chr = array();
        if (preg_match('/iso-?8859-?(\d+)/i', $charset, $match_iso))
        {
            $charset_to = 'ISO-8859-' . $match_iso[1];
            $charset_chr = $charset_AF;
        }
        else if (preg_match('/windows-?125(\d)/i', $charset, $match_win))
        {
            $charset_to = 'Windows-125' . $match_win[1];
            $charset_chr = $charset_8F;
        }
    }
    
    if ($charset_chr)
    {
        $str = str_replace(array_values($charset_entity), array_keys($charset_entity), $str);
        $str = str_replace($charset_html[$charset_to], $charset_chr, $str);
        //$str = str_replace(array_keys($charset_entity), array_values($charset_entity), $str);
    }
    
    return $str;
}


function mobiquo_iso8601_encode($timet)
{
    global $registry;
    $offset = $registry->getClass( 'class_localization')->getTimeOffset();
    $member = $registry->member()->fetchMemberData();

    $time_zone = $member['time_offset'];
    $first_part = intval( $time_zone );
    if (preg_match('/^-/', $time_zone)) {
        $second_part = $first_part - $time_zone;
    } else {
        $second_part = $time_zone - $first_part;
    }
    if ($second_part) {
        $second_part = 60 * $second_part;
    }
    if (preg_match('/^-(\d)$/', $first_part)) {
        $first_part = preg_replace('/^-(\d)$/', '-0$1', $first_part);
    } elseif (preg_match('/^(\d)$/', $first_part)) {
        $first_part = preg_replace('/^(\d)$/', '+0$1', $first_part);
    } elseif (preg_match('/^(\d)/', $first_part)) {
        $first_part = preg_replace('/^(\d)/', '+$1', $first_part);
    }

    if (preg_match('/^(\d)$/', $second_part)) {
        $second_part = preg_replace('/^(\d)$/', '0$1', $second_part);
    }

    $result = gmstrftime("%Y%m%dT%H:%M:%S", ($timet + $offset) ) . $first_part . ":" . $second_part;

    return $result;
}

function get_avatar($member)
{
    static $avatar = array();

    $id = is_array($member) ? $member['member_id'] : $member;

    if (isset($avatar[$id]))
        return $avatar[$id];
    else
    {
        $url = IPSMember::buildAvatar($member);
        if (preg_match('/<img src=\'(.*?)\'/si', $url, $match)) {
            $avatar[$id] = $match[1];
            return $match[1];
        } else {
            return '';
        }
    }
}

function get_short_content($message, $mode=0, $length = 200 )
{
    $message = preg_replace('/\s+/', ' ', $message);
    $message = preg_replace('/\[url.*?\].*?\[\/url.*?\]/si', '###url###', $message);
    $message = preg_replace('/\[img.*?\].*?\[\/img.*?\]/si', '###img###', $message);
    while (preg_match('/(\[list\].*?)\[\*\](.*\[\/list\])/si', $message)) {
        $message = preg_replace('/(\[list\].*?)\[\*\](.*\[\/list\])/si', '$1$2', $message);
    }
    $message = preg_replace('/\[email=.*?\](.*?)\[\/email\]/si', '$1', $message);
    $message = stripBbcode('quote', $message);
    $message = preg_replace('/\[\/(url|img)\]/si', '', $message);

    $message = preg_replace('/\[(\/)?(size|font|color).*?\]/si', '', $message);
    $message = preg_replace('/\[(\/)?(code|media|list|b|i|u|s|sub|sup|right|center|left|indent)\]/si', '', $message);
    if (preg_match('/\[attachment=(\d+).*?\]/si', $message)) {
        $message .= '#Attachment(s)#';
    }
    $message = preg_replace('/\[attachment=(\d+).*?\]/si', '', $message);

    $message = preg_replace('/###(url|img)###/', '[$1]', $message);
    $message = strip_tags($message);
    $message = to_utf8($message);
    $message = mobi_unescape_html($message);
    if ($mode) {
        $message = strip_tags($message);
        $message = str_replace('&nbsp;', ' ', $message);
    }
    $message = preg_replace('/^\s*|\s*$/', '', $message);
    $message = preg_replace('/[\n\r\t]+/', ' ', $message);
    $message = preg_replace('/\s+/', ' ', $message);
    $message = cutstr($message, $length);

    return $message;
}

function cutstr($string, $length, $dot = '') {
    global $context;

    if(strlen($string) <= $length) {
        return $string;
    }

    $string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);

    $strcut = '';

    $n = $tn = $noc = 0;
    while($n < strlen($string)) {

        $t = ord($string[$n]);
        if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
            $tn = 1; $n++; $noc++;
        } elseif(194 <= $t && $t <= 223) {
            $tn = 2; $n += 2; $noc += 2;
        } elseif(224 <= $t && $t <= 239) {
            $tn = 3; $n += 3; $noc += 2;
        } elseif(240 <= $t && $t <= 247) {
            $tn = 4; $n += 4; $noc += 2;
        } elseif(248 <= $t && $t <= 251) {
            $tn = 5; $n += 5; $noc += 2;
        } elseif($t == 252 || $t == 253) {
            $tn = 6; $n += 6; $noc += 2;
        } else {
            $n++;
        }

        if($noc >= $length) {
            break;
        }

    }
    if($noc > $length) {
        $n -= $tn;
    }

    $strcut = substr($string, 0, $n);

    return $strcut.$dot;
}


function get_error($err_key)
{
    global $registry;
    $lang = $registry->getClass('class_localization');
    $lang->loadLanguageFile( array( 'public_error' ), 'core' );
    $language = $lang->words;

    $err_str = isset($language[$err_key]) ? $language[$err_key] : $err_key;
    $err_str = strip_tags($err_str);
    $err_str = mobi_unescape_html($err_str);

    $response = new xmlrpcresp(
        new xmlrpcval(array(
            'result'        => new xmlrpcval(false, 'boolean'),
            'result_text'   => new xmlrpcval($err_str, 'base64'),
        ),'struct')
    );

    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".$response->serialize('UTF-8');
    exit;
}

function post_html_clean($str)
{
	$str = str_replace('[t-', '[', $str);
	$str = str_replace('[/t-', '[/', $str);
    // process [code] bbcode
    $str = preg_replace('/<pre class=\'prettyprint\'>(.*?)<\/pre>/sie', "'[quote]'.str_replace('\n', '<br />', '$1').'[/quote]'", $str);
    $str = preg_replace('/\n/si', '', $str);
    
    ####something in quote########
    if ( strstr( $str, '{timestamp:' ) )
    {
        $str = preg_replace_callback( '#<!--\{timestamp:(\d+?):([^\}]+?)\}-->#', create_function( '$key', 'return ipsRegistry::getClass(\'class_localization\')->getDate($key[1], \'$key[2]\', 1);' ), $str );
    }
    $str = preg_replace('/(<p class=[\'"]citation[\'"]>.*?<\/p>)\s*(<div class=[\'"]blockquote[\'"]><div class=[\'"]quote[\'"]>)/si', '$2[b]$1[/b]<br />', $str);

    ####deal with emotion############
    $str = str_replace('/<#EMO_DIR#>/', '/#EMO_DIR#/', $str);
    $str = preg_replace('/<img [^>]*?class=(\'|")bbc_emoticon\1 [^>]*?alt=(\'|")(.*?)\2[^>]*?\/>/si', '$3', $str);

    ###deal with attachment HTML #########
    $str = preg_replace('/(<div id=[\'"]attach_wrap["\'] .*?>)<h4>.*?<\/h4>(.*?<\/div>)/si', '$1$2', $str);

    $replace = array(
        array('/<img .*?src=(\'(.*?)\'|"(.*?)").*?>/sei', "'[img]'.url_encode('$2').url_encode('$3').'[/img]'"),
        array('/<br\s*\/?>|<\/cite>/si', "\n"),
    );
    
    // process list tag
    $str = preg_replace('/<ul class=\'(.*)\'>(.*)<\/ul>/sieU', "process_list_tag('$1', '$2')", $str);
    
    ############deal with the F**king quote##################
    ###change to bbcode###
    while (preg_match('/<div class=("blockquote"|\'blockquote\')><div class=(\'quote\'|"quote")>(.*?)<\/div><\/div>/si', $str, $match)) {
        $from_str = preg_replace('/(<div class=("blockquote"|\'blockquote\')><div class=(\'quote\'|"quote")>.*?<\/div><\/div>)/si', '$1', $match[0]);
        $text     = preg_replace('/<div class=("blockquote"|\'blockquote\')><div class=(\'quote\'|"quote")>(.*?<\/div><\/div>)/si', '$3', $match[0]);
        $to_str = preg_replace('/<div class=("blockquote"|\'blockquote\')><div class=(\'quote\'|"quote")>(.*?)<\/div><\/div>/si', '$3', $match[0]);
        while (preg_match('/<div class=("blockquote"|\'blockquote\')><div class=(\'quote\'|"quote")>(.*?<\/div><\/div>)/si', $text, $match1)) {
            $from_str = preg_replace('/(<div class=("blockquote"|\'blockquote\')><div class=(\'quote\'|"quote")>.*?<\/div><\/div>)/si', '$1', $match1[0]);
            $text = preg_replace('/<div class=("blockquote"|\'blockquote\')><div class=(\'quote\'|"quote")>(.*?<\/div><\/div>)/si', '$3', $match1[0]);
            $to_str = preg_replace('/<div class=("blockquote"|\'blockquote\')><div class=(\'quote\'|"quote")>(.*?)<\/div><\/div>/si', '$3', $match1[0]);
        }
        $str = str_replace($from_str, '[quote]' .$to_str. '[/quote]', $str);
    }
    $str = str_replace('<p class=\'citation\'>Quote</p>', '', $str);
    //deal with [indent]
    while(preg_match('/<p class=\'bbc_indent\' style=\'margin-left: (.*?)px;\'>(.*?)<\/p>/si', $str, $match2))
    {
    	$loop = $match2[1] / 40;
    	$to_str = '';
    	for($i = 0;$i <= $loop ; $i++)
    	{
    		$to_str .= '    ';
    	}
    	$match2[2] = str_replace('<br />', '<br />' . $to_str , $match2[2]);
    	$str = str_replace($match2[0], $to_str . $match2[2], $str);
    }   
    	
    
    ###deal with links#############
    $count = 0;
    $matches = array();
    $href_array = array();
    if ( preg_match_all('/<a .*?href=(\'(.*?)\'|"(.*?)").*?>(.*?)<\/a>/si', $str, $matches) ) {
        foreach($matches[0] as $match) {
            $to_str = preg_replace('/<a .*?href=(\'(.*?)\'|"(.*?)").*?>(.*?)<\/a>/si', '[###url='.$count .']$4[/url###]', $match);
            $href = preg_replace('/<a .*?href=(\'(.*?)\'|"(.*?)").*?>(.*?)<\/a>/si', '$2$3', $match);
            $str = str_replace($match, $to_str, $str);
            $href_array[$count] = url_encode($href);
            $count ++;
        }
    }
	
    ###deal with IMG#################
    foreach ($replace as $pattern) {
        $matches = array();
        if (preg_match_all($pattern[0], $str, $matches)) {
            foreach($matches[0] as $match) {
                $to_str = preg_replace($pattern[0], $pattern[1], $match);
                $str = str_replace($match, $to_str, $str);
            }
        }
    }

    ###deal with media###############
    $str = parse_media($str);
     	
    ###deal with bbcode with return_html is required
    if (isset($GLOBALS['return_html']) && $GLOBALS['return_html'])
    {
        $str = preg_replace('/<strong [^>]*>(.*?)<\/strong>/si', '[b]$1[/b]', $str);
        $str = preg_replace('/<span class=\'bbc_underline\'>(.*?)<\/span>/si', '[u]$1[/u]', $str);
        $str = preg_replace('/<em [^>]*>(.*?)<\/em>/si', '[i]$1[/i]', $str);
        //$str = preg_replace('/<span style=\'color:\s*([^\']+)\'>(.*?)<\/span>/si', '[color=$1]$2[/color]', $str);
	   	
	    
    }
    
    $str = strip_tags($str);

    ###Get rid of the inner quote####
    $str = stripInnerTag('quote', $str);

    ###if get from cache or bbcode is disabled###############
    while (preg_match('/(\[list\].*?)\[\*\](.*\[\/list\])/si', $str)) {
        $str = preg_replace('/(\[list\].*?)\[\*\](.*\[\/list\])/si', '$1$2', $str);
    }
    
    $str = preg_replace('/\[email=.*?\](.*?)\[\/email\]/si', '[url]$1[/url]', $str);
    $str = preg_replace('/\[(\/)?(size|font)[^\]]*?\]/si', '', $str);
    $str = preg_replace('/\[(\/)?(code|media|list|s|sub|sup|right|center|left|indent)\]/si', '', $str);
    $str = preg_replace('/\[attachment=(\d+).*?\]/si', '', $str);
    ##################################
    $str = to_utf8($str);
   	$str = mobi_unescape_html($str);
   	
    // remove link on img
    $str = preg_replace('/\[###url=(\d)+\](\[img\].*?\[\/img\])\[\/url###\]/si', '$2', $str);

    // remove quote icon
    $str = preg_replace('/\[img\](.*)(snapback.png|attachicon.gif)\[\/img\]/si', '', $str);

    if (preg_match_all('/\[###url=(\d+)\](.*?)\[\/url###\]/si', $str, $matches) ) {
        foreach($matches[0] as $match) {
            $count     = preg_replace('/\[###url=(\d+)\].*?\[\/url###\]/si', '$1', $match);
            $text     = preg_replace('/\[###url=(\d+)\](.*?)\[\/url###\]/si', '$2', $match);
            $to_str = '[url='.$href_array[$count] .']'.$text. '[/url]';
            $str = str_replace($match, $to_str, $str);
        }
    }
    
    return parse_bbcode($str);
}

function parse_bbcode($str)
{
    $search = array(
        '#\[(b)\](.*?)\[/b\]#si',
        '#\[(u)\](.*?)\[/u\]#si',
        '#\[(i)\](.*?)\[/i\]#si',
        '#\[color=(\#[\da-fA-F]{3}|\#[\da-fA-F]{6}|[A-Za-z]{1,20})\](.*?)\[/color\]#',
    	'#\[color=(rgb\(\d{1,3}, ?\d{1,3}, ?\d{1,3}\))\](.*?)\[/color\]#'
    );

    if ($GLOBALS['return_html']) {
        $str = htmlspecialchars($str, ENT_NOQUOTES);
        $replace = array(
            '<$1>$2</$1>',
            '<$1>$2</$1>',
            '<$1>$2</$1>',
            '<font color="$1">$2</font>',
        	'<font color="$1">$2</font>',
        );
        $str = str_replace("\n", '<br />', $str);
    } else {
        $replace = '$2';
    }
    $str = preg_replace($search, $replace, $str);
    $str = str_replace('[b]', '<b>', $str);
    $str = str_replace('[/b]', '</b>', $str);
    $str = str_replace('[u]', '<u>', $str);
    $str = str_replace('[/u]', '</u>', $str);
    $str = str_replace('[i]', '<i>', $str);
    $str = str_replace('[/i]', '</i>', $str);
    return $str;
}

function parse_media($str)
{
    $str = preg_replace('/<embed[^>]*?src="(.*?)"[^>]*?>/sei', "parse_video('$1')", $str);
    $str = preg_replace('/<param[^>]*?value="mp3=([^>]*?\.mp3)&[^>]*?"[^>]*?>/si', '[url=$1] >> [MP3] [/url]', $str);
    $str = preg_replace('/<iframe[^>]*?src="(http:\/\/www\.flickr\.com[^"]*?)"[^>]*?>/si', '[url=$1] >> [Flickr Image Set] [/url]', $str);
    $str = preg_replace('/<iframe[^>]*?src="(http:\/\/www\.youtube\.com\/embed[^"]*?)"[^>]*?>/sei', "parse_video('$1')", $str);

    return $str;
}

function parse_video($url)
{
    if (preg_match('#youtube\.com|youtu\.be#', $url))
        $str = 'YouTube';
    elseif (strpos($url, 'video.google.com') !== false)
        $str = 'Google Video';
    elseif (strpos($url, 'myspace.com') !== false)
        $str = 'MySpace Video';
    elseif (strpos($url, 'gametrailers.com') !== false)
        $str = 'GameTrailers';
    elseif (preg_match('#\.swf$#', $url))
        $str = 'Flash Movie/Game';
    else
        $str = '';

    if ($str)
        return "[url=$url] >> [$str] [/url]";
    else
        return '[Unknown Media]';
}

function process_list_tag($type, $list)
{
    if ($type == 'bbc')
    {
        $list = preg_replace('/<li>(.*)<\/li>/sieU', "'  '.'* $1'", $list);
    }
    else
    {
        $index = 1;
        $list = preg_replace('/<li>(.*)<\/li>/sieU', "'  '.\$index++.'. '.trim('$1')", $list);
    }
    
    return "\n\n$list\n\n";
}

function subject_clean($str)
{
    $str = strip_tags($str);
    $str = to_utf8($str);
    $str = mobi_unescape_html($str);
    return $str;
}

function escape_latin_code($str, $target_encoding){
    preg_match_all("/&#\d+;|&\w+;|.+|\\r|\\n/U", $str, $r);
      $ar = $r[0];

      foreach($ar as $k=>$v) {
           if(substr($v,0,2) != "&#" && substr($v,0,1) == "&") {
            $ar[$k] =@html_entity_decode($v,ENT_QUOTES,$target_encoding);
        }
      }
      return join("", $ar);
}

function mobiquo_to_local($str){
    global $charset;
    $target_encoding = $charset;
    $in_encoding = 'UTF-8';

    if(strtolower($target_encoding) == strtolower($in_encoding) ){
        $str = escape_latin_code($str, $target_encoding);
        return $str;
    }else{
        if(function_exists('mb_convert_encoding')){
            $str =  @mb_convert_encoding($str,'HTML-ENTITIES','UTF-8');
        }
        if (function_exists('mb_convert_encoding') AND $encoded_data = @mb_convert_encoding($str, $target_encoding, $in_encoding))
        {
               $encoded_data =escape_latin_code($encoded_data ,$target_encoding);
               return  $encoded_data;
        } else {
            $str = escape_latin_code($str ,$target_encoding);
            if($target_encoding == 'ISO-8859-1'){
                $str = utf8_decode($str);
            }
            return $str;
        }
    }
}

function mobiquo_to_utf8($str){
    $in_encoding = $charset;
    $target_encoding = 'UTF-8';

    $str =strip_tags($str);
    if(function_exists('htmlspecialchars_decode')){
            $str =htmlspecialchars_decode($str);
    }

    if(strtolower($target_encoding) == strtolower($in_encoding) ){
        $str = unescape_htmlentitles($str);
        $str = escape_latin_code($str,$target_encoding);
        return $str;
    }else{
        if (function_exists('mb_convert_encoding') AND $encoded_data = @mb_convert_encoding($str, $target_encoding, $in_encoding))
        {
               $encoded_data =escape_latin_code($encoded_data ,$target_encoding);
               $encoded_data = unescape_htmlentitles($encoded_data);
               return  $encoded_data;
        } else {
            $str = escape_latin_code($str ,$target_encoding);
            return unescape_htmlentitles($str);
        }
    }
}


function unescape_htmlentitles($str)
{
       global $stylevar;
      preg_match_all("/(?:%u.{4})|.{4};|&#\d+;|.+|\\r|\\n/U",$str,$r);
      $ar = $r[0];

      foreach($ar as $k=>$v) {
        if(substr($v,0,2) == "&#") {
                $ar[$k] =@html_entity_decode($v,ENT_QUOTES, 'UTF-8');
        }
      }
      return join("",$ar);
}

function mobi_unescape_html($str)
{
    //$str = str_replace(array('&nbsp;','&lt;', '&gt;', '&quot;', '&amp;', ),   array(' ','<', '>', '"', '&', ), $str);
    $str = unescape_htmlentitles($str);
    $str = escape_latin_code($str ,'UTF-8');
    return $str;
}

function stripBbcode( $tag, $txt )
{
    //-----------------------------------------
    // Protect against endless loops
    //-----------------------------------------
    static $iteration    = array();

    if( array_key_exists( $tag, $iteration ) AND $iteration[ $tag ] > 2000 )
    {
        return $txt;
    }

    $iteration[ $tag ]++;

    if( stripos( $txt, '[' . $tag ) !== false )
    {
        $start_position = stripos( $txt, '[' . $tag );
        $end_position    = stripos( $txt, '[/' . $tag . ']', $start_position );

        if( $start_position === false OR $end_position === false )
        {
            return $txt;
        }

        while (stripos( $txt, '[' . $tag , $start_position + 1 ) !== FALSE AND (stripos( $txt, '[' . $tag , $start_position + 1 ) < $end_position)) {
            $start_position    = stripos( $txt, '[' . $tag , $start_position + 1 );
        }

        $txt = substr_replace( $txt, '', $start_position, $end_position - $start_position + strlen('[/' . $tag . ']') );

        return stripBbcode( $tag, $txt );
    }
    else
    {
        return $txt;
    }
}

function stripInnerTag($tag, $txt)
{
    $start_position = 0;
    while( stripos( $txt, '[' . $tag, $start_position ) !== false )
    {
        $start_position = stripos( $txt, '[' . $tag, $start_position);
        $end_position    = stripos( $txt, '[/' . $tag . ']', $start_position );

        if( $start_position === false OR $end_position === false )
        {
            return $txt;
        }
        $inner_content    = substr( $txt, stripos( $txt, ']', $start_position ) + 1, $end_position - (stripos( $txt, ']', $start_position ) + 1) );
        $extra_closers    = substr_count( $inner_content, '[' . $tag );
        if( $extra_closers > 0 )
        {
            for( $done=0; $done < $extra_closers; $done++ )
            {
                $end_position = stripos( $txt, '[/' . $tag . ']', $end_position + 1 );
                $inner_content    = substr( $txt, stripos( $txt, ']', $start_position ) + 1, $end_position - (stripos( $txt, ']', $start_position ) + 1) );
                $extra_closers_1    = substr_count( $inner_content, '[' . $tag );
                if ($extra_closers_1 > $extra_closers) {
                    $extra_closers = $extra_closers_1;
                }
            }
        }

        $inner_content    = substr( $txt, stripos( $txt, ']', $start_position ) + 1, $end_position - (stripos( $txt, ']', $start_position ) + 1) );

        $replace = stripBbcode($tag, $inner_content);
        $txt = str_replace( $inner_content, $replace, $txt);
        $start_position = $start_position + strlen($replace) + strlen('[/' . $tag . ']');

    }

    return $txt;
}

function ipboard_version()
{
    $app_version = trim(ipsRegistry::$applications['forums']['app_version']);
    $temp_array = explode(' ', $app_version, 2);
    return $temp_array[0];
}

function url_encode($url)
{
    global $board_url;
    $url_data = parse_url($board_url);
    $host_name = $url_data['scheme'].'://'.$url_data['host'].(isset($url_data['port']) ? ':'.$url_data['port'] : '');

    $url = preg_replace('/^\s*|\s*$/s', '', $url);
    $url = rawurlencode($url);
    $from = array('/%3A/', '/%2F/', '/%3F/', '/%2C/', '/%3D/', '/%26/', '/%25/', '/%23/', '/%2B/', '/%3B/');
    $to   = array(':',     '/',     '?',     ',',     '=',     '&',     '%',     '#',     '+',     ';');
    $url = preg_replace($from, $to, $url);

    if (preg_match('/^\//', $url)) {
        $url = $host_name.$url;
    } else if ($url && !preg_match('/^http/', $url)) {
        $url = "$board_url/$url";
    }

    return htmlspecialchars_decode($url);
}

function follow_item($type, $add = true)
{
    global $registry, $member, $request_params, $freq_option;

    if (!$member['member_id'])
    {
        get_error("Please Login!");
    }

    $relid = intval($request_params[0]);
    $like_notify_do = empty($freq_option) ? 0 : 1;

    if (!$relid) get_error('Missing Forum ID');

    require_once( IPS_ROOT_PATH . 'sources/classes/like/composite.php' );
    $like = classes_like::bootstrap('forums', $type);

    if ($add) {
        if ($like->isLiked($relid, $member['member_id'])) {
            $_likeKey = classes_like_registry::getKey( $relid, $member['member_id']);
            $registry->DB()->update( 'core_like', array( 'like_notify_do' => $like_notify_do, 'like_notify_freq' => $freq_option ), "like_id='" . addslashes($_likeKey) . "'" );
        } else {
            $like->add( $relid, $member['member_id'], array( 'like_notify_do' => $like_notify_do, 'like_notify_freq' => $freq_option ), 0 );
        }
    } else
        $like->remove( $relid, $member['member_id'] );

    return true;
}

function is_subscribed($id, $type = 'topics')
{
    global $member;

    if (!$member['member_id'] || !$id) return false;

    require_once( IPS_ROOT_PATH . 'sources/classes/like/composite.php' );

    if ($type == 'topics')
    {
        static $like_topic;
        if (!$like_topic)
            $like_topic = classes_like::bootstrap('forums', 'topics');

        return $like_topic->isLiked( $id, $member['member_id'] );
    }
    else
    {
        static $like_forum;
        if (!$like_forum)
            $like_forum = classes_like::bootstrap('forums', 'forums');

        return $like_forum->isLiked( $id, $member['member_id'] );
    }
}

function get_forum_icon($id, $type = 'forum', $lock = false, $new = false)
{
    if (!in_array($type, array('link', 'category', 'forum')))
        $type = 'forum';
    
    $icon_name = $type;
    if ($type != 'link')
    {
        if ($lock) $icon_name .= '_lock';
        if ($new) $icon_name .= '_new';
    }
    
    $icon_map = array(
        'category_lock_new' => array('category_lock', 'category_new', 'lock_new', 'category', 'lock', 'new'),
        'category_lock'     => array('category', 'lock'),
        'category_new'      => array('category', 'new'),
        'lock_new'          => array('lock', 'new'),
        'forum_lock_new'    => array('forum_lock', 'forum_new', 'lock_new', 'forum', 'lock', 'new'),
        'forum_lock'        => array('forum', 'lock'),
        'forum_new'         => array('forum', 'new'),
        'category'          => array(),
        'forum'             => array(),
        'lock'              => array(),
        'new'               => array(),
        'link'              => array(),
    );
    
    $final = empty($icon_map[$icon_name]);
    
    if ($url = get_forum_icon_by_name($id, $icon_name, $final))
        return $url;
    
    foreach ($icon_map[$icon_name] as $sub_name)
    {
        $final = empty($icon_map[$sub_name]);
        if ($url = get_forum_icon_by_name($id, $sub_name, $final))
            return $url;
    }
    
    return '';
}

function get_forum_icon_by_name($id, $name, $final)
{
    global $tapatalk_forum_icon_dir, $tapatalk_forum_icon_url;
    
    $filename_array = array(
        $name.'_'.$id.'.png',
        $name.'_'.$id.'.jpg',
        $id.'.png', $id.'.jpg',
        $name.'.png',
        $name.'.jpg',
    );
    
    foreach ($filename_array as $filename)
    {
        if (file_exists($tapatalk_forum_icon_dir.$filename))
        {
            return $tapatalk_forum_icon_url.$filename;
        }
    }
    
    if ($final) {
        if (file_exists($tapatalk_forum_icon_dir.'default.png'))
            return $tapatalk_forum_icon_url.'default.png';
        else if (file_exists($tapatalk_forum_icon_dir.'default.jpg'))
            return $tapatalk_forum_icon_url.'default.jpg';
    }
    
    return false;
}

function mobi_color_convert($color, $str , $is_background)
{
    static $colorlist;
    
    if (preg_match('/#[\da-fA-F]{6}/is', $color))
    {
        if (empty($colorlist))
        {
            $colorlist = array(
                '#000000' => 'Black',             '#708090' => 'SlateGray',       '#C71585' => 'MediumVioletRed', '#FF4500' => 'OrangeRed',
                '#000080' => 'Navy',              '#778899' => 'LightSlateGrey',  '#CD5C5C' => 'IndianRed',       '#FF6347' => 'Tomato',
                '#00008B' => 'DarkBlue',          '#778899' => 'LightSlateGray',  '#CD853F' => 'Peru',            '#FF69B4' => 'HotPink',
                '#0000CD' => 'MediumBlue',        '#7B68EE' => 'MediumSlateBlue', '#D2691E' => 'Chocolate',       '#FF7F50' => 'Coral',
                '#0000FF' => 'Blue',              '#7CFC00' => 'LawnGreen',       '#D2B48C' => 'Tan',             '#FF8C00' => 'Darkorange',
                '#006400' => 'DarkGreen',         '#7FFF00' => 'Chartreuse',      '#D3D3D3' => 'LightGrey',       '#FFA07A' => 'LightSalmon',
                '#008000' => 'Green',             '#7FFFD4' => 'Aquamarine',      '#D3D3D3' => 'LightGray',       '#FFA500' => 'Orange',
                '#008080' => 'Teal',              '#800000' => 'Maroon',          '#D87093' => 'PaleVioletRed',   '#FFB6C1' => 'LightPink',
                '#008B8B' => 'DarkCyan',          '#800080' => 'Purple',          '#D8BFD8' => 'Thistle',         '#FFC0CB' => 'Pink',
                '#00BFFF' => 'DeepSkyBlue',       '#808000' => 'Olive',           '#DA70D6' => 'Orchid',          '#FFD700' => 'Gold',
                '#00CED1' => 'DarkTurquoise',     '#808080' => 'Grey',            '#DAA520' => 'GoldenRod',       '#FFDAB9' => 'PeachPuff',
                '#00FA9A' => 'MediumSpringGreen', '#808080' => 'Gray',            '#DC143C' => 'Crimson',         '#FFDEAD' => 'NavajoWhite',
                '#00FF00' => 'Lime',              '#87CEEB' => 'SkyBlue',         '#DCDCDC' => 'Gainsboro',       '#FFE4B5' => 'Moccasin',
                '#00FF7F' => 'SpringGreen',       '#87CEFA' => 'LightSkyBlue',    '#DDA0DD' => 'Plum',            '#FFE4C4' => 'Bisque',
                '#00FFFF' => 'Aqua',              '#8A2BE2' => 'BlueViolet',      '#DEB887' => 'BurlyWood',       '#FFE4E1' => 'MistyRose',
                '#00FFFF' => 'Cyan',              '#8B0000' => 'DarkRed',         '#E0FFFF' => 'LightCyan',       '#FFEBCD' => 'BlanchedAlmond',
                '#191970' => 'MidnightBlue',      '#8B008B' => 'DarkMagenta',     '#E6E6FA' => 'Lavender',        '#FFEFD5' => 'PapayaWhip',
                '#1E90FF' => 'DodgerBlue',        '#8B4513' => 'SaddleBrown',     '#E9967A' => 'DarkSalmon',      '#FFF0F5' => 'LavenderBlush',
                '#20B2AA' => 'LightSeaGreen',     '#8FBC8F' => 'DarkSeaGreen',    '#EE82EE' => 'Violet',          '#FFF5EE' => 'SeaShell',
                '#228B22' => 'ForestGreen',       '#90EE90' => 'LightGreen',      '#EEE8AA' => 'PaleGoldenRod',   '#FFF8DC' => 'Cornsilk',
                '#2E8B57' => 'SeaGreen',          '#9370D8' => 'MediumPurple',    '#F08080' => 'LightCoral',      '#FFFACD' => 'LemonChiffon',
                '#2F4F4F' => 'DarkSlateGrey',     '#9400D3' => 'DarkViolet',      '#F0E68C' => 'Khaki',           '#FFFAF0' => 'FloralWhite',
                '#2F4F4F' => 'DarkSlateGray',     '#98FB98' => 'PaleGreen',       '#F0F8FF' => 'AliceBlue',       '#FFFAFA' => 'Snow',
                '#32CD32' => 'LimeGreen',         '#9932CC' => 'DarkOrchid',      '#F0FFF0' => 'HoneyDew',        '#FFFF00' => 'Yellow',
                '#3CB371' => 'MediumSeaGreen',    '#9ACD32' => 'YellowGreen',     '#F0FFFF' => 'Azure',           '#FFFFE0' => 'LightYellow',
                '#40E0D0' => 'Turquoise',         '#A0522D' => 'Sienna',          '#F4A460' => 'SandyBrown',      '#FFFFF0' => 'Ivory',
                '#4169E1' => 'RoyalBlue',         '#A52A2A' => 'Brown',           '#F5DEB3' => 'Wheat',           '#FFFFFF' => 'White',
                '#4682B4' => 'SteelBlue',         '#A9A9A9' => 'DarkGrey',        '#F5F5DC' => 'Beige',
                '#483D8B' => 'DarkSlateBlue',     '#A9A9A9' => 'DarkGray',        '#F5F5F5' => 'WhiteSmoke',
                '#48D1CC' => 'MediumTurquoise',   '#ADD8E6' => 'LightBlue',       '#F5FFFA' => 'MintCream',
                '#4B0082' => 'Indigo',            '#ADFF2F' => 'GreenYellow',     '#F8F8FF' => 'GhostWhite',
                '#556B2F' => 'DarkOliveGreen',    '#AFEEEE' => 'PaleTurquoise',   '#FA8072' => 'Salmon',
                '#5F9EA0' => 'CadetBlue',         '#B0C4DE' => 'LightSteelBlue',  '#FAEBD7' => 'AntiqueWhite',
                '#6495ED' => 'CornflowerBlue',    '#B0E0E6' => 'PowderBlue',      '#FAF0E6' => 'Linen',
                '#66CDAA' => 'MediumAquaMarine',  '#B22222' => 'FireBrick',       '#FAFAD2' => 'LightGoldenRodYellow',
                '#696969' => 'DimGrey',           '#B8860B' => 'DarkGoldenRod',   '#FDF5E6' => 'OldLace',
                '#696969' => 'DimGray',           '#BA55D3' => 'MediumOrchid',    '#FF0000' => 'Red',
                '#6A5ACD' => 'SlateBlue',         '#BC8F8F' => 'RosyBrown',       '#FF00FF' => 'Fuchsia',
                '#6B8E23' => 'OliveDrab',         '#BDB76B' => 'DarkKhaki',       '#FF00FF' => 'Magenta',
                '#708090' => 'SlateGrey',         '#C0C0C0' => 'Silver',          '#FF1493' => 'DeepPink',
            );
        }
        
        if (isset($colorlist[strtoupper($color)])) $color = $colorlist[strtoupper($color)];
    }
    if($is_background)
    	return "[t-color=$color][t-b]".$str.'[/t-b][/t-color]';
    else 
        return "[t-color=$color]".$str.'[/t-color]';
}

function check_return_user_type($username)
{
	$member_info = IPSMember::load($username, 'all', 'username');
	if(!empty($member_info['member_banned']) || $member_info['member_group_id'] == 5)
	{
		$user_type = 'banned';
	}
	else if($member_info['member_group_id'] == 4)
	{
		$user_type = 'admin';
	}
	else if($member_info['member_group_id'] == 6)
	{
		$user_type = 'mod';
	}
	else
    {
		$user_type = 'normal';
	}
	return $user_type;
}

function post_bbcode_clean($str)
{
	global $board_url,$app_version;
	$str = ipb_convert_bbcode($str);

	$array_reg = array(
		array('reg' => '/\[spoiler\](.*?)\[\/spoiler\]/si','replace' => '[t-spoiler]$1[/t-spoiler]'),
		//array('reg' => '/\[topic=(.*?)\](.*?)\[\/topic\]/si','replace' => '[t-topic=$1]$2[t-topic]'),
		//array('reg' => '/\[post=(.*?)\](.*?)\[\/post\]/si','replace' => '[t-post=$1]$2[t-post]'),
		array('reg' => '/\[snapback\](.*?)\[\/snapback\]/si','replace' => '[t-post=$1]Snapback[/t-post]'),
		array('reg' => '/\[color=(.*?)\](.*?)\[\/color\]/sei','replace' => "mobi_color_convert('$1','$2' ,false)"),
		array('reg' => '/\[background=(.*?)\](.*?)\[\/background\]/sei','replace' => "mobi_color_convert('$1','$2' ,true)"),
		array('reg' => '/\[xml\](.*?)\[\/xml\]/si','replace' => '[quote]$1[/quote]'),
		array('reg' => '/\[html\](.*?)\[\/html\]/si','replace' => '[quote]$1[/quote]'),
		array('reg' => '/\[sql\](.*?)\[\/sql\]/si','replace' => '[quote]$1[/quote]'),
		array('reg' => '/\[entry=(.*?)\](.*?)\[\/entry\]/si','replace' => '[url='.$board_url.'/index.php?app=blog&showentry=$1]$2[/url]'),
		array('reg' => '/\[blog=(.*?)\](.*?)\[\/blog\]/si','replace' => '[url='.$board_url.'/index.php?app=blog&showblog=$1]$2[/url]'),
		array('reg' => '/\[extract\](.*?)\[\/extract\]/si','replace' => '$1'),
		array('reg' => '/\[member=(.*?)\]/si',replace => '[t-member=$1]'),
		array('reg' => '/\[acronym=\'(.*?)\'\](.*?)\[\/acronym\]/si','replace' => '$1($2)'),
		array('reg' => '/\[hr\]/si','replace' => "----------------------------------------------------------\n"),
		array('reg' => '/\[left\](.*?)\[\/left\]/si',replace=>"$1"),
		array('reg' => '/\[right\](.*?)\[\/right\]/si',replace=>'$1'),
		array('reg' => '/\[center\](.*?)\[\/center\]/si',replace=>'$1'),
	);
	foreach ($array_reg as $arr)
	{
		$str = preg_replace($arr['reg'], $arr['replace'], $str);
	}
	if($app_version >='3.4.0')
	{
		$str = preg_replace('/\[quote (.*?)\]/si', '[t-quote]', $str);
		$str = str_replace('[/quote]', '[/t-quote]', $str);
	}
	return $str;
}


/**
 * Get content from remote server
 *
 * @param string $url      NOT NULL          the url of remote server, if the method is GET, the full url should include parameters; if the method is POST, the file direcotry should be given.
 * @param string $holdTime [default 0]       the hold time for the request, if holdtime is 0, the request would be sent and despite response.
 * @param string $error_msg                  return error message
 * @param string $method   [default GET]     the method of request.
 * @param string $data     [default array()] post data when method is POST.
 *
 * @exmaple: getContentFromRemoteServer('http://push.tapatalk.com/push.php', 0, $error_msg, 'POST', $ttp_post_data)
 * @return string when get content successfully|false when the parameter is invalid or connection failed.
*/
function getContentFromRemoteServer($url, $holdTime = 0, &$error_msg, $method = 'GET', $data = array())
{
    //Validate input.
    $vurl = parse_url($url);
    if ($vurl['scheme'] != 'http')
    {
        $error_msg = 'Error: invalid url given: '.$url;
        return false;
    }
    if($method != 'GET' && $method != 'POST')
    {
        $error_msg = 'Error: invalid method: '.$method;
        return false;//Only POST/GET supported.
    }
    if($method == 'POST' && empty($data))
    {
        $error_msg = 'Error: data could not be empty when method is POST';
        return false;//POST info not enough.
    }

    if(!empty($holdTime) && function_exists('file_get_contents') && $method == 'GET')
    {
        $response = file_get_contents($url);
    }
    else if (@ini_get('allow_url_fopen'))
    {
        if(empty($holdTime))
        {
            // extract host and path:
            $host = $vurl['host'];
            $path = $vurl['path'];

            if($method == 'POST')
            {
                $fp = fsockopen($host, 80, $errno, $errstr, 5);

                if(!$fp)
                {
                    $error_msg = 'Error: socket open time out or cannot connet.';
                    return false;
                }

                $data =  http_build_query($data);

                fputs($fp, "POST $path HTTP/1.1\r\n");
                fputs($fp, "Host: $host\r\n");
                fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
                fputs($fp, "Content-length: ". strlen($data) ."\r\n");
                fputs($fp, "Connection: close\r\n\r\n");
                fputs($fp, $data);
                fclose($fp);
            }
            else
            {
                $error_msg = 'Error: 0 hold time for get method not supported.';
                return false;
            }
        }
        else
        {
            if($method == 'POST')
            {
                $params = array('http' => array(
                    'method' => 'POST',
                    'content' => http_build_query($data, '', '&'),
                ));
                $ctx = stream_context_create($params);
                $old = ini_set('default_socket_timeout', $holdTime);
                $fp = @fopen($url, 'rb', false, $ctx);
            }
            else
            {
                $fp = @fopen($url, 'rb', false);
            }
            if (!$fp)
            {
                $error_msg = 'Error: fopen failed.';
                return false;
            }
            ini_set('default_socket_timeout', $old);
            stream_set_timeout($fp, $holdTime);
            stream_set_blocking($fp, 0);

            $response = @stream_get_contents($fp);
        }
    }
    elseif (function_exists('curl_init'))
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if($method == 'POST')
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        if(empty($holdTime))
        {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT,1);
        }
        $response = curl_exec($ch);
        curl_close($ch);
    }
    else
    {
        $error_msg = 'CURL is disabled and PHP option "allow_url_fopen" is OFF. You can enable CURL or turn on "allow_url_fopen" in php.ini to fix this problem.';
        return false;
    }
    return $response;
}

function tt_register_verify($tt_token,$tt_code)
{
	global $settings;
	if(empty($settings['tapatalk_push_key']))
	{
		return false;
	}
	$url = "http://directory.tapatalk.com/au_reg_verify.php?token=".$tt_token."&code=".$tt_code."&key=" . $settings['tapatalk_push_key'];
	$error_msg = '';
	$response = getContentFromRemoteServer($url, 10 , $error_msg);
	if(!empty($error_msg))
	{
		get_error($error_msg);
	}
	if(empty($response))
	{
		get_error("Contect timeout , please try again");
	}
	$result = json_decode($response);
	if($result->result === false)
	{
		return false;
	}
	if(!empty($result->email))
	{
		return $result->email;
	}
	return false;
}

function ipb_convert_bbcode($message)
{
	global $app_version;
	if($app_version >= '3.4.0')
	{
		/* Convert to BBCode for non JS peoples */
		/* Grab the parser file */
		//require_once( IPS_ROOT_PATH . 'sources/classes/text/parser.php');
		if ( ! class_exists( 'class_text_parser_legacy' ) )
		{
			if(! class_exists( 'classes_text_parser' ) )
			{
				require_once( IPS_ROOT_PATH . 'sources/classes/text/parser.php');
			}
			require_once( IPS_ROOT_PATH . 'sources/classes/text/parser/legacy.php');
		}
		$parser        = new class_text_parser_legacy();
		$message = $parser->postEditor( $message );
	}
	
	return $message;
}
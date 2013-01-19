<?php

class tapatalk_detected_js
{
    public function getOutput()
    {
    	$settings = ipsRegistry::$settings;
        $board_url = $this->registry->output->isHTTPS ? $settings['board_url_https'] : $settings['board_url'];
        $tapatalkdir = isset($settings['tapatalk_directory']) && !empty($settings['tapatalk_directory'])
                       ? $settings['tapatalk_directory'] : 'mobiquo';
        $jsfilename = 'tapatalkdetect';
        $tapatalkdirset = "<script type='text/javascript'>
        var tapatalkdir = '$tapatalkdir';
        var tapatalk_ipad_msg = '{$settings['tapatalk_ipad_msg']}';
        var tapatalk_ipad_url  = '{$settings['tapatalk_ipad_url']}';
        var tapatalk_iphone_msg = '{$settings['tapatalk_iphone_msg']}';
        var tapatalk_iphone_url  = '{$settings['tapatalk_iphone_url']}';
        var tapatalk_android_msg = '{$settings['tapatalk_android_msg']}';
        var tapatalk_android_url  = '{$settings['tapatalk_android_url']}';
        var tapatalk_kindle_msg = '{$settings['tapatalk_kindle_msg']}';
        var tapatalk_kindle_url  = '{$settings['tapatalk_kindle_url']}';
        var tapatalk_chrome_enable = '{$settings['tapatalk_chrome_notifier']}';
        </script>\n";
        
        return "{$tapatalkdirset}<script type='text/javascript' src='{$board_url}/{$tapatalkdir}/{$jsfilename}.js'></script>";
    }
}
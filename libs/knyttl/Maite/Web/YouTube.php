<?php
/**
 * YouTube utils library.
 *
 * This source file is subject to the "New BSD License".
 *
 * @author     Vojtěch Knyttl
 * @copyright  Copyright (c) 2010 Vojtěch Knyttl
 * @license    New BSD License
 * @link       http://knyt.tl/
 */

namespace Maite\Web;

use Nette;



class YouTube {

    private static function asc($str) {
        return preg_replace('#\s+#', '', strtolower(\Maite\Utils\Strings::toAscii($str)));
    }



    public static function find($query) {
        $vids = json_decode(Utils::get('http://gdata.youtube.com/feeds/api/videos/?v=2&alt=jsonc&q='.urlencode($query)));
        $videos = array();

        if (!empty($vids->data->items)) {
            foreach ($vids->data->items as $vid) {
                if ($vid->accessControl->embed != "allowed") {
                    continue;
                }
    
                if ($vid->category != 'Music') {
                    continue;
                }
    
                
                if (stripos(self::asc($vid->title.$vid->description), self::asc($query)) === false) {
                    continue;
                }
                $videos[] = @array('id' => $vid->id, 'name' => $vid->title, 
                    'counts' => array('like' => $vid->likeCount, 'rating' => $vid->ratingCount, 
                        'view' => $vid->viewCount, 'favorite' => $vid->favoriteCount, 'comment' => $vid->commentCount));
            }
        }
        return $videos;
    }
}
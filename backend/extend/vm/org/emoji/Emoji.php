<?php
/**
 * Created by Rocareer Team.
 * User: albert
 * Mail: albert@rocareer.com
 * Date: 2017/10/25
 * Time: 下午8:45
 */

namespace vm\org\emoji;


class Emoji
{
    protected $emojimaps;

    public function __construct()
    {
        $this->emojimaps = $e = include __DIR__ . '/config.php';
        $this->emojimaps['html_to_unified'] = array_flip($e['unified_to_html']);

    }

    public function emoji($data)
    {
        $data = $this->emoji_docomo_to_unified($data);   # DoCoMo devices
        $data = $this->emoji_kddi_to_unified($data);     # KDDI & Au devices
        $data = $this->emoji_softbank_to_unified($data); # Softbank & pre-iOS6 Apple devices
        $data = $this->emoji_google_to_unified($data);   # Google Android devices
//
//
//# when sending data back to mobile devices, you can
//# convert back to their native format.
//
//        $data = $this->emoji_unified_to_docomo($data);   # DoCoMo devices
//        $data = $this->emoji_unified_to_kddi($data);     # KDDI & Au devices
//        $data = $this->emoji_unified_to_softbank($data); # Softbank & pre-iOS6 Apple devices
//        $data = $this->emoji_unified_to_google($data);   # Google Android devices
////

# when displaying data to anyone else, you can use HTML
# to format the emoji.

        $data = $this->emoji_unified_to_html($data);

# if you want to use an editor(i.e:wysiwyg) to create the content,
# you can use html_to_unified to store the unified value.

//        $data = $this->emoji_html_to_unified($this->emoji_unified_to_html($data));
        return $data;
    }

    public function emoji_docomo_to_unified($text)
    {
        return $this->emoji_convert($text, 'docomo_to_unified');
    }

    public function emoji_kddi_to_unified($text)
    {
        return $this->emoji_convert($text, 'kddi_to_unified');
    }

    public function emoji_softbank_to_unified($text)
    {
        return $this->emoji_convert($text, 'softbank_to_unified');
    }

    public function emoji_google_to_unified($text)
    {
        return $this->emoji_convert($text, 'google_to_unified');
    }

    #
    # functions to convert unified data into an outgoing format
    #

    public function emoji_unified_to_docomo($text)
    {
        return $this->emoji_convert($text, 'unified_to_docomo');
    }

    public function emoji_unified_to_kddi($text)
    {
        return $this->emoji_convert($text, 'unified_to_kddi');
    }

    public function emoji_unified_to_softbank($text)
    {
        return $this->emoji_convert($text, 'unified_to_softbank');
    }

    public function emoji_unified_to_google($text)
    {
        return $this->emoji_convert($text, 'unified_to_google');
    }

    public function emoji_unified_to_html($text)
    {
        return $this->emoji_convert($text, 'unified_to_html');
    }

    public function emoji_html_to_unified($text)
    {
        return $this->emoji_convert($text, 'html_to_unified');
    }


    public function emoji_convert($text, $map)
    {

        return str_replace(array_keys($this->emojimaps[$map]), $this->emojimaps[$map], $text);
    }

    public function emoji_get_name($unified_cp)
    {

        return $this->emojimaps['names'][$unified_cp] ? $this->emojimaps['names'][$unified_cp] : '?';
    }
}



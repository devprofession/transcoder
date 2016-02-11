<?php

namespace Ddeboer\Transcoder;

use Ddeboer\Transcoder\Exception\ExtensionMissingException;
use Ddeboer\Transcoder\Exception\UnsupportedEncodingException;

class Transcoder implements TranscoderInterface
{
    private static $chain;
    
    /**
     * @var TranscoderInterface[]
     */
    private $transcoders = [];

    /**
     * Available in https://wiki.mozilla.org/I18n:Charset_Aliases
     * @var array
     */
    protected static $charsetMap = [
        '5601' => 'EUC-KR',
        '646' => 'us-ascii',
        '850' => 'IBM850',
        '852' => 'IBM852',
        '855' => 'IBM855',
        '857' => 'IBM857',
        '862' => 'IBM862',
        '864' => 'IBM864',
        '864i' => 'IBM864i',
        '866' => 'IBM866',
        'ansi-1251' => 'windows-1251',
        'ansi_x3.4-1968' => 'us-ascii',
        'arabic' => 'ISO-8859-6',
        'ascii' => 'us-ascii',
        'asmo-708' => 'ISO-8859-6',
        'chinese' => 'GB2312',
        'cns11643' => 'x-euc-tw',
        'cp1250' => 'windows-1250',
        'cp1251' => 'windows-1251',
        'cp1252' => 'windows-1252',
        'cp1253' => 'windows-1253',
        'cp1254' => 'windows-1254',
        'cp1255' => 'windows-1255',
        'cp1256' => 'windows-1256',
        'cp1257' => 'windows-1257',
        'cp1258' => 'windows-1258',
        'cp819' => 'ISO-8859-1',
        'cp850' => 'IBM850',
        'cp852' => 'IBM852',
        'cp855' => 'IBM855',
        'cp857' => 'IBM857',
        'cp862' => 'IBM862',
        'cp864' => 'IBM864',
        'cp864i' => 'IBM864i',
        'cp-866' => 'IBM866',
        'cp866' => 'IBM866',
        'csbig5' => 'Big5',
        'cseucjpkdfmtjapanese' => 'EUC-JP',
        'csgb2312' => 'GB2312',
        'csIBM850' => 'IBM850',
        'csIBM852' => 'IBM852',
        'csIBM855' => 'IBM855',
        'csIBM857' => 'IBM857',
        'csIBM862' => 'IBM862',
        'csIBM864' => 'IBM864',
        'csibm864i' => 'IBM864i',
        'csIBM866' => 'IBM866',
        'csiso103t618bit' => 'T.61-8bit',
        'csiso111ecmacyrillic' => 'ISO-IR-111',
        'csiso2022jp2' => 'ISO-2022-JP',
        'csiso2022jp' => 'ISO-2022-JP',
        'csiso2022kr' => 'ISO-2022-KR',
        'csiso58gb231280' => 'GB2312',
        'csiso88596e' => 'ISO-8859-6-E',
        'csiso88596i' => 'ISO-8859-6-I',
        'csiso88598e' => 'ISO-8859-8-E',
        'csiso88598i' => 'ISO-8859-8-I',
        'csisolatin1' => 'ISO-8859-1',
        'csisolatin2' => 'ISO-8859-2',
        'csisolatin3' => 'ISO-8859-3',
        'csisolatin4' => 'ISO-8859-4',
        'csisolatin5' => 'ISO-8859-9',
        'csisolatin6' => 'ISO-8859-10',
        'csisolatinarabic' => 'ISO-8859-6',
        'csisolatincyrillic' => 'ISO-8859-5',
        'csisolatingreek' => 'ISO-8859-7',
        'csisolatinhebrew' => 'ISO-8859-8',
        'csksc56011987' => 'EUC-KR',
        'csMacintosh' => 'x-mac-roman',
        'csshiftjis' => 'Shift_JIS',
        'csueckr' => 'EUC-KR',
        'csunicode11' => 'UTF-16BE',
        'csunicode11utf7' => 'UTF-7',
        'csunicodeascii' => 'UTF-16BE',
        'csunicodelatin1' => 'UTF-16BE',
        'csunicode' => 'UTF-16BE',
        'csviqr' => 'VIQR',
        'csviscii' => 'VISCII',
        'cyrillic' => 'ISO-8859-5',
        'ecma-114' => 'ISO-8859-6',
        'ecma-118' => 'ISO-8859-7',
        'ecma-cyrillic' => 'ISO-IR-111',
        'elot_928' => 'ISO-8859-7',
        'gb_2312-80' => 'GB2312',
        'gbk' => 'x-gbk',
        'greek8' => 'ISO-8859-7',
        'greek' => 'ISO-8859-7',
        'hebrew' => 'ISO-8859-8',
        'ibm819' => 'ISO-8859-1',
        'ibm874' => 'windows-874',
        'iso-10646-j-1' => 'UTF-16BE',
        'iso-10646-ucs-2' => 'UTF-16BE',
        'iso-10646-ucs-4' => 'UTF-32BE',
        'iso-10646-ucs-basic' => 'UTF-16BE',
        'iso-10646-unicode-latin1' => 'UTF-16BE',
        'iso-10646' => 'UTF-16BE',
        'iso-2022-cn-ext' => 'ISO-2022-CN',
        'iso-2022-jp-2' => 'ISO-2022-JP',
        'iso-ir-100' => 'ISO-8859-1',
        'iso-ir-101' => 'ISO-8859-2',
        'iso-ir-103' => 'T.61-8bit',
        'iso-ir-109' => 'ISO-8859-3',
        'iso-ir-110' => 'ISO-8859-4',
        'iso-ir-126' => 'ISO-8859-7',
        'iso-ir-127' => 'ISO-8859-6',
        'iso-ir-138' => 'ISO-8859-8',
        'iso-ir-144' => 'ISO-8859-5',
        'iso-ir-148' => 'ISO-8859-9',
        'iso-ir-149' => 'EUC-KR',
        'iso-ir-157' => 'ISO-8859-10',
        'iso-ir-58' => 'GB2312',
        'korean' => 'EUC-KR',
        'ks_c_5601-1987' => 'x-windows-949',
        'ks_c_5601-1989' => 'EUC-KR',
        'ksc_5601' => 'EUC-KR',
        'ksc5601' => 'EUC-KR',
        'l1' => 'ISO-8859-1',
        'l2' => 'ISO-8859-2',
        'l3' => 'ISO-8859-3',
        'l4' => 'ISO-8859-4',
        'l5' => 'ISO-8859-9',
        'l6' => 'ISO-8859-10',
        'latin1' => 'ISO-8859-1',
        'latin2' => 'ISO-8859-2',
        'latin3' => 'ISO-8859-3',
        'latin4' => 'ISO-8859-4',
        'latin5' => 'ISO-8859-9',
        'latin6' => 'ISO-8859-10',
        'macintosh' => 'x-mac-roman',
        'mac' => 'x-mac-roman',
        'ms_kanji' => 'Shift_JIS',
        'sun_eu_greek' => 'ISO-8859-7',
        't.61' => 'T.61-8bit',
        'unicode-1-1-utf-7' => 'UTF-7',
        'unicode-1-1-utf-8' => 'UTF-8',
        'unicode-2-0-utf-7' => 'UTF-7',
        'visual' => 'ISO-8859-8',
        'windows-31j' => 'Shift_JIS',
        'x-cp1250' => 'windows-1250',
        'x-cp1251' => 'windows-1251',
        'x-cp1252' => 'windows-1252',
        'x-cp1253' => 'windows-1253',
        'x-cp1254' => 'windows-1254',
        'x-cp1255' => 'windows-1255',
        'x-cp1256' => 'windows-1256',
        'x-cp1257' => 'windows-1257',
        'x-cp1258' => 'windows-1258',
        'x-euc-jp' => 'EUC-JP',
        'x-iso-10646-ucs-2-be' => 'UTF-16BE',
        'x-iso-10646-ucs-2-le' => 'UTF-16LE',
        'x-iso-10646-ucs-4-be' => 'UTF-32BE',
        'x-iso-10646-ucs-4-le' => 'UTF-32LE',
        'x-sjis' => 'Shift_JIS',
        'x-unicode-2-0-utf-7' => 'UTF-7',
        'x-x-big5' => 'Big5',
        'zh_cn.euc' => 'GB2312',
        'zh_tw-big5' => 'Big5',
        'zh_tw-euc' => 'x-euc-tw',
    ];
    
    public function __construct(array $transcoders)
    {
        $this->transcoders = $transcoders;
    }

    /**
     * {@inheritdoc}
     */
    public function transcode($string, $from = null, $to = null)
    {
        foreach ($this->transcoders as $transcoder) {
            try {
                return $transcoder->transcode($string, $from, $to);
            } catch (UnsupportedEncodingException $e) {
                // Try again, now with the alias
                if (!empty(static::$charsetMap[$from])) {
                    try {
                        return $transcoder->transcode($string, static::$charsetMap[$from], $to);
                    } catch (UnsupportedEncodingException $e) {
                        // Ignore as long as the fallback transcoder is all right
                    }
                }
            }
        }
        
        throw $e;
    }

    /**
     * Create a transcoder
     * 
     * @param string $defaultEncoding
     *
     * @return TranscoderInterface
     *
     * @throws ExtensionMissingException
     */
    public static function create($defaultEncoding = 'UTF-8')
    {
        if (isset(self::$chain[$defaultEncoding])) {
            return self::$chain[$defaultEncoding];
        }
        
        $transcoders = [];
        
        try {
            $transcoders[] = new MbTranscoder($defaultEncoding);
        } catch (ExtensionMissingException $mb) {
            // Ignore missing mbstring extension; fall back to iconv
        }

        try {
            $transcoders[] = new IconvTranscoder($defaultEncoding);
        } catch (ExtensionMissingException $iconv) {
            // Neither mbstring nor iconv
            throw $iconv;
        }
        
        self::$chain[$defaultEncoding] = new self($transcoders);

        return self::$chain[$defaultEncoding];
    }
}

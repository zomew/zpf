<?php
/**
 * Created by PhpStorm.
 * User: Jamers
 * Date: 2018/11/12
 * Time: 15:13
 * File: Cls_Base91.php
 */

namespace ZF;

/**
 * 非常用编码方式Base16/32/91
 *
 * @author Jamers <jamersnox@zomew.net>
 * @license https://opensource.org/licenses/GPL-3.0 GPL
 * @since 2018.11.12
 * @copyright 2005-2006 Joachim Henke
 *
 * @see Base91 http://base91.sourceforge.net/
 * @see Base32 https://github.com/Katoga/allyourbase/blob/master/src/Allyourbase/Base32.php

 * Class Base91
 * @package ZF
 */
class BaseCode {
    /**
     * Base91编码表
     * @var array
     */
    private static $b91_enctab = array(
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
        'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
        'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '!', '#', '$',
        '%', '-', '(', ')', '*', '+', ',', '.', '/', ':', ';', '<', '=',
        '>', '?', '@', '[', ']', '^', '_', '`', '{', '|', '}', '~', '"'
    );

    /**
     * Base91解码表
     * @var array
     */
    private static $b91_dectab;

    /**
     * Base16编码表（已弃用）
     * @var array
     * @deprecated
     */
    private static $b16_enctab = array(
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F',
    );

    /**
     * 初始化Base91解码表
     * @since 2018.11.13
     */
    private static function init() {
        if (!self::$b91_dectab) self::$b91_dectab = array_flip(self::$b91_enctab);
    }

    /**
     * Base91解码
     * @since 2018.11.13
     *
     * @param $d
     * @return string
     */
    public static function base91_decode($d)
    {
        self::init();
        $l = strlen($d);
        $v = -1;
        $b = 0;
        $o = '';
        $n = 0;
        for ($i = 0; $i < $l; ++$i) {
            $c = self::$b91_dectab[$d{$i}];
            if (!isset($c))
                continue;
            if ($v < 0)
                $v = $c;
            else {
                $v += $c * 91;
                $b |= $v << $n;
                $n += ($v & 8191) > 88 ? 13 : 14;
                do {
                    $o .= chr($b & 255);
                    $b >>= 8;
                    $n -= 8;
                } while ($n > 7);
                $v = -1;
            }
        }
        if ($v + 1)
            $o .= chr(($b | $v << $n) & 255);
        return $o;
    }

    /**
     * Base91编码
     * @since 2018.11.13
     *
     * @param $d
     * @return string
     */
    public static function base91_encode($d)
    {
        self::init();
        $b = 0;
        $n = 0;
        $o = '';
        $l = strlen($d);
        for ($i = 0; $i < $l; ++$i) {
            $b |= ord($d{$i}) << $n;
            $n += 8;
            if ($n > 13) {
                $v = $b & 8191;
                if ($v > 88) {
                    $b >>= 13;
                    $n -= 13;
                } else {
                    $v = $b & 16383;
                    $b >>= 14;
                    $n -= 14;
                }
                $o .= self::$b91_enctab[$v % 91] . self::$b91_enctab[$v / 91];
            }
        }
        if ($n) {
            $o .= self::$b91_enctab[$b % 91];
            if ($n > 7 || $b > 90)
                $o .= self::$b91_enctab[$b / 91];
        }
        return $o;
    }

    /**
     * Base16编码
     * @since 2018.11.13
     *
     * @param $str
     * @return array
     */
    public static function base16_encode($str) {
        /*
        //老算法，意义不大，直接用bin2hex处理
        $i = 0;
        $len = strlen($str);
        $ret = '';
        while($i < $len) {
            $v = ord($str[$i]);
            $ret .= self::$b16_enctab[($v & 0xF0) >> 4];
            $ret .= self::$b16_enctab[$v & 0x0F];
            $i ++;
        }
        */
        $ret = bin2hex($str);
        return $ret;
    }

    /**
     * Base16解码
     * @since 2018.11.13
     *
     * @param $str
     * @return bool|string
     */
    public static function base16_decode($str) {
        /*
        //老算法，意义不大，直接用hex2bin处理
        $i = 0;
        $ret = '';
        $len = (strlen($str) / 2) * 2;
        while($i < $len) {
            $ret .= chr((ord($str[$i++]) << 4) | ord($str[$i++]));
        }
        */
        $ret = hex2bin($str);
        return $ret;
    }


    /**
     * A-Z, 2-7
     * New RFC that obsoleted RFC3548, uses the same alphabet.
     *
     * @var int
     */
    const RFC4648 = 1;
    /**
     * 0-9, A-V
     * "Extended hex" or "base32hex"
     *
     * @var int
     */
    const RFC2938 = 2;
    /**
     * 0-9, A-Z without I, L, O, U
     *
     * @link http://www.crockford.com/wrmg/base32.html
     * @var int
     */
    const CROCKFORD = 3;
    /**
     * @var string
     */
    const PAD_CHAR = '=';
    /**
     * @var int
     */
    const ENCODE = 1;
    /**
     * @var int
     */
    const DECODE = 2;
    /**
     * @var int
     */
    protected static $type = self::RFC4648;
    /**
     * @var array
     */
    protected static $alphabet = [
        self::RFC4648 => [
            self::ENCODE => [],
            self::DECODE => [],
        ],
        self::RFC2938 => [
            self::ENCODE => [],
            self::DECODE => [],
        ],
        self::CROCKFORD => [
            self::ENCODE => [],
            self::DECODE => [],
        ],
    ];
    /**
     * @param int $type = self::RFC4648
     */
    public function __construct(int $type = self::RFC4648)
    {
        self::$type = $type;
    }
    /**
     * @param string $input binary string
     * @return string ascii string
     */
    public static function base32_encode(string $input): string
    {
        $output = '';
        if ($input != '') {
            $alphabet = self::getEncodingAlphabet(self::$type);
            // create binary represantation of input string
            $binStr = '';
            foreach (str_split($input) as $char) {
                // append 8 bits of source string
                // padding zeros needed for chars with ASCII position < 64 (up to '?')
                // or portions of splitted multibyte chars
                $binStr .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
            }
            // pad binary string, its length has to be divisible by 5
            $binStr = self::pad($binStr, 5, '0');
            // split binary string to 5bit chunks
            $binArr = explode(' ', trim(chunk_split($binStr, 5, ' ')));
            // encode
            foreach ($binArr as $binChar) {
                $output .= $alphabet[bindec($binChar)];
            }
            // pad output, its length has to be divisible by 8
            $output = self::pad($output, 8, self::PAD_CHAR);
        }
        return $output;
    }
    /**
     * @param string $input ascii string
     * @return string binary string
     */
    public static function base32_decode(string $input): string
    {
        $output = '';
        if ($input != '') {
            $alphabet = self::getDecodingAlphabet(self::$type);
            // convert input to uppercase and remove trailing padding chars
            $input = rtrim(strtoupper($input), self::PAD_CHAR);
            $binStr = '';
            foreach (str_split($input) as $ch) {
                if (!isset($alphabet[$ch])) {
                    return '';
                }
                // append 5bit binary representation of encoded char
                $binStr .= str_pad((decbin($alphabet[$ch])), 5, '0', STR_PAD_LEFT);
            }
            // trim the right side of binary string, its length has to be divisible by 8
            $binStr = self::trim($binStr, 8);
            $binArr = explode(' ', trim(chunk_split($binStr, 8, ' ')));
            foreach ($binArr as $bin) {
                $output .= chr(bindec($bin));
            }
        }
        return $output;
    }
    /**
     * Pads $string on right side with $char to length divisible by $factor
     *
     * @param string $string
     * @param int $factor
     * @param string $char
     * @return string
     */
    protected static function pad(string $string, int $factor, string $char): string
    {
        $output = $string;
        $length = strlen($string);
        $modulo = $length % $factor;
        if ($modulo != 0) {
            $outputPaddedLength = $length + ($factor - $modulo);
            $output = str_pad($output, $outputPaddedLength, $char, STR_PAD_RIGHT);
        }
        return $output;
    }
    /**
     * Trims $char from right side of $string to length divisible by $factor
     *
     * @param string $string
     * @param int $factor
     * @return string
     */
    protected static function trim(string $string, int $factor): string
    {
        $output = $string;
        $length = strlen($string);
        $modulo = $length % $factor;
        if ($modulo != 0) {
            $outputTrimmedLength = $length - $modulo;
            $output = substr($output, 0, $outputTrimmedLength);
        }
        return $output;
    }
    /**
     * @param int $type
     * @return array
     */
    protected static function getEncodingAlphabet(int $type): array
    {
        return self::getAlphabet($type, self::ENCODE);
    }
    /**
     * @param int $type
     * @return array
     */
    protected static function getDecodingAlphabet(int $type): array
    {
        return self::getAlphabet($type, self::DECODE);
    }
    /**
     * @param int $type
     * @param int $mode
     * @return array
     * @throws \InvalidArgumentException
     */
    protected static function getAlphabet(int $type, int $mode): array
    {
        if (!isset(self::$alphabet[$type])) {
            throw new \InvalidArgumentException(sprintf('Wrong alphabet requested: "%s"!', $type));
        }
        if (!isset(self::$alphabet[$type][$mode])) {
            throw new \InvalidArgumentException(sprintf('Wrong mode requested: "%s"!', $mode));
        }
        if (empty(self::$alphabet[$type][$mode])) {
            // generate the requested alphabet
            switch ($type) {
                case self::RFC4648:
                    $alphabet = array_merge(
                        range('A', 'Z'),
                        ['2', '3', '4', '5', '6', '7']
                    );
                    self::$alphabet[$type][self::ENCODE] = $alphabet;
                    self::$alphabet[$type][self::DECODE] = array_flip($alphabet);
                    break;
                case self::RFC2938:
                    $alphabet = array_merge(
                        ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                        range('A', 'V')
                    );
                    self::$alphabet[$type][self::ENCODE] = $alphabet;
                    self::$alphabet[$type][self::DECODE] = array_flip($alphabet);
                    break;
                case self::CROCKFORD:
                    $alphabet = array_merge(
                        ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                        array_diff(
                            range('A', 'Z'),
                            ['I', 'L', 'O', 'U']
                        )
                    );
                    self::$alphabet[$type][self::ENCODE] = $alphabet;
                    $decodeCrockford = array_merge(
                        array_flip($alphabet),
                        [
                            'I' => 1,
                            'L' => 1,
                            'O' => 0
                        ]
                    );
                    $lowercase = range('a', 'z');
                    unset($lowercase[20]);
                    foreach ($lowercase as $ch) {
                        $decodeCrockford[$ch] = $decodeCrockford[strtoupper($ch)];
                    }
                    self::$alphabet[$type][self::DECODE] = $decodeCrockford;
                    break;
            }
        }
        return self::$alphabet[$type][$mode];
    }
}
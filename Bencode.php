<?php

namespace PureBencode;

class Bencode {
    /**
     * @param int|string|array $value
     * @throws Exception
     * @return string
     */
    static function encode($value) {
        if (is_array($value)) {
            if (self::isAssoc($value)) {
                ksort($value, SORT_STRING);
                $result = '';

                foreach ($value as $k => $v)
                    $result .= self::encode("$k") . self::encode($v);

                return "d{$result}e";
            } else {
                $result = '';

                foreach ($value as $v)
                    $result .= self::encode($v);

                return "l{$result}e";
            }
        } else if (is_int($value)) {
            return "i{$value}e";
        } else if (is_string($value)) {
            return strlen($value) . ":$value";
        } else {
            $type = gettype($value);

            throw new Exception("Bencode supports only integers, strings and arrays. $type given.");
        }
    }

    private static function isAssoc(array $array) {
        $i = 0;

        foreach ($array as $k => $v)
            if ($k !== $i++)
                return true;

        return false;
    }

    /**
     * @param string $string Bencode string
     * @throws Exception
     * @return int|string|array
     */
    static function decode($string) {
        $parser = new BencodeParser($string);
        return $parser->decode();
    }
}

class BencodeParser {
    private $string, $pos = 0;

    function __construct($string) {
        $this->string = $string;
    }

    function decode() {
        switch ($this->read()) {
            case 'i':
                $this->seek();
                return $this->readInt('e');
            case 'l':
                $this->seek();
                $result = array();

                while ($this->read() !== 'e')
                    $result[] = $this->decode();

                $this->seek();
                return $result;
            case 'd':
                $this->seek();
                $result = array();

                while ($this->read() !== 'e')
                    $result[$this->decode()] = $this->decode();

                $this->seek();
                return $result;
            default:
                $len = $this->readInt(':');
                if ($len < 0)
                    throw new Exception("Length of string ($len) must not be negative");
                return $this->remove($len);
        }
    }

    private function read($len = 1) {
        $result = (string)substr($this->string, $this->pos, $len);

        $len2 = strlen($result);
        if ($len !== $len2)
            throw new Exception("$len bytes expected but only $len2 bytes remain");

        return $result;
    }

    private function seek($len = 1) {
        $this->pos += $len;
    }

    private function readInt($delimiter) {
        $result = $this->remove($this->find($delimiter));
        $this->seek(strlen($delimiter));
        $int = (int)$result;
        if ("$int" !== $result)
            throw new Exception("Invalid integer: $result");
        return $int;
    }

    private function remove($len) {
        $string = $this->read($len);
        $this->seek($len);
        return $string;
    }

    private function find($needle) {
        $pos = strpos($this->string, $needle, $this->pos);
        if ($pos === false)
            throw new Exception("'$needle' not found");
        return $pos - $this->pos;
    }
}

class Exception extends \Exception {
}

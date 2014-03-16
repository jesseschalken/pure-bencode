<?php

namespace PureBencode;

class BencodeTest extends \PHPUnit_Framework_TestCase {
    function testNumbers() {
        self::assertBencode(0, 'i0e');
        self::assertBencode(34957683495, 'i34957683495e');
        self::assertBencode(-87, 'i-87e');
        self::assertBencode('123456789012', '12:123456789012');
    }

    static function assertBencode($value, $bencode) {
        self::assertEquals($bencode, Bencode::encode($value));
        self::assertEquals($value, Bencode::decode($bencode));
    }

    function testTorrentFile() {
        $bencode = <<<'s'
d8:announce39:http://torrent.foobar.baz:9374/announce13:announce-listll39:http://torrent.foobar.baz:9374/announceel44:http://ipv6.torrent.foobar.baz:9374/announceee7:comment31:My torrent comment goes here :)13:creation datei1382003607e4:infod6:lengthi925892608e4:name13:some-file.boo12:piece lengthi524288e6:pieces0:ee
s;
        $torrent = array(
            'announce' => 'http://torrent.foobar.baz:9374/announce',
            'announce-list' => array(
                array('http://torrent.foobar.baz:9374/announce'),
                array('http://ipv6.torrent.foobar.baz:9374/announce'),
            ),
            'comment' => 'My torrent comment goes here :)',
            'creation date' => 1382003607,
            'info' => array(
                'length' => 925892608,
                'name' => 'some-file.boo',
                'piece length' => 524288,
                'pieces' => '',
            ),
        );

        self::assertBencode($torrent, $bencode);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid integer: -0
     */
    function testNegativeZero() {
        Bencode::decode('i-0e');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Length of string (-9) must not be negative
     */
    function testNegativeStringLength() {
        Bencode::decode("-9:string");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid integer: 8f
     */
    function testInvalidInteger() {
        Bencode::decode('i8fe');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage 1 bytes expected but only 0 bytes remain
     */
    function testMissingE() {
        Bencode::decode('d2:ffi8e');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage 8 bytes expected but only 3 bytes remain
     */
    function testStringLengthMismatch() {
        Bencode::decode('8:foo');
    }

    function testList() {
        self::assertBencode(array(9, 2, 4, 'foo', 'bar', array()), 'li9ei2ei4e3:foo3:barlee');
    }

    function testDictionary() {
        self::assertBencode(array('foo' => 'bar', 'baz' => 'boo'), 'd3:foo3:bar3:baz3:booe');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage ':' not found
     */
    function testOddDictionaryMembers() {
        Bencode::decode('di0ee');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Bencode supports only integers, strings and arrays. double given.
     */
    function testFloat() {
        Bencode::encode(0.0);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Bencode supports only integers, strings and arrays. object given.
     */
    function testObject() {
        Bencode::encode(new \stdClass);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Bencode supports only integers, strings and arrays. NULL given.
     */
    function testNull() {
        Bencode::encode(null);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Bencode supports only integers, strings and arrays. boolean given.
     */
    function testBool() {
        Bencode::encode(true);
    }

    function testDictionarySortOrder() {
        $keysUnsorted = array(
            "\n",
            "\t",
            '',
            "\xff",
            "\xff\x00",
            '0',
            'baaa',
            'abbb',
            '00',
            '000',
            "\x00",
            ' ',
            '10',
            '1',
            '01',
        );

        $keysSorted = array(
            '',
            "\x00",
            "\t",
            "\n",
            " ",
            '0',
            '00',
            '000',
            '01',
            '1',
            '10',
            'abbb',
            'baaa',
            "\xff",
            "\xff\x00",
        );

        $dictionary = array_fill_keys($keysUnsorted, 0);
        $dictionary2 = Bencode::decode(Bencode::encode($dictionary));

        self::assertEquals($keysSorted, array_keys($dictionary2));
    }
}
 

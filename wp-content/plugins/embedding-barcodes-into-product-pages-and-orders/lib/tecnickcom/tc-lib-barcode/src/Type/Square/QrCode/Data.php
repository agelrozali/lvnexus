<?php

namespace Com\Tecnick\Barcode\Type\Square\QrCode;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

class Data
{
    const QRSPEC_VERSION_MAX = 40;

    const QRSPEC_WIDTH_MAX = 177;


    const QRCAP_WIDTH = 0;

    const QRCAP_WORDS = 1;

    const QRCAP_REMINDER = 2;

    const QRCAP_EC = 3;



    const STRUCTURE_HEADER_BITS = 20;

    const MAX_STRUCTURED_SYMBOLS = 16;



    const N1 = 3;

    const N2 = 3;

    const N3 = 40;

    const N4 = 10;

    public static $encodingModes = array('NL' => -1, 'NM' => 0, 'AN' => 1, '8B' => 2, 'KJ' => 3, 'ST' => 4);

    public static $errCorrLevels = array('L' => 0, 'M' => 1, 'Q' => 2, 'H' => 3);

    public static $anTable = array(
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 
        36, -1, -1, -1, 37, 38, -1, -1, -1, -1, 39, 40, -1, 41, 42, 43, 
         0,  1,  2,  3,  4,  5,  6,  7,  8,  9, 44, -1, -1, -1, -1, -1, 
        -1, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 
        25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, -1, -1, -1, -1, -1, 
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1  
    );

    public static $capacity = array(
        array(  0,    0, 0, array(   0,    0,    0,    0)), 
        array( 21,   26, 0, array(   7,   10,   13,   17)), 
        array( 25,   44, 7, array(  10,   16,   22,   28)), 
        array( 29,   70, 7, array(  15,   26,   36,   44)), 
        array( 33,  100, 7, array(  20,   36,   52,   64)), 
        array( 37,  134, 7, array(  26,   48,   72,   88)), 
        array( 41,  172, 7, array(  36,   64,   96,  112)), 
        array( 45,  196, 0, array(  40,   72,  108,  130)), 
        array( 49,  242, 0, array(  48,   88,  132,  156)), 
        array( 53,  292, 0, array(  60,  110,  160,  192)), 
        array( 57,  346, 0, array(  72,  130,  192,  224)), 
        array( 61,  404, 0, array(  80,  150,  224,  264)), 
        array( 65,  466, 0, array(  96,  176,  260,  308)), 
        array( 69,  532, 0, array( 104,  198,  288,  352)), 
        array( 73,  581, 3, array( 120,  216,  320,  384)), 
        array( 77,  655, 3, array( 132,  240,  360,  432)), 
        array( 81,  733, 3, array( 144,  280,  408,  480)), 
        array( 85,  815, 3, array( 168,  308,  448,  532)), 
        array( 89,  901, 3, array( 180,  338,  504,  588)), 
        array( 93,  991, 3, array( 196,  364,  546,  650)), 
        array( 97, 1085, 3, array( 224,  416,  600,  700)), 
        array(101, 1156, 4, array( 224,  442,  644,  750)), 
        array(105, 1258, 4, array( 252,  476,  690,  816)), 
        array(109, 1364, 4, array( 270,  504,  750,  900)), 
        array(113, 1474, 4, array( 300,  560,  810,  960)), 
        array(117, 1588, 4, array( 312,  588,  870, 1050)), 
        array(121, 1706, 4, array( 336,  644,  952, 1110)), 
        array(125, 1828, 4, array( 360,  700, 1020, 1200)), 
        array(129, 1921, 3, array( 390,  728, 1050, 1260)), 
        array(133, 2051, 3, array( 420,  784, 1140, 1350)), 
        array(137, 2185, 3, array( 450,  812, 1200, 1440)), 
        array(141, 2323, 3, array( 480,  868, 1290, 1530)), 
        array(145, 2465, 3, array( 510,  924, 1350, 1620)), 
        array(149, 2611, 3, array( 540,  980, 1440, 1710)), 
        array(153, 2761, 3, array( 570, 1036, 1530, 1800)), 
        array(157, 2876, 0, array( 570, 1064, 1590, 1890)), 
        array(161, 3034, 0, array( 600, 1120, 1680, 1980)), 
        array(165, 3196, 0, array( 630, 1204, 1770, 2100)), 
        array(169, 3362, 0, array( 660, 1260, 1860, 2220)), 
        array(173, 3532, 0, array( 720, 1316, 1950, 2310)), 
        array(177, 3706, 0, array( 750, 1372, 2040, 2430))  
    );

    public static $lengthTableBits = array(
        array(10, 12, 14),
        array( 9, 11, 13),
        array( 8, 16, 16),
        array( 8, 10, 12)
    );

    public static $eccTable = array(
        array(array( 0,  0), array( 0,  0), array( 0,  0), array( 0,  0)), 
        array(array( 1,  0), array( 1,  0), array( 1,  0), array( 1,  0)), 
        array(array( 1,  0), array( 1,  0), array( 1,  0), array( 1,  0)), 
        array(array( 1,  0), array( 1,  0), array( 2,  0), array( 2,  0)), 
        array(array( 1,  0), array( 2,  0), array( 2,  0), array( 4,  0)), 
        array(array( 1,  0), array( 2,  0), array( 2,  2), array( 2,  2)), 
        array(array( 2,  0), array( 4,  0), array( 4,  0), array( 4,  0)), 
        array(array( 2,  0), array( 4,  0), array( 2,  4), array( 4,  1)), 
        array(array( 2,  0), array( 2,  2), array( 4,  2), array( 4,  2)), 
        array(array( 2,  0), array( 3,  2), array( 4,  4), array( 4,  4)), 
        array(array( 2,  2), array( 4,  1), array( 6,  2), array( 6,  2)), 
        array(array( 4,  0), array( 1,  4), array( 4,  4), array( 3,  8)), 
        array(array( 2,  2), array( 6,  2), array( 4,  6), array( 7,  4)), 
        array(array( 4,  0), array( 8,  1), array( 8,  4), array(12,  4)), 
        array(array( 3,  1), array( 4,  5), array(11,  5), array(11,  5)), 
        array(array( 5,  1), array( 5,  5), array( 5,  7), array(11,  7)), 
        array(array( 5,  1), array( 7,  3), array(15,  2), array( 3, 13)), 
        array(array( 1,  5), array(10,  1), array( 1, 15), array( 2, 17)), 
        array(array( 5,  1), array( 9,  4), array(17,  1), array( 2, 19)), 
        array(array( 3,  4), array( 3, 11), array(17,  4), array( 9, 16)), 
        array(array( 3,  5), array( 3, 13), array(15,  5), array(15, 10)), 
        array(array( 4,  4), array(17,  0), array(17,  6), array(19,  6)), 
        array(array( 2,  7), array(17,  0), array( 7, 16), array(34,  0)), 
        array(array( 4,  5), array( 4, 14), array(11, 14), array(16, 14)), 
        array(array( 6,  4), array( 6, 14), array(11, 16), array(30,  2)), 
        array(array( 8,  4), array( 8, 13), array( 7, 22), array(22, 13)), 
        array(array(10,  2), array(19,  4), array(28,  6), array(33,  4)), 
        array(array( 8,  4), array(22,  3), array( 8, 26), array(12, 28)), 
        array(array( 3, 10), array( 3, 23), array( 4, 31), array(11, 31)), 
        array(array( 7,  7), array(21,  7), array( 1, 37), array(19, 26)), 
        array(array( 5, 10), array(19, 10), array(15, 25), array(23, 25)), 
        array(array(13,  3), array( 2, 29), array(42,  1), array(23, 28)), 
        array(array(17,  0), array(10, 23), array(10, 35), array(19, 35)), 
        array(array(17,  1), array(14, 21), array(29, 19), array(11, 46)), 
        array(array(13,  6), array(14, 23), array(44,  7), array(59,  1)), 
        array(array(12,  7), array(12, 26), array(39, 14), array(22, 41)), 
        array(array( 6, 14), array( 6, 34), array(46, 10), array( 2, 64)), 
        array(array(17,  4), array(29, 14), array(49, 10), array(24, 46)), 
        array(array( 4, 18), array(13, 32), array(48, 14), array(42, 32)), 
        array(array(20,  4), array(40,  7), array(43, 22), array(10, 67)), 
        array(array(19,  6), array(18, 31), array(34, 34), array(20, 61))  
    );

    public static $alignmentPattern = array(
        array( 0,  0),
        array( 0,  0), array(18,  0), array(22,  0), array(26,  0), array(30,  0), 
        array(34,  0), array(22, 38), array(24, 42), array(26, 46), array(28, 50), 
        array(30, 54), array(32, 58), array(34, 62), array(26, 46), array(26, 48), 
        array(26, 50), array(30, 54), array(30, 56), array(30, 58), array(34, 62), 
        array(28, 50), array(26, 50), array(30, 54), array(28, 54), array(32, 58), 
        array(30, 58), array(34, 62), array(26, 50), array(30, 54), array(26, 52), 
        array(30, 56), array(34, 60), array(30, 58), array(34, 62), array(30, 54), 
        array(24, 50), array(28, 54), array(32, 58), array(26, 54), array(30, 58)  
    );

    public static $versionPattern = array(
        0x07c94, 0x085bc, 0x09a99, 0x0a4d3, 0x0bbf6, 0x0c762, 0x0d847, 0x0e60d, 
        0x0f928, 0x10b78, 0x1145d, 0x12a17, 0x13532, 0x149a6, 0x15683, 0x168c9, 
        0x177ec, 0x18ec4, 0x191e1, 0x1afab, 0x1b08e, 0x1cc1a, 0x1d33f, 0x1ed75, 
        0x1f250, 0x209d5, 0x216f0, 0x228ba, 0x2379f, 0x24b0b, 0x2542e, 0x26a64, 
        0x27541, 0x28c69
    );

    public static $formatInfo = array(
        array(0x77c4, 0x72f3, 0x7daa, 0x789d, 0x662f, 0x6318, 0x6c41, 0x6976), 
        array(0x5412, 0x5125, 0x5e7c, 0x5b4b, 0x45f9, 0x40ce, 0x4f97, 0x4aa0), 
        array(0x355f, 0x3068, 0x3f31, 0x3a06, 0x24b4, 0x2183, 0x2eda, 0x2bed), 
        array(0x1689, 0x13be, 0x1ce7, 0x19d0, 0x0762, 0x0255, 0x0d0c, 0x083b)  
    );
}

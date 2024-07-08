<?php

declare(strict_types=1);

namespace Michnovka\OpenWebNet;

/**
 * Class OPENHash
 *
 * Wrapper class to calculate OPEN response hash for challenge nonce given configured password
 */
class OPENHash
{
    public static function calculate(string $password, string $nonce): string
    {
        $msr = 0x7FFFFFFF;
        $m1 = 0xFFFFFFFF;
        $m8 = 0xFFFFFFF8;
        $m16 = 0xFFFFFFF0;
        $m128 = 0xFFFFFF80;
        $m16777216 = 0xFF000000;
        $flag = true;
        $num1 = 0;
        $num2 = 0;
        $password = intval($password);

        foreach (str_split($nonce) as $c) {
            $num1 = $num1 & $m1;
            $num2 = $num2 & $m1;
            if ($c == '1') {
                $length = !$flag;
                if (!$length) {
                    $num2 = $password;
                }

                $num1 = $num2 & $m128;
                $num1 = $num1 >> 1;
                $num1 = $num1 & $msr;
                $num1 = $num1 >> 6;
                $num2 = $num2 << 25;
                $num1 = $num1 + $num2;
                $flag = false;
            } elseif ($c == '2') {
                $length = !$flag;
                if (!$length) {
                    $num2 = $password;
                }
                $num1 = $num2 & $m16;
                $num1 = $num1 >> 1;
                $num1 = $num1 & $msr;
                $num1 = $num1 >> 3;
                $num2 = $num2 << 28;
                $num1 = $num1 + $num2;
                $flag = false;
            } elseif ($c == '3') {
                $length = !$flag;
                if (!$length) {
                    $num2 = $password;
                }
                $num1 = $num2 & $m8;
                $num1 = $num1 >> 1;
                $num1 = $num1 & $msr;
                $num1 = $num1 >> 2;
                $num2 = $num2 << 29;
                $num1 = $num1 + $num2;
                $flag = false;
            } elseif ($c == '4') {
                $length = !$flag;
                if (!$length) {
                    $num2 = $password;
                }
                $num1 = $num2 << 1;
                $num2 = $num2 >> 1;
                $num2 = $num2 & $msr;
                $num2 = $num2 >> 30;
                $num1 = $num1 + $num2;
                $flag = false;
            } elseif ($c == '5') {
                $length = !$flag;
                if (!$length) {
                    $num2 = $password;
                }
                $num1 = $num2 << 5;
                $num2 = $num2 >> 1;
                $num2 = $num2 & $msr;
                $num2 = $num2 >> 26;
                $num1 = $num1 + $num2;
                $flag = false;
            } elseif ($c == '6') {
                $length = !$flag;
                if (!$length) {
                    $num2 = $password;
                }
                $num1 = $num2 << 12;
                $num2 = $num2 >> 1;
                $num2 = $num2 & $msr;
                $num2 = $num2 >> 19;
                $num1 = $num1 + $num2;
                $flag = false;
            } elseif ($c == '7') {
                $length = !$flag;
                if (!$length) {
                    $num2 = $password;
                }
                $num1 = $num2 & 0xFF00;
                $num1 = $num1 + (($num2 & 0xFF) << 24);
                $num1 = $num1 + (($num2 & 0xFF0000) >> 16);
                $num2 = $num2 & $m16777216;
                $num2 = $num2 >> 1;
                $num2 = $num2 & $msr;
                $num2 = $num2 >> 7;
                $num1 = $num1 + $num2;
                $flag = false;
            } elseif ($c == '8') {
                $length = !$flag;
                if (!$length) {
                    $num2 = $password;
                }
                $num1 = $num2 & 0xFFFF;
                $num1 = $num1 << 16;
                $numx = $num2 >> 1;
                $numx = $numx & $msr;
                $numx = $numx >> 23;
                $num1 = $num1 + $numx;
                $num2 = $num2 & 0xFF0000;
                $num2 = $num2 >> 1;
                $num2 = $num2 & $msr;
                $num2 = $num2 >> 7;
                $num1 = $num1 + $num2;
                $flag = false;
            } elseif ($c == '9') {
                $length = !$flag;
                if (!$length) {
                    $num2 = $password;
                }
                $num1 = ~(int)$num2;
                $flag = false;
            } else {
                $num1 = $num2;
            }
            $num2 = $num1;
        }
        return sprintf('%u', $num1 & $m1);
    }
}

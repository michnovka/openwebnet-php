<?php

declare(strict_types=1);

namespace Michnovka\OpenWebNet;

class OpenWebNetLocations
{
    /**
     */
    public static function all(): string
    {
        return '0';
    }

    /**
     * Return OWN Address format based on area and point. If point is null/false then returns whole are address.
     *
     */
    public static function address(int $area, ?int $point): string
    {

        if ($point === null) {
            return (string)$area;
        }

        if ($point >= 10 || $area >= 10) {
            $area = str_pad((string) $area, 2, '0', STR_PAD_LEFT);
            $point = str_pad((string) $point, 2, '0', STR_PAD_LEFT);
        }

        return $area . $point;
    }

    public static function isArea(string $address): bool
    {
        if ((int) $address < 10) {
            return true;
        }

        return false;
    }

    /**
     * Returns array with keys 'area' and 'point'. Returns false if invalid address provided
     *
     * @return false|array{area: int, point?: int}
     */
    public static function parseAddress(string $address): false|array
    {
        $reply = [];

        if (self::isArea($address)) {
            $reply['area'] = intval($address);
        } else {
            if (strlen($address) == 4) {
                $reply['area'] = intval(substr($address, 0, 2));
                $reply['point'] = intval(substr($address, 2, 2));
            } elseif (strlen($address) == 2) {
                $reply['area'] = intval(substr($address, 0, 1));
                $reply['point'] = intval(substr($address, 1, 1));
            } else {
                return false;
            }
        }

        return $reply;
    }
}

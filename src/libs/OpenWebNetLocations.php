<?php

class OpenWebNetLocations{

	/**
	 * @return string
	 */
	public static function All(){
		return '0';
	}

	/**
	 * Return OWN Address format based on area and point. If point is null/false then returns whole are address.
	 *
	 * @param int $area
	 * @param int|null|false $point
	 * @return string
	 */
	public static function Address($area, $point){

		if($point === null || $point === false)
			return $area;

		if($point >= 10 || $area >= 10){
			$area = str_pad($area, 2, '0', STR_PAD_LEFT);
			$point = str_pad($point, 2, '0', STR_PAD_LEFT);
		}

		return $area.$point;
	}

	/**
	 * @param int $address
	 * @return bool
	 */
	public static function IsArea($address){
		if($address < 10)
			return true;

		return false;
	}

	/**
	 * Returns array with keys 'area' and 'point'. Returns false if invalid address provided
	 *
	 * @param int $address
	 * @return bool|array
	 */
	public static function ParseAddress($address){
		$reply = array('area' => null, 'point' => null);

		if(self::IsArea($address)){
			$reply['area'] = $address;
		}else{
			if(strlen($address) == 4){
				$reply['area'] = intval(substr($address, 0, 2));
				$reply['point'] = intval(substr($address, 2, 2));
			}elseif(strlen($address) == 2){
				$reply['area'] = intval(substr($address, 0, 1));
				$reply['point'] = intval(substr($address, 1, 1));
			}else{
				return false;
			}
		}

		return $reply;
	}
}
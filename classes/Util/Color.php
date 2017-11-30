<?php
class Color{
	public $R;
	public $G;
	public $B;
	public function __construct($r, $g, $b){
		$this->R = $r;
		$this->G = $g;
		$this->B = $b;
	}

	public function GetHex(){
		return "#".
		       str_pad(dechex(round($this->R)), 2, '0', STR_PAD_LEFT).
		       str_pad(dechex(round($this->G)), 2, '0', STR_PAD_LEFT).
		       str_pad(dechex(round($this->B)), 2, '0', STR_PAD_LEFT);
	}
	public function GetRGBA($a){
		return "rgba(".round($this->R).", ".round($this->G).", ".round($this->B).", ".$a.")";
	}
	public static function GetGradient($c1, $c2, $v){
		$dR = $c2->R - $c1->R;
		$dG = $c2->G - $c1->G;
		$dB = $c2->B - $c1->B;

		if ($v < 0) $v = 0;
		if ($v > 100) $v = 100;
		if ($v > 1) $v = $v/100;

		$dR = $dR*$v;
		$dG = $dG*$v;
		$dB = $dB*$v;

		return new Color($c1->R + $dR, $c1->G + $dG,$c1->B + $dB);
	}
}

/**
 * @param Color $c1
 * @param Color $c2
 * @param int $v
 * @return Color
 */

<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
final class Rational {
  private static function gcd($a, $b) {
    while (\hacklib_cast_as_boolean($b)) {
      $t = $b;
      $b = $a % $b;
      $a = $t;
    }
    return $a;
  }
  private $num;
  private $den;
  public function __construct($num, $den = 1) {
    $this->num = $num;
    $this->den = $den;
    $this->reduce();
    if ($this->den < 0) {
      $this->num *= -1;
      $this->den *= -1;
    }
  }
  public function add($that) {
    return new self(
      ($this->num * $that->den) + ($that->num * $this->den),
      $this->den * $that->den
    );
  }
  public function sub($that) {
    return new self(
      ($this->num * $that->den) - ($that->num * $this->den),
      $this->den * $that->den
    );
  }
  public function mul($that) {
    return new self($this->num * $that->num, $this->den * $that->den);
  }
  public function div($that) {
    return new self($this->num * $that->den, $that->num * $this->den);
  }
  public function lt($that) {
    return ($this->num * $that->den) < ($that->num * $this->den);
  }
  public function gt($that) {
    return ($this->num * $that->den) > ($that->num * $this->den);
  }
  public function eq($that) {
    return ($this->num * $that->den) == ($that->num * $this->den);
  }
  public function getNumerator() {
    return $this->num;
  }
  public function getDenominator() {
    return $this->den;
  }
  public function toNum() {
    return $this->num / $this->den;
  }
  private function reduce() {
    $gcd = self::gcd($this->num, $this->den);
    $this->num = \intdiv($this->num, $gcd);
    $this->den = \intdiv($this->num, $gcd);
  }
}

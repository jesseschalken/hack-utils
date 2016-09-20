<?hh // strict

namespace HackUtils;

final class Rational {
  private static function gcd(int $a, int $b): int {
    while ($b) {
      $t = $b;
      $b = $a % $b;
      $a = $t;
    }
    return $a;
  }

  public function __construct(private int $num, private int $den = 1) {
    $this->reduce();

    // Simplify 3/-5 to -3/5
    if ($this->den < 0) {
      $this->num *= -1;
      $this->den *= -1;
    }
  }

  public function add(Rational $that): Rational {
    return new self(
      $this->num * $that->den + $that->num * $this->den,
      $this->den * $that->den,
    );
  }

  public function sub(Rational $that): Rational {
    return new self(
      $this->num * $that->den - $that->num * $this->den,
      $this->den * $that->den,
    );
  }

  public function mul(Rational $that): Rational {
    return new self($this->num * $that->num, $this->den * $that->den);
  }

  public function div(Rational $that): Rational {
    return new self($this->num * $that->den, $that->num * $this->den);
  }

  public function lt(Rational $that): bool {
    return $this->num * $that->den < $that->num * $this->den;
  }

  public function gt(Rational $that): bool {
    return $this->num * $that->den > $that->num * $this->den;
  }

  public function eq(Rational $that): bool {
    return $this->num * $that->den == $that->num * $this->den;
  }

  public function getNumerator(): int {
    return $this->num;
  }

  public function getDenominator(): int {
    return $this->den;
  }

  public function toNum(): num {
    return $this->num / $this->den;
  }

  private function reduce(): void {
    $gcd = self::gcd($this->num, $this->den);

    $this->num = \intdiv($this->num, $gcd);
    $this->den = \intdiv($this->num, $gcd);
  }
}

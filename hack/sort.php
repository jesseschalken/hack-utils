<?hh // strict

namespace HackUtils;

abstract class Sorter<T> {
  public abstract function sort<Tv as T>(array<Tv> $array): array<Tv>;

  public abstract function sortValues<Tk, Tv as T>(
    array<Tk, Tv> $array,
  ): array<Tk, Tv>;

  public abstract function sortKeys<Tk as T, Tv>(
    array<Tk, Tv> $array,
  ): array<Tk, Tv>;
}

abstract class _BuiltinSorter<T> extends Sorter<T> {
  private bool $reverse = false;

  protected function __construct(private int $flags) {}

  protected function _set(int $flag, bool $val): this {
    if ($val)
      $this->flags |= $flag; else
      $this->flags &= ~$flag;
    return $this;
  }

  public function setReverse(bool $reverse = true): this {
    $this->reverse = $reverse;
    return $this;
  }

  <<__Override>>
  public function sort<Tv as T>(array<Tv> $array): array<Tv> {
    if ($this->reverse)
      Exception::assertTrue(\rsort($array, $this->flags)); else
      Exception::assertTrue(\sort($array, $this->flags));
    return $array;
  }

  <<__Override>>
  public function sortValues<Tk, Tv as T>(
    array<Tk, Tv> $array,
  ): array<Tk, Tv> {
    if ($this->reverse)
      Exception::assertTrue(\arsort($array, $this->flags)); else
      Exception::assertTrue(\asort($array, $this->flags));
    return $array;
  }

  <<__Override>>
  public function sortKeys<Tk as T, Tv>(array<Tk, Tv> $array): array<Tk, Tv> {
    if ($this->reverse)
      Exception::assertTrue(\krsort($array, $this->flags)); else
      Exception::assertTrue(\ksort($array, $this->flags));
    return $array;
  }
}

final class CallbackSorter<T> extends Sorter<T> {
  public function __construct(private (function(T, T): int) $cmp) {}

  <<__Override>>
  public function sort<Tv as T>(array<Tv> $array): array<Tv> {
    Exception::assertTrue(\usort($array, $this->cmp));
    return $array;
  }

  <<__Override>>
  public function sortValues<Tk, Tv as T>(
    array<Tk, Tv> $array,
  ): array<Tk, Tv> {
    Exception::assertTrue(\uasort($array, $this->cmp));
    return $array;
  }

  <<__Override>>
  public function sortKeys<Tk as T, Tv>(array<Tk, Tv> $array): array<Tk, Tv> {
    Exception::assertTrue(\uksort($array, $this->cmp));
    return $array;
  }
}

final class NumSorter extends _BuiltinSorter<num> {
  public function __construct() {
    parent::__construct(\SORT_NUMERIC);
  }
}

final class StringSorter extends _BuiltinSorter<string> {
  public function __construct() {
    parent::__construct(\SORT_STRING);
  }

  public function setNatural(bool $nat = true): this {
    $this->_set(\SORT_NATURAL & ~\SORT_STRING, $nat);
    return $this;
  }

  public function setCaseInsensitive(bool $ci = true): this {
    $this->_set(\SORT_FLAG_CASE, $ci);
    return $this;
  }
}

final class LocaleStringSorter extends _BuiltinSorter<string> {
  public function __construct() {
    parent::__construct(\SORT_LOCALE_STRING);
  }
}

final class MixedSorter extends _BuiltinSorter<mixed> {
  public function __construct() {
    parent::__construct(\SORT_REGULAR);
  }
}

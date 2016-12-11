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
      _check_return(\rsort($array, $this->flags), 'rsort'); else
      _check_return(\sort($array, $this->flags), 'sort');
    return $array;
  }

  <<__Override>>
  public function sortValues<Tk, Tv as T>(
    array<Tk, Tv> $array,
  ): array<Tk, Tv> {
    if ($this->reverse)
      _check_return(\arsort($array, $this->flags), 'arsort'); else
      _check_return(\asort($array, $this->flags), 'asort');
    return $array;
  }

  <<__Override>>
  public function sortKeys<Tk as T, Tv>(array<Tk, Tv> $array): array<Tk, Tv> {
    if ($this->reverse)
      _check_return(\krsort($array, $this->flags), 'krsort'); else
      _check_return(\ksort($array, $this->flags), 'ksort');
    return $array;
  }
}

final class CallbackSorter<T> extends Sorter<T> {
  public function __construct(private (function(T, T): int) $cmp) {}

  <<__Override>>
  public function sort<Tv as T>(array<Tv> $array): array<Tv> {
    _check_return(\usort($array, $this->cmp), 'usort');
    return $array;
  }

  <<__Override>>
  public function sortValues<Tk, Tv as T>(
    array<Tk, Tv> $array,
  ): array<Tk, Tv> {
    _check_return(\uasort($array, $this->cmp), 'uasort');
    return $array;
  }

  <<__Override>>
  public function sortKeys<Tk as T, Tv>(array<Tk, Tv> $array): array<Tk, Tv> {
    _check_return(\uksort($array, $this->cmp), 'uksort');
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

function _check_return(bool $ret, string $func): void {
  if ($ret === false)
    throw new \Exception("$func() failed");
}

<?hh // strict

namespace HackUtils;

const ?string NULL_STRING = null;
const ?int NULL_INT = null;
const ?float NULL_FLOAT = null;
const ?resource NULL_RESOURCE = null;
const ?bool NULL_BOOL = null;
const mixed NULL_MIXED = null;

class TestNewNull extends Test {
  public function run(): void {
    self::assertEqual(new_null(), null);
  }
}

/**
 * The Hack typechecker reports "null" as "Partially type checked code.
 * Consider adding type annotations". To avoid that, you can replace it with
 * a call to this function.
 */
function new_null<T>(): ?T {
  return null;
}

class TestNullThrows extends Test {
  public function run(): void {
    self::assertEqual(null_throws('foo'), 'foo');
    self::assertException(
      function() {
        null_throws(null);
      },
      'Unexpected null',
    );
    self::assertException(
      function() {
        null_throws(null, 'foo');
      },
      'foo',
    );
  }
}

/**
 * Convert a nullable value into a non-nullable value, throwing an exception
 * in the case of null.
 */
function null_throws<T>(?T $value, string $message = "Unexpected null"): T {
  return $value === null ? throw_(new \Exception($message)) : $value;
}

class TestThrow extends Test {
  public function run(): void {
    self::assertException(
      function() {
        $a = throw_(new \Exception('lol', 2));
        print $a;
      },
      'lol',
      2,
    );
  }
}

/**
 * Throw an exception in the context of an expression.
 */
function throw_<T>(\Exception $e): T {
  throw $e;
}

class TestUnreachable extends Test {
  public function run(): void {
    self::assertException(
      function() {
        unreachable();
      },
      'This code should be unreachable',
    );
    self::assertException(
      function() {
        unreachable('foo');
      },
      'foo',
    );
  }
}

function unreachable(
  string $message = 'This code should be unreachable',
): noreturn {
  throw new \Exception($message);
}

class TestIfNull extends Test {
  public function run(): void {
    self::assertEqual(if_null('a', 'b'), 'a');
    self::assertEqual(if_null('a', null), 'a');
    self::assertEqual(if_null(null, 'b'), 'b');
    self::assertEqual(if_null(null, null), null);
  }
}

/**
 * On PHP7 and HHVM you can use "??". Use this function to support PHP5.
 */
function if_null<T>(?T $x, T $y): T {
  return $x === null ? $y : $x;
}

class TestFst extends Test {
  public function run(): void {
    self::assertEqual(fst(tuple('a', 'b')), 'a');
  }
}

function fst<T>((T, mixed) $t): T {
  return $t[0];
}

class TestSnd extends Test {
  public function run(): void {
    self::assertEqual(snd(tuple('a', 'b')), 'b');
  }
}

function snd<T>((mixed, T) $t): T {
  return $t[1];
}

interface Gettable<+T> {
  public function get(): T;
}

interface Settable<-T> {
  public function set(T $value): void;
}

class TestRef extends Test {
  public function run(): void {
    $ref = new Ref('x');
    self::assertEqual($ref->get(), 'x');
    $ref->set('y');
    self::assertEqual($ref->get(), 'y');
  }
}

/**
 * Simple container for a value of a given type. Useful to replace PHP's
 * built in references, which are not supported in Hack.
 */
final class Ref<T> implements Gettable<T>, Settable<T> {
  public function __construct(private T $value) {}

  public function get(): T {
    return $this->value;
  }

  public function set(T $value): void {
    $this->value = $value;
  }
}

class TestIsAssoc extends SampleTest<array<mixed, mixed>, bool> {
  public function evaluate(array<mixed, mixed> $in): bool {
    return is_assoc($in);
  }
  public function getData(): array<(array<mixed, mixed>, bool)> {
    return [
      tuple([], false),
      tuple(['a'], false),
      tuple(['a', 'b'], false),
      tuple([1 => 'a', 0 => 'b'], true),
      tuple(['c' => 'a', 'd' => 'b'], true),
    ];
  }
}

/**
 * Returns true if the array is associative. False if not.
 */
function is_assoc(array<mixed, mixed> $x): bool {
  $i = 0;
  foreach ($x as $k => $v) {
    if ($k !== $i++) {
      return true;
    }
  }
  return false;
}

class TestConcat extends Test {
  public function run(): void {
    self::assertEqual(concat([], []), []);
    self::assertEqual(concat(['a'], []), ['a']);
    self::assertEqual(concat([], ['a']), ['a']);
    self::assertEqual(concat(['a'], ['b']), ['a', 'b']);
    self::assertEqual(concat(['a', 'c'], ['b']), ['a', 'c', 'b']);
    self::assertEqual(concat(['a', 'c'], ['b', 'd']), ['a', 'c', 'b', 'd']);
  }
}

function concat<T>(array<T> $a, array<T> $b): array<T> {
  return \array_merge($a, $b);
}

class TestConcatAll extends Test {
  public function run(): void {
    self::assertEqual(concat_all([]), []);
    self::assertEqual(concat_all([['a', 'b']]), ['a', 'b']);
    self::assertEqual(concat_all([['a'], []]), ['a']);
    self::assertEqual(concat_all([[], ['a']]), ['a']);
    self::assertEqual(concat_all([['a'], ['b']]), ['a', 'b']);
    self::assertEqual(concat_all([['a'], ['b'], ['c']]), ['a', 'b', 'c']);
    self::assertEqual(
      concat_all([['a', 'd'], ['b', 'e'], ['c', 'f']]),
      ['a', 'd', 'b', 'e', 'c', 'f'],
    );
  }
}

function concat_all<T>(array<array<T>> $vectors): array<T> {
  return $vectors ? \call_user_func_array('array_merge', $vectors) : [];
}

class TestPush extends Test {
  public function run(): void {
    self::assertEqual(push([], 'x'), ['x']);
    self::assertEqual(push(['y'], 'x'), ['y', 'x']);
    self::assertEqual(push(['y', 'z'], 'x'), ['y', 'z', 'x']);
  }
}

function push<T>(array<T> $v, T $x): array<T> {
  \array_push($v, $x);
  return $v;
}

class TestPop extends Test {
  public function run(): void {
    self::assertEqual(pop([0]), tuple([], 0));
    self::assertEqual(pop(['a', 'b']), tuple(['a'], 'b'));
    self::assertException(
      function() {
        pop([]);
      },
      'Cannot pop last element: Array is empty',
    );
  }
}

function pop<T>(array<T> $v): (array<T>, T) {
  if (!$v) {
    throw new Exception('Cannot pop last element: Array is empty');
  }
  $x = \array_pop($v);
  return tuple($v, $x);
}

class TestUnshift extends Test {
  public function run(): void {
    self::assertEqual(unshift('x', []), ['x']);
    self::assertEqual(unshift('x', ['y']), ['x', 'y']);
    self::assertEqual(unshift('x', ['y', 'z']), ['x', 'y', 'z']);
  }
}

function unshift<T>(T $x, array<T> $v): array<T> {
  \array_unshift($v, $x);
  return $v;
}

class TestShift extends Test {
  public function run(): void {
    self::assertEqual(shift([0]), tuple(0, []));
    self::assertEqual(shift(['a', 'b']), tuple('a', ['b']));
    self::assertException(
      function() {
        shift([]);
      },
      'Cannot shift first element: Array is empty',
    );
  }
}

function shift<T>(array<T> $v): (T, array<T>) {
  if (!$v) {
    throw new Exception('Cannot shift first element: Array is empty');
  }
  $x = \array_shift($v);
  return tuple($x, $v);
}

class TestRange extends Test {
  public function run(): void {
    self::assertEqual(range(0, 5), [0, 1, 2, 3, 4, 5]);
    self::assertEqual(range(-3, 3), [-3, -2, -1, 0, 1, 2, 3]);
    self::assertEqual(range(3, -3), [3, 2, 1, 0, -1, -2, -3]);
    self::assertEqual(range(3, -3, 2), [3, 1, -1, -3]);
    self::assertEqual(range(3, -3, -3), [3, 0, -3]);
    self::assertEqual(range(3, -3, 4), [3, -1]);
  }
}

function range(int $start, int $end, int $step = 1): array<int> {
  return \range($start, $end, $step);
}

class TestFilter extends Test {
  public function run(): void {
    self::assertEqual(
      filter(
        [6, 7, 8, 9, 10, 11, 12],
        function($x) {
          return (bool) ($x & 1);
        },
      ),
      [7, 9, 11],
    );
    self::assertEqual(
      filter(
        [6, 7, 8, 9, 10, 11, 12],
        function($x) {
          return !(bool) ($x & 1);
        },
      ),
      [6, 8, 10, 12],
    );
  }
}

function filter<T>(array<T> $array, (function(T): bool) $f): array<T> {
  $ret = filter_assoc($array, $f);
  // array_filter() preserves keys, so if some elements were removed,
  // renumber keys 0,1...N.
  return count($ret) != count($array) ? values($ret) : $array;
}

class TestFilterAssoc extends Test {
  public function run(): void {
    self::assertEqual(
      filter_assoc(
        ["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5],
        function($x) {
          return (bool) ($x & 1);
        },
      ),
      ['a' => 1, 'c' => 3, 'e' => 5],
    );
  }
}

function filter_assoc<Tk, Tv>(
  array<Tk, Tv> $array,
  (function(Tv): bool) $f,
): array<Tk, Tv> {
  return \array_filter($array, $f);
}

class TestMap extends Test {
  public function run(): void {
    self::assertEqual(
      map(
        [1, 2, 3, 4, 5],
        function($x) {
          return $x * $x * $x;
        },
      ),
      [1, 8, 27, 64, 125],
    );

    self::assertEqual(
      map(
        range(1, 5),
        function($x) {
          return $x * 2;
        },
      ),
      [2, 4, 6, 8, 10],
    );
  }
}

function map<Tin, Tout>(
  array<Tin> $array,
  (function(Tin): Tout) $f,
): array<Tout> {
  return \array_map($f, $array);
}

class TestMapAssoc extends Test {
  public function run(): void {
    self::assertEqual(
      map_assoc(
        ["stringkey" => "value"],
        function($x) {
          Test::assertEqual($x, 'value');
          return 'value2';
        },
      ),
      ["stringkey" => "value2"],
    );
  }
}

function map_assoc<Tk, Tv1, Tv2>(
  array<Tk, Tv1> $array,
  (function(Tv1): Tv2) $f,
): array<Tk, Tv2> {
  return \array_map($f, $array);
}

class TestMapKeys extends Test {
  public function run(): void {
    self::assertEqual(
      map_keys(
        [9 => 'a', -8 => 'c'],
        function($x) {
          return $x * 2;
        },
      ),
      [18 => 'a', -16 => 'c'],
    );
  }
}

function map_keys<Tk1, Tk2, Tv>(
  array<Tk1, Tv> $array,
  (function(Tk1): Tk2) $f,
): array<Tk2, Tv> {
  $ret = [];
  foreach ($array as $k => $v)
    $ret[$f($k)] = $v;
  return $ret;
}

class TestConcatMap extends Test {
  public function run(): void {
    self::assertEqual(
      concat_map([1, 5], $x ==> [$x + 1, $x + 2]),
      [2, 3, 6, 7],
    );
  }
}

function concat_map<Tin, Tout>(
  array<Tin> $array,
  (function(Tin): array<Tout>) $f,
): array<Tout> {
  $ret = [];
  foreach ($array as $x) {
    // I'm not sure, but I think the looping append will be faster than a
    // concat because it wont have to allocate a new array whereas a concat
    // will.
    // $ret = concat($ret, $f($x));
    foreach ($f($x) as $x2) {
      $ret[] = $x2;
    }
  }
  return $ret;
}

class TestReduce extends Test {
  public function run(): void {
    $sum = function($x, $y) {
      return $x + $y;
    };
    $product = function($x, $y) {
      return $x * $y;
    };
    $push = function($x, $y) {
      return push($x, $y);
    };
    $unshift = function($x, $y) {
      return unshift($y, $x);
    };
    self::assertEqual(reduce([1, 2, 3, 4, 5], $sum, 0), 15);
    self::assertEqual(reduce([1, 2, 3, 4, 5], $sum, -5), 10);
    self::assertEqual(reduce([1, 2, 3, 4, 5], $product, 10), 1200);
    self::assertEqual(reduce([1, 2, 3, 4, 5], $push, []), [1, 2, 3, 4, 5]);
    self::assertEqual(reduce([1, 2, 3, 4, 5], $unshift, []), [5, 4, 3, 2, 1]);
  }
}

function reduce<Tin, Tout>(
  array<arraykey, Tin> $array,
  (function(Tout, Tin): Tout) $f,
  Tout $initial,
): Tout {
  return \array_reduce($array, $f, $initial);
}

class TestReduceRight extends Test {
  public function run(): void {
    $push = function($x, $y) {
      return push($x, $y);
    };
    $unshift = function($x, $y) {
      return unshift($y, $x);
    };
    self::assertEqual(
      reduce_right([1, 2, 3, 4, 5], $push, []),
      [5, 4, 3, 2, 1],
    );
    self::assertEqual(
      reduce_right([1, 2, 3, 4, 5], $unshift, []),
      [1, 2, 3, 4, 5],
    );
  }
}

function reduce_right<Tin, Tout>(
  array<arraykey, Tin> $array,
  (function(Tout, Tin): Tout) $f,
  Tout $value,
): Tout {
  $iter = new ArrayIterator($array);
  for ($iter->end(); $iter->valid(); $iter->prev()) {
    $value = $f($value, $iter->current());
  }
  return $value;
}

class TestGroupBy extends Test {
  public function run(): void {
    self::assertEqual(
      group_by(
        [
          'a' => 12,
          'asdf' => 4,
          'etr' => 3,
          '' => 24,
          'efw' => 23,
          'x' => 23,
          '23' => 3423,
          'sd' => 54,
          'ergerg' => 53,
          '+(' => 43445,
          ']123' => 45,
        ],
        function($x) {
          return quot($x, 10);
        },
      ),
      [
        1 => [12],
        0 => [4, 3],
        2 => [24, 23, 23],
        342 => [3423],
        5 => [54, 53],
        4344 => [43445],
        4 => [45],
      ],
    );
  }
}

function group_by<Tk as arraykey, Tv>(
  array<mixed, Tv> $a,
  (function(Tv): Tk) $f,
): array<Tk, array<Tv>> {
  $res = [];
  foreach ($a as $v) {
    $res[$f($v)][] = $v;
  }
  return $res;
}

class TestAnyAll extends Test {
  public function run(): void {
    $count = new Ref(0);
    $list = [9, 4, 1, 3, 4, 345, 2342, 3434, 34];
    $moreThan100 = function($x) use ($count) {
      $count->set($count->get() + 1);
      return $x > 100;
    };
    $lessThan0 = function($x) use ($count) {
      $count->set($count->get() + 1);
      return $x < 0;
    };
    $moreThan0 = function($x) use ($count) {
      $count->set($count->get() + 1);
      return $x > 0;
    };

    $count->set(0);
    self::assertEqual(any($list, $moreThan100), true);
    self::assertEqual($count->get(), 6);

    $count->set(0);
    self::assertEqual(all($list, $moreThan100), false);
    self::assertEqual($count->get(), 1);

    $count->set(0);
    self::assertEqual(any($list, $lessThan0), false);
    self::assertEqual($count->get(), 9);

    $count->set(0);
    self::assertEqual(all($list, $lessThan0), false);
    self::assertEqual($count->get(), 1);

    $count->set(0);
    self::assertEqual(any($list, $moreThan0), true);
    self::assertEqual($count->get(), 1);

    $count->set(0);
    self::assertEqual(all($list, $moreThan0), true);
    self::assertEqual($count->get(), 9);
  }
}

function any<T>(array<mixed, T> $a, (function(T): bool) $f): bool {
  foreach ($a as $x) {
    if ($f($x)) {
      return true;
    }
  }
  return false;
}

function all<T>(array<mixed, T> $a, (function(T): bool) $f): bool {
  foreach ($a as $x) {
    if (!$f($x)) {
      return false;
    }
  }
  return true;
}

class TestKeysToLower extends Test {
  public function run(): void {
    self::assertEqual(
      keys_to_lower(
        [
          'fer' => 4,
          'SADFf' => 9,
          ':}{ADSjj}' => 6,
          'foo BAR baz' => 97674,
          'KEK' => 1,
        ],
      ),
      [
        'fer' => 4,
        'sadff' => 9,
        ':}{adsjj}' => 6,
        'foo bar baz' => 97674,
        'kek' => 1,
      ],
    );
  }
}

function keys_to_lower<Tk as arraykey, Tv>(
  array<Tk, Tv> $array,
): array<Tk, Tv> {
  return \array_change_key_case($array, \CASE_LOWER);
}

class TestKeysToUpper extends Test {
  public function run(): void {
    self::assertEqual(
      keys_to_uppper(
        [
          'fer' => 4,
          'SADFf' => 9,
          ':}{ADSjj}' => 6,
          'foo BAR baz' => 97674,
          'KEK' => 1,
        ],
      ),
      [
        'FER' => 4,
        'SADFF' => 9,
        ':}{ADSJJ}' => 6,
        'FOO BAR BAZ' => 97674,
        'KEK' => 1,
      ],
    );
  }
}

function keys_to_uppper<Tk as arraykey, Tv>(
  array<Tk, Tv> $array,
): array<Tk, Tv> {
  return \array_change_key_case($array, \CASE_UPPER);
}

class TestToPairs extends Test {
  public function run(): void {
    self::assertEqual(
      to_pairs(
        [
          'fer' => 4,
          'SADFf' => 9,
          ':}{ADSjj}' => 6,
          'foo BAR baz' => 97674,
          'KEK' => 1,
        ],
      ),
      [
        tuple('fer', 4),
        tuple('SADFf', 9),
        tuple(':}{ADSjj}', 6),
        tuple('foo BAR baz', 97674),
        tuple('KEK', 1),
      ],
    );
  }
}

function to_pairs<Tk, Tv>(array<Tk, Tv> $array): array<(Tk, Tv)> {
  $r = [];
  foreach ($array as $k => $v) {
    $r[] = tuple($k, $v);
  }
  return $r;
}

class TestFromPairs extends Test {
  public function run(): void {
    self::assertEqual(
      from_pairs(
        [
          tuple('fer', 4),
          tuple('SADFf', 9),
          tuple(':}{ADSjj}', 6),
          tuple('foo BAR baz', 97674),
          tuple('KEK', 1),
          tuple('fer', 9),
        ],
      ),
      [
        'fer' => 9,
        'SADFf' => 9,
        ':}{ADSjj}' => 6,
        'foo BAR baz' => 97674,
        'KEK' => 1,
      ],
    );
  }
}

function from_pairs<Tk as arraykey, Tv>(
  array<(Tk, Tv)> $pairs,
): array<Tk, Tv> {
  $r = [];
  foreach ($pairs as $p) {
    $r[$p[0]] = $p[1];
  }
  return $r;
}

class TestGet extends Test {
  public function run(): void {
    self::assertEqual(get(['a' => 5], 'a'), 5);
    self::assertEqual(get(['c' => 1, 'a' => 5], 'a'), 5);
    self::assertEqual(get(['c' => 1, 'a' => null], 'a'), null);

    $this->testErrors();
  }

  private function testErrors(): void {
    $errors = CaptureErrors::start();
    self::assertException(
      function() {
        get([], 'key');
      },
      "Key 'key' does not exist in array",
    );
    $errors->finish();

    // Make sure a PHP errors was emitted
    $errors = $errors->getErrors();
    self::assertEqual(\count($errors), 1);
    self::assertEqual($errors[0]->getMessage(), 'Undefined index: key');
    self::assertEqual($errors[0]->getCode(), 0);
    self::assertEqual($errors[0]->getSeverity(), \E_NOTICE);
  }
}

function get<Tk as arraykey, Tv>(array<Tk, Tv> $array, Tk $key): Tv {
  $res = $array[$key];
  if ($res === null && !key_exists($array, $key)) {
    throw new Exception("Key '$key' does not exist in array");
  }
  return $res;
}

class TestGetPair extends Test {
  public function run(): void {
    $array = ['a' => 'b', 'c' => 'd', 'e' => 'f'];
    self::assertEqual(get_pair($array, 0), tuple('a', 'b'));
    self::assertEqual(get_pair($array, 1), tuple('c', 'd'));
    self::assertEqual(get_pair($array, 2), tuple('e', 'f'));
    self::assertEqual(get_pair($array, -1), tuple('e', 'f'));
    self::assertEqual(get_pair($array, -2), tuple('c', 'd'));
    self::assertEqual(get_pair($array, -3), tuple('a', 'b'));
    self::assertException(
      function() use ($array) {
        get_pair($array, 3);
      },
      'Offset 3 is out of bounds in array of size 3',
    );
    self::assertException(
      function() use ($array) {
        get_pair($array, -4);
      },
      'Offset -4 is out of bounds in array of size 3',
    );
  }
}

/**
 * Get the key/value pair at the specified offset. Useful to get the first/last
 * key/value.
 *
 * get_pair($map, 0)[0] // first key
 * get_pair($map, 0)[1] // first value
 * get_pair($map, -1)[0] // last key
 * get_pair($map, -1)[1] // last value
 */
function get_pair<Tk, Tv>(array<Tk, Tv> $array, int $offset): (Tk, Tv) {
  $count = size($array);
  if ($offset < $count && $offset >= -$count) {
    foreach (slice_assoc($array, $offset, 1) as $k => $v) {
      return tuple($k, $v);
    }
  }
  throw new Exception(
    "Offset $offset is out of bounds in array of size $count",
  );
}

class TestSet extends Test {
  public function run(): void {
    $array = ['a' => 'b', 'c' => 'd'];
    self::assertEqual(set($array, 'a', 9), ['a' => 9, 'c' => 'd']);
    self::assertEqual(
      set($array, 'e', 9),
      ['a' => 'b', 'c' => 'd', 'e' => 9],
    );
  }
}

function set<Tk, Tv>(array<Tk, Tv> $array, Tk $key, Tv $val): array<Tk, Tv> {
  $array[$key] = $val;
  return $array;
}

class TestGetOrNull extends Test {
  public function run(): void {
    $array = ['a' => 'b'];
    self::assertEqual(get_or_null($array, 'a'), 'b');
    self::assertEqual(get_or_null($array, 'c'), null);
  }
}

function get_or_null<Tk, Tv>(array<Tk, Tv> $array, Tk $key): ?Tv {
  return _idx_isset($array, $key, null);
}

class TestGetOrDefault extends Test {
  public function run(): void {
    $array = ['a' => 'b', 'c' => null];
    self::assertEqual(get_or_default($array, 'a', 1), 'b');
    self::assertEqual(get_or_default($array, 'c', 1), null);
    self::assertEqual(get_or_default($array, 'e', 1), 1);
  }
}

function get_or_default<Tk, Tv>(
  array<Tk, Tv> $array,
  Tk $key,
  Tv $default,
): Tv {
  return _idx($array, $key, $default);
}

class TestGetIssetDefault extends Test {
  public function run(): void {
    $array = ['a' => 'b', 'c' => null];
    self::assertEqual(get_isset_default($array, 'a', 1), 'b');
    self::assertEqual(get_isset_default($array, 'c', 1), 1);
    self::assertEqual(get_isset_default($array, 'e', 1), 1);
  }
}

/**
 * Same as get_or_default() but fills a value of NULL with the default.
 */
function get_isset_default<Tk, Tv>(
  array<Tk, Tv> $array,
  Tk $key,
  Tv $default,
): Tv {
  return _idx_isset($array, $key, $default);
}

class TestKeyExists extends Test {
  public function run(): void {
    $array = ['a' => 'b', 'c' => null];
    self::assertEqual(key_exists($array, 'a'), true);
    self::assertEqual(key_exists($array, 'c'), true);
    self::assertEqual(key_exists($array, 'e'), false);
  }
}

function key_exists<Tk>(array<Tk, mixed> $array, Tk $key): bool {
  return \array_key_exists($key, $array);
}

class TestKeyIsset extends Test {
  public function run(): void {
    $array = ['a' => 'b', 'c' => null];
    self::assertEqual(key_isset($array, 'a'), true);
    self::assertEqual(key_isset($array, 'c'), false);
    self::assertEqual(key_isset($array, 'e'), false);
  }
}

/**
 * Same as key_exists() except considers a key assigned to NULL not to exist.
 */
function key_isset<Tk>(array<Tk, mixed> $array, Tk $key): bool {
  return get_or_null($array, $key) !== null;
}

class TestGetOffset extends Test {
  public function run(): void {
    $array = ['a', 'b', 'c', null];
    self::assertEqual(get_offset($array, 0), 'a');
    self::assertEqual(get_offset($array, 1), 'b');
    self::assertEqual(get_offset($array, 2), 'c');
    self::assertEqual(get_offset($array, 3), null);
    self::assertEqual(get_offset($array, -4), 'a');
    self::assertEqual(get_offset($array, -3), 'b');
    self::assertEqual(get_offset($array, -2), 'c');
    self::assertEqual(get_offset($array, -1), null);

    self::assertException(
      function() use ($array) {
        get_offset($array, 8);
      },
      'Index 8 out of bounds in array of length 4',
    );
  }
}

function get_offset<T>(array<T> $v, int $i): T {
  $l = \count($v);
  if ($i < 0) {
    $i += $l;
  }
  if ($i < 0 || $i >= $l) {
    throw new Exception("Index $i out of bounds in array of length $l");
  }
  return $v[$i];
}

class TestSetOffset extends Test {
  public function run(): void {
    $array = ['a', 'c', null];
    self::assertEqual(set_offset($array, 0, 'kek'), ['kek', 'c', null]);
    self::assertEqual(set_offset($array, 1, 'kek'), ['a', 'kek', null]);
    self::assertEqual(set_offset($array, 2, 'kek'), ['a', 'c', 'kek']);
    self::assertEqual(set_offset($array, -3, 'kek'), ['kek', 'c', null]);
    self::assertEqual(set_offset($array, -2, 'kek'), ['a', 'kek', null]);
    self::assertEqual(set_offset($array, -1, 'kek'), ['a', 'c', 'kek']);

    self::assertException(
      function() use ($array) {
        set_offset($array, 8, 'kek');
      },
      'Index 8 out of bounds in array of length 3',
    );
  }
}

function set_offset<T>(array<T> $v, int $i, T $x): array<T> {
  $l = \count($v);
  if ($i < 0) {
    $i += $l;
  }
  if ($i < 0 || $i >= $l) {
    throw new Exception("Index $i out of bounds in array of length $l");
  }
  $v[$i] = $x;
  return $v;
}

// TODO Add functions for
// - array_column($a, $valcol, $keycol)
// - array_column($a, null, $keycol)

function column<Tk as arraykey, Tv>(
  array<array<Tk, Tv>> $arrays,
  Tk $key,
): array<Tv> {
  return \array_column($arrays, $key);
}

function combine<Tk, Tv>(array<Tk> $keys, array<Tv> $values): array<Tk, Tv> {
  return \array_combine($keys, $values);
}

function separate<Tk, Tv>(array<Tk, Tv> $array): (array<Tk>, array<Tv>) {
  $ks = [];
  $vs = [];
  foreach ($array as $k => $v) {
    $ks[] = $k;
    $vs[] = $v;
  }
  return tuple($ks, $vs);
}

function from_keys<Tk as arraykey, Tv>(
  array<Tk> $keys,
  Tv $value,
): array<Tk, Tv> {
  return \array_fill_keys($keys, $value);
}

function unique<T as arraykey>(array<T> $values): array<T> {
  // Faster than array_unique(), and importantly doesn't change strings to ints
  // like keys(from_keys($values)) would.
  return values(combine($values, $values));
}

function flip<Tk as arraykey, Tv as arraykey>(
  array<Tk, Tv> $array,
): array<Tv, Tk> {
  return \array_flip($array);
}

function flip_count<T as arraykey>(array<arraykey, T> $values): array<T, int> {
  return \array_count_values($values);
}

function flip_all<Tk as arraykey, Tv as arraykey>(
  array<Tk, Tv> $array,
): array<Tv, array<Tk>> {
  $ret = [];
  foreach ($array as $k => $v) {
    $ret[$v][] = $k;
  }
  return $ret;
}

function keys<Tk>(array<Tk, mixed> $array): array<Tk> {
  return \array_keys($array);
}

function keys_strings(array<arraykey, mixed> $array): array<string> {
  return map(keys($array), $k ==> (string) $k);
}

function values<Tv>(array<mixed, Tv> $array): array<Tv> {
  return \array_values($array);
}

/**
 * If a key exists in both arrays, the value from the second array is used.
 */
function union_keys<Tk, Tv>(
  array<Tk, Tv> $a,
  array<Tk, Tv> $b,
): array<Tk, Tv> {
  return \array_replace($a, $b);
}

/**
 * If a key exists in multiple arrays, the value from the later array is used.
 */
function union_keys_all<Tk, Tv>(array<array<Tk, Tv>> $arrays): array<Tk, Tv> {
  return $arrays ? \call_user_func_array('array_replace', $arrays) : [];
}

/**
 * Returns an array with only values that exist in both arrays, using keys from
 * the first array.
 */
function intersect<Tk, Tv as arraykey>(
  array<Tk, Tv> $a,
  array<mixed, Tv> $b,
): array<Tk, Tv> {
  return \array_intersect($a, $b);
}

/**
 * Returns an array with only (key, value) pairs that exist in both arrays.
 */
function intersect_assoc<Tk as arraykey, Tv as arraykey>(
  array<Tk, Tv> $a,
  array<Tk, Tv> $b,
): array<Tk, Tv> {
  return \array_intersect_assoc($a, $b);
}

/**
 * Returns an array with only keys that exist in both arrays, using values from
 * the first array.
 */
function intersect_keys<Tk as arraykey, Tv>(
  array<Tk, Tv> $a,
  array<Tk, mixed> $b,
): array<Tk, Tv> {
  return \array_intersect_key($a, $b);
}

/**
 * Returns an array with values that exist in the first array but not the
 * second, using keys from the first array.
 */
function diff<Tk, Tv as arraykey>(
  array<Tk, Tv> $a,
  array<mixed, Tv> $b,
): array<Tk, Tv> {
  return \array_diff($a, $b);
}

/**
 * Returns an array with (key, value) pairs that exist in the first array
 * but not the second.
 */
function diff_assoc<Tk, Tv as arraykey>(
  array<Tk, Tv> $a,
  array<Tk, Tv> $b,
): array<Tk, Tv> {
  return \array_diff_assoc($a, $b);
}

/**
 * Returns an array with keys that exist in the first array but not the second,
 * using values from the first array.
 */
function diff_keys<Tk as arraykey, Tv>(
  array<Tk, Tv> $a,
  array<Tk, mixed> $b,
): array<Tk, Tv> {
  return \array_intersect_key($a, $b);
}

/**
 * Extract multiple keys from a map at once.
 */
function select<Tk, Tv>(array<Tk, Tv> $array, array<Tk> $keys): array<Tv> {
  return map($keys, $key ==> $array[$key]);
}

/**
 * Extract multiple keys from a map at once, returning NULL for a key that
 * doesn't exist.
 */
function select_or_null<Tk, Tv>(
  array<Tk, Tv> $array,
  array<Tk> $keys,
): array<?Tv> {
  return map(
    $keys,
    function($key) use ($array) {
      return get_or_null($array, $key);
    },
  );
}

function zip<Ta, Tb>(array<Ta> $a, array<Tb> $b): array<(Ta, Tb)> {
  $r = [];
  $l = min(count($a), count($b));
  for ($i = 0; $i < $l; $i++) {
    $r[] = tuple($a[$i], $b[$i]);
  }
  return $r;
}

function zip_assoc<Tk, Ta, Tb>(
  array<Tk, Ta> $a,
  array<Tk, Tb> $b,
): array<Tk, (Ta, Tb)> {
  $ret = [];
  foreach ($a as $k => $v) {
    if (key_exists($b, $k)) {
      $ret[$k] = tuple($v, $b[$k]);
    }
  }
  return $ret;
}

function unzip<Ta, Tb>(array<(Ta, Tb)> $x): (array<Ta>, array<Tb>) {
  $a = [];
  $b = [];
  foreach ($x as $p) {
    $a[] = $p[0];
    $b[] = $p[1];
  }
  return tuple($a, $b);
}

function unzip_assoc<Tk, Ta, Tb>(
  array<Tk, (Ta, Tb)> $array,
): (array<Tk, Ta>, array<Tk, Tb>) {
  $a = [];
  $b = [];
  foreach ($array as $k => $v) {
    $a[$k] = $v[0];
    $b[$k] = $v[1];
  }
  return tuple($a, $b);
}

/**
 * Useful to force an empty array to be considered
 * as a vector and not a hash table.
 */
function new_array<T>(): array<T> {
  return [];
}

/**
 * Useful to force an empty array to be considered
 * as a hash table and not a vector.
 */
function new_assoc<Tk, Tv>(): array<Tk, Tv> {
  return [];
}

function transpose<T>(array<array<T>> $arrays): array<array<T>> {
  $ret = new_array();
  foreach ($arrays as $array) {
    $i = 0;
    foreach ($array as $v) {
      $ret[$i++][] = $v;
    }
  }
  return $ret;
}

function transpose_assoc<Tk1, Tk2, Tv>(
  array<Tk1, array<Tk2, Tv>> $arrays,
): array<Tk2, array<Tk1, Tv>> {
  $ret = [];
  foreach ($arrays as $k1 => $array) {
    foreach ($array as $k2 => $v) {
      $ret[$k2][$k1] = $v;
    }
  }
  return $ret;
}

function transpose_num_assoc<Tk, Tv>(
  array<array<Tk, Tv>> $arrays,
): array<Tk, array<Tv>> {
  $ret = [];
  foreach ($arrays as $array) {
    foreach ($array as $k => $v) {
      $ret[$k][] = $v;
    }
  }
  return $ret;
}

function transpose_assoc_num<Tk, Tv>(
  array<Tk, array<Tv>> $arrays,
): array<array<Tk, Tv>> {
  $ret = new_array();
  foreach ($arrays as $k => $array) {
    $i = 0;
    foreach ($array as $v) {
      $ret[$i++][$k] = $v;
    }
  }
  return $ret;
}

function shuffle<T>(array<T> $array): array<T> {
  \shuffle($array);
  return $array;
}

class TestStringShuffle extends Test {
  public function run(): void {
    self::assertEqual(length(shuffle_string("abc")), 3);
  }
}

function shuffle_string(string $string): string {
  return \str_shuffle($string);
}

class TestReverseString extends Test {
  public function run(): void {
    self::assertEqual(reverse_string("abc"), 'cba');
    self::assertEqual(reverse_string(""), '');
  }
}

function reverse<T>(array<T> $array): array<T> {
  return \array_reverse($array, false);
}

function reverse_assoc<Tk, Tv>(array<Tk, Tv> $array): array<Tk, Tv> {
  return \array_reverse($array, true);
}

function reverse_string(string $string): string {
  return \strrev($string);
}

function chunk<T>(array<T> $array, int $size): array<array<T>> {
  if ($size < 1) {
    throw new Exception("Chunk size must be >= 1");
  }
  return \array_chunk($array, $size, false);
}

function chunk_assoc<Tk, Tv>(
  array<Tk, Tv> $array,
  int $size,
): array<array<Tk, Tv>> {
  if ($size < 1) {
    throw new Exception("Chunk size must be >= 1");
  }
  return \array_chunk($array, $size, true);
}

class TestStringChunk extends Test {
  public function run(): void {
    self::assertEqual(chunk_string('abc', 1), ['a', 'b', 'c']);
    self::assertEqual(chunk_string('abc', 2), ['ab', 'c']);
    self::assertEqual(chunk_string('abc', 3), ['abc']);
  }
}

function chunk_string(string $string, int $size): array<string> {
  if ($size < 1) {
    throw new \Exception("Chunk size must be >= 1");
  }
  return Exception::assertArray(\str_split($string, $size));
}

class TestStringRepeat extends Test {
  public function run(): void {
    self::assertEqual(repeat_string('123', 3), '123123123');
  }
}

function repeat<T>(T $value, int $count): array<T> {
  if (!$count)
    return [];
  return \array_fill(0, $count, $value);
}

function repeat_string(string $string, int $count): string {
  return \str_repeat($string, $count);
}

class TestStringSlice extends Test {
  public function run(): void {
    self::assertEqual(slice('abc', 1, 1), 'b');
    self::assertEqual(slice('abc', -1, 1), 'c');
    self::assertEqual(slice('abc', 1, -1), 'b');
    self::assertEqual(slice('abc', 1), 'bc');
    self::assertEqual(slice('abc', -1), 'c');
  }
}

function slice(string $string, int $offset, ?int $length = NULL_INT): string {
  $ret = \substr($string, $offset, if_null($length, 0x7FFFFFFF));
  // \substr() returns false "on failure".
  return $ret === false ? '' : $ret;
}

function slice_array<T>(
  array<T> $array,
  int $offset,
  ?int $length = NULL_INT,
): array<T> {
  return \array_slice($array, $offset, $length);
}

function slice_assoc<Tk, Tv>(
  array<Tk, Tv> $array,
  int $offset,
  ?int $length = NULL_INT,
): array<Tk, Tv> {
  return \array_slice($array, $offset, $length, true);
}

class TestStringSplice extends Test {
  public function run(): void {
    self::assertEqual(splice('abc', 1, 1), 'ac');
    self::assertEqual(splice('abc', 1, 1, 'lol'), 'alolc');
  }
}

function splice(
  string $string,
  int $offset,
  ?int $length = NULL_INT,
  string $replacement = '',
): string {
  return \substr_replace(
    $string,
    $replacement,
    $offset,
    if_null($length, 0x7FFFFFFF),
  );
}

/**
 * Returns a pair of (new list, removed elements).
 */
function splice_array<T>(
  array<T> $array,
  int $offset,
  ?int $length = NULL_INT,
  array<T> $replacement = [],
): (array<T>, array<T>) {
  $removed = \array_splice($array, $offset, $length, $replacement);
  return tuple($array, $removed);
}

class TestStringSearch extends Test {
  public function run(): void {
    self::assertEqual(find('a', 'a'), 0);
    self::assertEqual(find('a', 'a', 1), NULL_INT);
    self::assertEqual(find('a', 'a', -1), 0);
    self::assertEqual(find('abc', 'a'), 0);
    self::assertEqual(find('abc', 'b'), 1);
    self::assertEqual(find('abc', 'c'), 2);
    self::assertEqual(find('abc', 'a', -2), NULL_INT);
    self::assertEqual(find('abc', 'b', -2), 1);
    self::assertEqual(find('abc', 'c', -2), 2);
    self::assertEqual(find('abbb', 'bb'), 1);
    self::assertEqual(find('abbb', 'bb', 2), 2);

    self::assertEqual(find_last('a', 'a'), 0);
    self::assertEqual(find_last('a', 'a', 1), NULL_INT);
    self::assertEqual(find_last('a', 'a', -1), 0);
    self::assertEqual(find_last('aba', 'a'), 2);
    self::assertEqual(find_last('aba', 'b'), 1);
    self::assertEqual(find_last('aba', 'c'), NULL_INT);
    self::assertEqual(find_last('aba', 'a', -2), 0);
    self::assertEqual(find_last('aba', 'b', -2), 1);
    self::assertEqual(find_last('aba', 'c', -2), NULL_INT);
    self::assertEqual(find_last('abbb', 'bb'), 2);
    self::assertEqual(find_last('abbb', 'bb', 2), 2);
  }
}

function find(
  string $haystack,
  string $needle,
  int $offset = 0,
  bool $caseInsensitive = false,
): ?int {
  // strpos()/stripos() support negative lengths as of PHP 7.1.0
  if (\PHP_VERSION_ID < 70100 && $offset < 0) {
    $offset += length($haystack);
  }
  $ret =
    $caseInsensitive
      ? \stripos($haystack, $needle, $offset)
      : \strpos($haystack, $needle, $offset);
  return $ret === false ? null : $ret;
}

function find_last(
  string $haystack,
  string $needle,
  int $offset = 0,
  bool $caseInsensitive = false,
): ?int {
  // Unlike strpos() and stripos(), strrpos() and strripos() both support
  // negative offsets in all PHP versions.
  $ret =
    $caseInsensitive
      ? \strripos($haystack, $needle, $offset)
      : \strrpos($haystack, $needle, $offset);
  return $ret === false ? null : $ret;
}

function find_count(string $haystack, string $needle, int $offset = 0): int {
  // substr_count() supports negative lengths as of PHP 7.1.0
  if (\PHP_VERSION_ID < 70100 && $offset < 0) {
    $offset += length($haystack);
  }
  return \substr_count($haystack, $needle, $offset);
}

function contains(string $haystack, string $needle, int $offset = 0): bool {
  return find($haystack, $needle, $offset) !== null;
}

function length(string $string): int {
  return \strlen($string);
}

function count(array<mixed, mixed> $array): int {
  return \count($array);
}

function size(array<mixed, mixed> $array): int {
  return \count($array);
}

function find_key<Tk, Tv>(array<Tk, Tv> $array, Tv $value): ?Tk {
  $ret = \array_search($value, $array, true);
  return $ret === false ? new_null() : $ret;
}

function find_keys<Tk, Tv>(array<Tk, Tv> $array, Tv $value): array<Tk> {
  return \array_keys($array, $value, true);
}

function find_last_key<Tk, Tv>(array<Tk, Tv> $array, Tv $value): ?Tk {
  $iter = new ArrayIterator($array);
  for ($iter->end(); $iter->valid(); $iter->prev()) {
    if ($iter->current() === $value) {
      return $iter->key();
    }
  }
  return null;
}

function in<T>(T $value, array<mixed, T> $array): bool {
  return \in_array($value, $array, true);
}

class TestToHex extends Test {
  public function run(): void {
    self::assertEqual(to_hex("\x00\xff\x20"), "00ff20");
  }
}

function to_hex(string $string): string {
  return \bin2hex($string);
}

class TestFromHex extends Test {
  public function run(): void {
    self::assertEqual(from_hex("00ff20"), "\x00\xff\x20");
    self::assertEqual(from_hex("00Ff20"), "\x00\xff\x20");
  }
}

function from_hex(string $string): string {
  return Exception::assertString(\hex2bin($string));
}

class TestToLower extends Test {
  public function run(): void {
    self::assertEqual(to_lower("ABC.1.2.3"), "abc.1.2.3");
  }
}

function to_lower(string $string): string {
  return \strtolower($string);
}

class TestToUpper extends Test {
  public function run(): void {
    self::assertEqual(to_upper("abc.1.2.3"), "ABC.1.2.3");
  }
}

function to_upper(string $string): string {
  return \strtoupper($string);
}

/**
 * ASCII space characters.
 */
const string SPACE_CHARS = " \t\r\n\v\f";

/**
 * The default characters trimmed by PHP's trim(), ltrim() and rtrim().
 */
const string TRIM_CHARS = " \t\r\n\v\x00";

function trim(string $string, string $chars = TRIM_CHARS): string {
  return \trim($string, $chars);
}

function trim_left(string $string, string $chars = TRIM_CHARS): string {
  return \ltrim($string, $chars);
}

function trim_right(string $string, string $chars = TRIM_CHARS): string {
  return \rtrim($string, $chars);
}

/**
 * Decode the given utf8 string, convert code points 0-255 to raw bytes
 * and discard code points >255.
 */
function decode_utf8(string $s): string {
  return \utf8_decode($s);
}

/**
 * Treat each byte as a unicode code point between 0 and 255 and encode these
 * characters as utf8.
 */
function encode_utf8(string $s): string {
  return \utf8_encode($s);
}

function is_utf8(string $s): bool {
  // My testing reveals this is about 4.5 times faster than mb_check_encoding()
  // on 1,000,000 random 4 byte strings, and produces the exact same result.
  // On 10,000 10KB strings, it was about 250 times faster. :O
  return (bool) \preg_match('//u', $s);
}

/**
 * Replaces the null byte with "\0" and prepends a backslash before single
 * quotes, double quotes and backslashes.
 */
function add_slashes(string $s): string {
  return \addslashes($s);
}

/**
 * Removes unescaped backslashes. "\a" => "a", "\\" => "\", "\0" => "<NUL>", ..
 */
function strip_slashes(string $s): string {
  return \stripslashes($s);
}

class TestStringSplit extends Test {
  public function run(): void {
    self::assertEqual(split(''), []);
    self::assertEqual(split('a'), ['a']);
    self::assertEqual(split('abc'), ['a', 'b', 'c']);

    self::assertEqual(split('', '', 1), []);
    self::assertEqual(split('a', '', 1), ['a']);
    self::assertEqual(split('abc', '', 1), ['abc']);
    self::assertEqual(split('abc', '', 2), ['a', 'bc']);
    self::assertEqual(split('abc', '', 3), ['a', 'b', 'c']);

    self::assertEqual(split('', 'b'), ['']);
    self::assertEqual(split('abc', 'b'), ['a', 'c']);

    self::assertEqual(split('abc', 'b', 1), ['abc']);
    self::assertEqual(split('abc', 'b', 2), ['a', 'c']);
  }
}

/**
 * Split a string on a delimiter. If the delimiter is the empty string, splits
 * the string into individual characters. $limit will limit the number of
 * returned elements, with the remainder of the string included in the last
 * element.
 */
function split(
  string $string,
  string $delimiter = '',
  ?int $limit = NULL_INT,
): array<string> {
  $limit = if_null($limit, 0x7FFFFFFF);
  // TODO Add support for negative limits with the same semantics as explode().
  if ($limit < 1) {
    throw new Exception("Limit must be >= 1, $limit given");
  }
  // \explode() doesn't accept an empty delimiter
  if ($delimiter === '') {
    $length = length($string);
    if ($length == 0) {
      // The only case where we return an empty array is if both the delimiter
      // and string are empty, i.e. if they are tring to split the string
      // into characters and the string is empty.
      return [];
    }
    if ($limit == 1) {
      return [$string];
    }
    if ($length > $limit) {
      return push(
        \str_split(slice($string, 0, $limit - 1)),
        slice($string, $limit - 1),
      );
    }
    return \str_split($string);
  }
  return \explode($delimiter, $string, $limit);
}

/**
 * Split a string into lines terminated by \n or \r\n.
 * A final line terminator is optional.
 */
function split_lines(string $string): array<string> {
  $lines = split($string, "\n");
  // Remove a final \r at the end of any lines
  foreach ($lines as $i => $line) {
    if (slice($line, -1) === "\r") {
      $lines[$i] = slice($line, 0, -1);
    }
  }
  // Remove a final empty line
  if ($lines && get_offset($lines, -1) === '') {
    $lines = slice_array($lines, 0, -1);
  }
  return $lines;
}

class TestStringSplitAt extends Test {
  public function run(): void {
    self::assertEqual(split_at('abc', -4), tuple('', 'abc'));
    self::assertEqual(split_at('abc', -3), tuple('', 'abc'));
    self::assertEqual(split_at('abc', -2), tuple('a', 'bc'));
    self::assertEqual(split_at('abc', -1), tuple('ab', 'c'));
    self::assertEqual(split_at('abc', 0), tuple('', 'abc'));
    self::assertEqual(split_at('abc', 1), tuple('a', 'bc'));
    self::assertEqual(split_at('abc', 2), tuple('ab', 'c'));
    self::assertEqual(split_at('abc', 3), tuple('abc', ''));
    self::assertEqual(split_at('abc', 4), tuple('abc', ''));
  }
}

/**
 * Split the string in two at the specified offset.
 * Negative offsets are supported.
 */
function split_at(string $string, int $offset): (string, string) {
  return tuple(slice($string, 0, $offset), slice($string, $offset));
}

/**
 * Split the array at the specified offset.
 * Negative offsets are supported.
 */
function split_array_at<T>(
  array<T> $array,
  int $offset,
): (array<T>, array<T>) {
  return tuple(slice_array($array, 0, $offset), slice_array($array, $offset));
}

class TestStringJoin extends Test {
  public function run(): void {
    self::assertEqual(join([]), '');
    self::assertEqual(join(['abc']), 'abc');
    self::assertEqual(join(['a', 'bc']), 'abc');

    self::assertEqual(join([], ','), '');
    self::assertEqual(join(['abc'], ','), 'abc');
    self::assertEqual(join(['a', 'bc'], ','), 'a,bc');
  }
}

function join(array<string> $strings, string $delimiter = ''): string {
  return \implode($delimiter, $strings);
}

/**
 * Join lines back together with the given line separator. A final
 * separator is included in the output.
 */
function join_lines(array<string> $lines, string $nl = "\n"): string {
  return $lines ? join($lines, $nl).$nl : '';
}

class TestStringReplace extends Test {
  public function run(): void {
    self::assertEqual(replace_count('abc', 'b', 'lol'), tuple('alolc', 1));
    self::assertEqual(replace_count('abc', 'B', 'lol'), tuple('abc', 0));
    self::assertEqual(
      replace_count('abc', 'B', 'lol', true),
      tuple('alolc', 1),
    );
  }
}

function replace(
  string $subject,
  string $search,
  string $replace,
  bool $caseInsensitive = false,
): string {
  return Exception::assertString(
    $caseInsensitive
      ? \str_ireplace($search, $replace, $subject)
      : \str_replace($search, $replace, $subject),
  );
}

function replace_count(
  string $subject,
  string $search,
  string $replace,
  bool $caseInsensitive = false,
): (string, int) {
  $count = 0;
  $result = Exception::assertString(
    $caseInsensitive
      ? \str_ireplace($search, $replace, $subject, $count)
      : \str_replace($search, $replace, $subject, $count),
  );
  return tuple($result, $count);
}

class TestStringPad extends Test {
  public function run(): void {
    self::assertEqual(pad('abc', 3), 'abc');
    self::assertEqual(pad('abc', 4), 'abc ');
    self::assertEqual(pad('abc', 5), ' abc ');
    self::assertEqual(pad('abc', 6), ' abc  ');
    self::assertEqual(pad('1', 3, 'ab'), 'a1a');
    self::assertEqual(pad('1', 4, 'ab'), 'a1ab');

    self::assertEqual(pad_left('abc', 3), 'abc');
    self::assertEqual(pad_left('abc', 4), ' abc');
    self::assertEqual(pad_left('abc', 5), '  abc');
    self::assertEqual(pad_left('abc', 6), '   abc');
    self::assertEqual(pad_left('1', 3, 'ab'), 'ab1');
    self::assertEqual(pad_left('1', 4, 'ab'), 'aba1');

    self::assertEqual(pad_right('abc', 3), 'abc');
    self::assertEqual(pad_right('abc', 4), 'abc ');
    self::assertEqual(pad_right('abc', 5), 'abc  ');
    self::assertEqual(pad_right('abc', 6), 'abc   ');
    self::assertEqual(pad_right('1', 3, 'ab'), '1ab');
    self::assertEqual(pad_right('1', 4, 'ab'), '1aba');
  }
}

function pad(string $string, int $length, string $pad = ' '): string {
  return \str_pad($string, $length, $pad, \STR_PAD_BOTH);
}

function pad_array<T>(array<T> $array, int $size, T $value): array<T> {
  return \array_pad($array, $size, $value);
}

function pad_left(string $string, int $length, string $pad = ' '): string {
  return \str_pad($string, $length, $pad, \STR_PAD_LEFT);
}

function pad_right(string $string, int $length, string $pad = ' '): string {
  return \str_pad($string, $length, $pad, \STR_PAD_RIGHT);
}

class TestStringSetLength extends Test {
  public function run(): void {
    self::assertEqual(set_length('ab', -3), '');
    self::assertEqual(set_length('ab', -2), '');
    self::assertEqual(set_length('ab', -1), 'a');
    self::assertEqual(set_length('ab', 0), '');
    self::assertEqual(set_length('ab', 1), 'a');
    self::assertEqual(set_length('ab', 2), 'ab');
    self::assertEqual(set_length('ab', 3), 'ab ');
    self::assertEqual(set_length('ab', 4), 'ab  ');
    self::assertEqual(set_length('ab', 3, '12'), 'ab1');
    self::assertEqual(set_length('ab', 4, '12'), 'ab12');
    self::assertEqual(set_length('ab', 5, '12'), 'ab121');
    self::assertEqual(set_length('ab', 6, '12'), 'ab1212');
  }
}

/**
 * Set a string's length, padding with the specified pad string and discarding
 * bytes in excess. If length is negative, that many characters will be removed
 * from the end.
 */
function set_length(string $string, int $length, string $pad = ' '): string {
  $string = slice($string, 0, $length);
  $string = pad_right($string, $length, $pad);
  return $string;
}

class TestStringCharCode extends Test {
  public function run(): void {
    self::assertEqual(from_char_code(128), "\x80");
    self::assertEqual(from_char_code(0), "\x00");
    self::assertEqual(from_char_code(255), "\xFF");

    self::assertEqual(char_code_at('a'), 97);
    self::assertEqual(char_code_at('a99'), 97);
  }
}

function from_char_code(int $ascii): string {
  if ($ascii < 0 || $ascii >= 256) {
    throw new Exception(
      'ASCII character code must be >= 0 and < 256: '.$ascii,
    );
  }

  return \chr($ascii);
}

function char_at(string $s, int $i = 0): string {
  $l = \strlen($s);
  // Allow caller to specify negative offsets for characters from the end of
  // the string
  if ($i < 0) {
    $i += $l;
  }
  if ($i < 0 || $i >= $l) {
    throw new Exception(
      "String offset $i out of bounds in string of length $l",
    );
  }
  return $s[$i];
}

function char_code_at(string $string, int $offset = 0): int {
  return \ord(char_at($string, $offset));
}

class TestStringCompare extends Test {
  public function run(): void {
    self::assertEqual(str_cmp('a', 'a'), 0);
    self::assertEqual(str_cmp('a', 'A'), 1);
    self::assertEqual(str_cmp('', ''), 0);
    self::assertEqual(str_cmp('', 'a'), -1);
    self::assertEqual(str_cmp('a', ''), 1);

    self::assertEqual(str_cmp('a', 'a', true), 0);
    self::assertEqual(str_cmp('a', 'A', true), 0);
    self::assertEqual(str_cmp('', '', true), 0);
    self::assertEqual(str_cmp('', 'a', true), -1);
    self::assertEqual(str_cmp('a', '', true), 1);

    self::assertEqual(str_eq('a', 'a'), true);
    self::assertEqual(str_eq('a', 'A'), false);
    self::assertEqual(str_eq('', ''), true);
    self::assertEqual(str_eq('', 'a'), false);
    self::assertEqual(str_eq('a', ''), false);

    self::assertEqual(str_eq('a', 'a', true), true);
    self::assertEqual(str_eq('a', 'A', true), true);
    self::assertEqual(str_eq('', '', true), true);
    self::assertEqual(str_eq('', 'a', true), false);
    self::assertEqual(str_eq('a', '', true), false);
  }
}

function str_cmp(
  string $a,
  string $b,
  bool $caseInsensitive = false,
  bool $natural = false,
): int {
  $ret =
    $caseInsensitive
      ? ($natural ? \strnatcasecmp($a, $b) : \strcasecmp($a, $b))
      : ($natural ? \strnatcmp($a, $b) : \strcmp($a, $b));
  return sign($ret);
}

function str_eq(
  string $a,
  string $b,
  bool $caseInsensitive = false,
  bool $natural = false,
): bool {
  return str_cmp($a, $b, $caseInsensitive, $natural) == 0;
}

class TestStringStartsWith extends Test {
  public function run(): void {
    self::assertEqual(starts_with('abbb', 'ab'), true);
    self::assertEqual(starts_with('abbb', 'bb'), false);
    self::assertEqual(starts_with('abbb', ''), true);
    self::assertEqual(starts_with('', ''), true);
    self::assertEqual(starts_with('', 'a'), false);
  }
}

function starts_with(string $string, string $prefix): bool {
  return slice($string, 0, length($prefix)) === $prefix;
}

class TestStringEndsWith extends Test {
  public function run(): void {
    self::assertEqual(ends_with('abbb', 'bb'), true);
    self::assertEqual(ends_with('abbb', 'ba'), false);
    self::assertEqual(ends_with('abbb', ''), true);
    self::assertEqual(ends_with('', ''), true);
    self::assertEqual(ends_with('', 'a'), false);
  }
}

function ends_with(string $string, string $suffix): bool {
  $length = length($suffix);
  return $length ? slice($string, -$length) === $suffix : true;
}

<?hh // strict

namespace HackUtils;

function keys_to_lower<Tk, Tv>(array<Tk, Tv> $array): array<Tk, Tv> {
  return \array_change_key_case($array, \CASE_LOWER);
}

function keys_to_uppper<Tk, Tv>(array<Tk, Tv> $array): array<Tk, Tv> {
  return \array_change_key_case($array, \CASE_UPPER);
}

function to_pairs<Tk, Tv>(array<Tk, Tv> $map): array<(Tk, Tv)> {
  $r = [];
  foreach ($map as $k => $v) {
    $r[] = tuple($k, $v);
  }
  return $r;
}

function from_pairs<Tk, Tv>(array<(Tk, Tv)> $pairs): array<Tk, Tv> {
  $r = [];
  foreach ($pairs as $p) {
    $r[$p[0]] = $p[1];
  }
  return $r;
}

function get_key<Tk as arraykey, Tv>(array<Tk, Tv> $map, Tk $key): Tv {
  $res = $map[$key];
  if ($res === null && !key_exists($map, $key)) {
    throw new \Exception("Key '$key' does not exist in map");
  }
  return $res;
}

function set_key<Tk, Tv>(array<Tk, Tv> $map, Tk $key, Tv $val): array<Tk, Tv> {
  $map[$key] = $val;
  return $map;
}

function get_key_or_null<Tk, Tv>(array<Tk, Tv> $map, Tk $key): ?Tv {
  return $map[$key] ?? new_null();
}

function get_key_or_default<Tk, Tv>(
  array<Tk, Tv> $map,
  Tk $key,
  Tv $default,
): Tv {
  return key_exists($map, $key) ? $map[$key] : $default;
}

function key_exists<Tk>(array<Tk, mixed> $map, Tk $key): bool {
  return \array_key_exists($key, $map);
}

/**
 * The key of a map is actually a string, but PHP converts intish strings to
 * ints. Use this function to convert them back.
 */
function fixkey(arraykey $key): string {
  return $key.'';
}

function fixkeys(array<arraykey> $keys): array<string> {
  return map($keys, $key ==> $key.'');
}

function column<Tk as arraykey, Tv>(
  array<array<Tk, Tv>> $maps,
  Tk $key,
): array<Tv> {
  return \array_column($maps, $key);
}

function combine<Tk, Tv>(array<Tk> $keys, array<Tv> $values): array<Tk, Tv> {
  return \array_combine($keys, $values);
}

function separate<Tk, Tv>(array<Tk, Tv> $map): (array<Tk>, array<Tv>) {
  $ks = [];
  $vs = [];
  foreach ($map as $k => $v) {
    $ks[] = $k;
    $vs[] = $v;
  }
  return tuple($ks, $vs);
}

function from_keys<Tk, Tv>(array<Tk> $keys, Tv $value): array<Tk, Tv> {
  return \array_fill_keys($keys, $value);
}

function flip<Tk as arraykey, Tv as arraykey>(
  array<Tk, Tv> $map,
): array<Tv, Tk> {
  return \array_flip($map);
}

function flip_count<T as arraykey>(array<arraykey, T> $values): array<T, int> {
  return \array_count_values($values);
}

function keys<Tk>(array<Tk, mixed> $map): array<Tk> {
  return \array_keys($map);
}

function keys_strings(array<arraykey, mixed> $map): array<string> {
  return map(keys($map), $k ==> ''.$k);
}

function values<Tv>(array<mixed, Tv> $map): array<Tv> {
  return \array_values($map);
}

/**
 * If a key exists in both arrays, the value from the second array is used.
 */
function union_keys<Tk, Tv>(array<Tk, Tv> $a, array<Tk, Tv> $b): array<Tk, Tv> {
  return \array_replace($a, $b);
}

/**
 * If a key exists in multiple arrays, the value from the later array is used.
 */
function union_keys_all<Tk, Tv>(array<array<Tk, Tv>> $maps): array<Tk, Tv> {
  return \call_user_func_array('array_replace', $maps);
}

function intersect<T as arraykey>(array<T> $a, array<T> $b): array<T> {
  return \array_values(\array_intersect($a, $b));
}

/**
 * Returns an array with only keys that exist in both arrays, using values from
 * the first array.
 */
function intersect_keys<Tk as arraykey, Tv>(
  array<Tk, Tv> $a,
  array<Tk, Tv> $b,
): array<Tk, Tv> {
  return \array_intersect_key($a, $b);
}

function diff<T as arraykey>(array<T> $a, array<T> $b): array<T> {
  return \array_values(\array_diff($a, $b));
}

/**
 * Returns an array with keys that exist in the first array but not the second,
 * using values from the first array.
 */
function diff_keys<Tk as arraykey, Tv>(
  array<Tk, Tv> $a,
  array<Tk, Tv> $b,
): array<Tk, Tv> {
  return \array_intersect_key($a, $b);
}

/**
 * Extract multiple keys from a map at once.
 */
function select<Tk, Tv>(array<Tk, Tv> $map, array<Tk> $keys): array<Tv> {
  $ret = [];
  foreach ($keys as $key) {
    $ret[] = $map[$key];
  }
  return $ret;
}

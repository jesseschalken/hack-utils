<?hh // strict

namespace HackUtils\regex;

use HackUtils\vector;
use HackUtils\map;
use function HackUtils\new_null;

const string CASELESS = 'i';
const string MULTILINE = 'm';
const string DOTALL = 's';
const string EXTENDED = 'x';
const string ANCHORED = 'A';
const string DOLLAR_ENDONLY = 'D';
const string UNGREEDY = 'U';
const string EXTRA = 'X';
const string UTF8 = 'u';
const string STUDY = 'S';

type match = map<(string, int)>;

function quote(string $text): string {
  return \preg_quote($text);
}

function match(
  string $regex,
  string $subject,
  string $options = '',
  int $offset = 0,
): ?match {
  $match = [];
  $count = \preg_match(
    _compose($regex, $options),
    $subject,
    $match,
    \PREG_OFFSET_CAPTURE,
    $offset,
  );
  _check_last_error();
  return $count ? _fix_match($match) : new_null();
}

function match_all(
  string $regex,
  string $subject,
  string $options,
  int $offset = 0,
): vector<match> {
  $matches = [];
  \preg_match_all(
    _compose($regex, $options),
    $subject,
    $matches,
    \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE,
    $offset,
  );
  return vector\map(
    $matches,
    function($match) {
      return _fix_match($match);
    },
  );
}

function replace(
  string $regex,
  string $subject,
  string $replacement,
  ?int $limit = null,
  string $options = '',
): string {
  $result = \preg_replace(
    _compose($regex, $options),
    $replacement,
    $subject,
    $limit === null ? -1 : \max(0, $limit),
  );
  _check_last_error();
  if (!\is_string($result)) {
    throw new Exception('preg_replace() failed');
  }
  return $result;
}

function split(
  string $regex,
  string $subject,
  ?int $limit = null,
  string $options = '',
): vector<string> {
  $pieces = \preg_split(
    _compose($regex, $options),
    $subject,
    $limit === null ? -1 : max(1, $limit),
  );
  _check_last_error();
  if (!\is_array($pieces)) {
    throw new Exception('preg_split() failed');
  }
  return $pieces;
}

final class Exception extends \Exception {}

function _compose(string $regex, string $options = ''): string {
  return '/'._EscapeCache::escape($regex).'/'.$options;
}

final class _EscapeCache {
  private static map<string> $cache = [];

  public static function escape(string $regex): string {
    $escaped = self::$cache[$regex] ?? new_null();
    if ($escaped !== null) {
      return $escaped;
    }
    // Dumb cache policy, but it works.
    if (map\count(self::$cache) >= 10000) {
      self::$cache = [];
    }
    return (self::$cache[$regex] = _escape($regex));
  }
}

function _escape(string $regex): string {
  // Insert a "\" before each unescaped "/".
  // I'm really hoping this simple state machine will get jitted to efficient
  // machine code.
  $result = '';
  $length = strlen($regex);
  $escape = false;
  for ($i = 0; $i < $length; $i++) {
    $char = $regex[$i];
    if ($escape) {
      $escape = false;
    } else if ($char === '/') {
      $result .= '\\';
    } else if ($char === '\\') {
      $escape = true;
    }
    $result .= $char;
  }
  return $result;
}

function _fix_match(match $match): match {
  // A sub pattern will exist in $subPatterns if it didn't match
  // only if a later sub pattern matched.
  //
  // Example:
  //   match (a)(lol)?b   against "ab" => ["ab", 0], ["a", 0]
  //   match (a)(lol)?(b) against "ab" => ["ab", 0], ["a", 0], ["", -1], ["b", 1]
  //
  // Remove those ones.
  foreach ($match as $k => $v) {
    if ($v[1] == -1) {
      unset($match[$k]);
    }
  }
  return $match;
}

function _get_error_message(int $error): string {
  switch ($error) {
    case PREG_NO_ERROR:
      return 'No errors';
    case PREG_INTERNAL_ERROR:
      return 'Internal PCRE error';
    case PREG_BACKTRACK_LIMIT_ERROR:
      return 'Backtrack limit (pcre.backtrack_limit) was exhausted';
    case PREG_RECURSION_LIMIT_ERROR:
      return 'Recursion limit (pcre.recursion_limit) was exhausted';
    case PREG_BAD_UTF8_ERROR:
      return 'Malformed UTF-8 data';
    case PREG_BAD_UTF8_OFFSET_ERROR:
      return
        'The offset didn\'t correspond to the beginning of a valid UTF-8 code point';
    case 6 /* PREG_JIT_STACKLIMIT_ERROR */:
      return 'JIT stack space limit exceeded';
    default:
      return 'Unknown error';
  }
}

function _check_last_error(): void {
  $error = \preg_last_error();
  if ($error !== \PREG_NO_ERROR) {
    throw new Exception(_get_error_message($error), $error);
  }
}

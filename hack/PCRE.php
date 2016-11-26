<?hh // strict

namespace HackUtils\PCRE;

use HackUtils as HU;

final class Pattern {
  public static function quote(string $text): string {
    return \preg_quote($text);
  }

  public static function create(string $regex, string $options = ''): Pattern {
    return new self($regex, $options);
  }

  private static array<arraykey, string> $escapeCache = [];

  private static function escape(string $regex): string {
    $escaped = HU\get_or_null(self::$escapeCache, $regex);
    if ($escaped !== null)
      return $escaped;
    // Dumb cache policy, but it works.
    if (\count(self::$escapeCache) >= 10000)
      self::$escapeCache = [];
    return (self::$escapeCache[$regex] = self::escapeImpl($regex));
  }

  private static function escapeImpl(string $regex): string {
    // Insert a "\" before each unescaped "/".
    // I'm really hoping this simple state machine will get jitted to efficient
    // machine code.
    $result = '';
    $length = \strlen($regex);
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

  private static array<int, string>
    $errors = [
      \PREG_NO_ERROR => 'No errors',
      \PREG_INTERNAL_ERROR => 'Internal PCRE error',
      \PREG_BACKTRACK_LIMIT_ERROR =>
        'Backtrack limit (pcre.backtrack_limit) was exhausted',
      \PREG_RECURSION_LIMIT_ERROR =>
        'Recursion limit (pcre.recursion_limit) was exhausted',
      \PREG_BAD_UTF8_ERROR => 'Malformed UTF-8 data',
      \PREG_BAD_UTF8_OFFSET_ERROR =>
        'The offset didn\'t correspond to the beginning of a valid UTF-8 code point',
      // \PREG_JIT_STACKLIMIT_ERROR
      6 => 'JIT stack space limit exceeded',
    ];

  private static function checkLastError(): void {
    $error = \preg_last_error();
    if ($error !== \PREG_NO_ERROR) {
      $message = HU\get_or_default(self::$errors, $error, 'Unknown error');
      throw new Exception($message, $error);
    }
  }

  private string $composed;

  private function __construct(string $regex, string $options = '') {
    $this->composed = '/'.self::escape($regex).'/'.$options;
  }

  public function match(string $subject, int $offset = 0): ?Match {
    $match = [];
    $flags = \PREG_OFFSET_CAPTURE;
    $count = \preg_match($this->composed, $subject, $match, $flags, $offset);
    self::checkLastError();
    return $count ? new Match($match) : HU\new_null();
  }

  public function matchOrThrow(string $subject, int $offset = 0): Match {
    $match = $this->match($subject, $offset);
    if (!$match)
      throw new NoMatchException(
        "Failed to match $this->composed against string '$subject'",
      );
    return $match;
  }

  public function matchAll(string $subject, int $offset = 0): array<Match> {
    $matches = [];
    $flags = \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE;
    \preg_match_all($this->composed, $subject, $matches, $flags, $offset);
    return HU\map(
      $matches,
      function($match) {
        return new Match($match);
      },
    );
  }

  public function replace(
    string $subject,
    string $replacement,
    ?int $limit = null,
  ): string {
    $result = \preg_replace(
      $this->composed,
      $replacement,
      $subject,
      $limit === null ? -1 : HU\max(0, $limit),
    );
    self::checkLastError();
    if (!\is_string($result))
      throw new Exception('preg_replace() failed');
    return $result;
  }

  public function split(string $subject, ?int $limit = null): array<string> {
    $pieces = \preg_split(
      $this->composed,
      $subject,
      $limit === null ? -1 : max(1, $limit),
    );
    self::checkLastError();
    if (!\is_array($pieces))
      throw new Exception('preg_split() failed');
    return $pieces;
  }
}

final class Match {
  public function __construct(private array<arraykey, (string, int)> $match) {
    // A sub pattern will exist in $subPatterns if it didn't match
    // only if a later sub pattern matched.
    //
    // Example:
    //   match (a)(lol)?b against "ab"
    //   - ["ab", 0]
    //   - ["a", 0]
    //   match (a)(lol)?(b) against "ab"
    //   - ["ab", 0]
    //   - ["a", 0]
    //   - ["", -1]
    //   - ["b", 1]
    //
    // Remove those ones.
    foreach ($this->match as $k => $v) {
      if ($v[1] == -1) {
        unset($this->match[$k]);
      }
    }
  }

  public function get(arraykey $group = 0): string {
    return $this->match[$group][0];
  }

  public function getOrNull(arraykey $group = 0): ?string {
    $match = HU\get_or_null($this->match, $group);
    return $match ? $match[0] : HU\new_null();
  }

  public function getOrEmpty(arraykey $group = 0): string {
    $match = HU\get_or_null($this->match, $group);
    return $match ? $match[0] : '';
  }

  public function start(arraykey $group = 0): int {
    return $this->match[$group][1];
  }

  public function end(arraykey $group = 0): int {
    list($text, $offset) = $this->match[$group];
    return $offset + \strlen($text);
  }

  public function range(arraykey $group = 0): (int, int) {
    list($text, $offset) = $this->match[$group];
    return tuple($offset, $offset + \strlen($text));
  }

  public function length(arraykey $group = 0): int {
    return \strlen($this->get($group));
  }

  public function has(arraykey $group): bool {
    return HU\key_exists($this->match, $group);
  }

  public function __toString(): string {
    return $this->get();
  }

  public function toString(): string {
    return $this->get();
  }

  public function toArray(): array<arraykey, string> {
    return HU\map_assoc($this->match, $x ==> $x[0]);
  }
}

class Exception extends \Exception {}
class NoMatchException extends Exception {}

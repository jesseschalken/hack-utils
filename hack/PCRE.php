<?hh // strict

namespace HackUtils\PCRE;

use HackUtils as HU;
use HackUtils\StrictErrors;

class Test extends HU\Test {
  public function run(): void {
    self::assertEqual(Pattern::quote('/a/'), '/a/');
    self::assertEqual(
      Pattern::quote('\\^$.|?*+()[]{}'),
      "\\\\\\^\\\$\\.\\|\\?\\*\\+\\(\\)\\[\\]\\{\\}",
    );
    self::assertEqual(
      Pattern::create('^a|bb$')->matchOrThrow('woeijdwefbbb')->unwrap(),
      [tuple('bb', 10)],
    );
    self::assertException(
      function() {
        Pattern::create('<?$(')->match('');
      },
      'preg_match(): Compilation failed: missing ) at offset 4',
    );
    self::assertException(
      function() {
        Pattern::create('b.*b')
          ->match('baab'.HU\repeat_string('a', 10002400));
      },
      'Backtrack limit (pcre.backtrack_limit) was exhausted',
      \PREG_BACKTRACK_LIMIT_ERROR,
    );
    self::assertException(
      function() {
        Pattern::create('foo bar')->matchOrThrow('baz');
      },
      'Failed to match /foo bar/ against string \'baz\'',
    );
    $matches =
      Pattern::create('\w+')
        ->matchAll('iuh iuh lij b   jhvbhgvhgv u yguyguyg uy iuh');
    self::assertEqual(
      HU\map(
        $matches,
        function($match) {
          return $match->unwrap();
        },
      ),
      [
        [["iuh", 0]],
        [["iuh", 4]],
        [["lij", 8]],
        [["b", 12]],
        [["jhvbhgvhgv", 16]],
        [["u", 27]],
        [["yguyguyg", 29]],
        [["uy", 38]],
        [["iuh", 41]],
      ],
    );
    self::assertEqual($matches[4]->start(), 16);
    self::assertEqual($matches[4]->end(), 26);
    self::assertEqual($matches[4]->range(), tuple(16, 26));
    self::assertEqual($matches[4]->length(), 10);
    self::assertEqual(
      Pattern::create('\w(\w+)')
        ->replace('iuh iuh lij b   jhvbhgvhgv u yguyguyg uy iuh', '\\$$1'),
      '$uh $uh $ij b   $hvbhgvhgv u $guyguyg $y $uh',
    );
    self::assertEqual(
      Pattern::create('\s+')
        ->split('iuh iuh lij b   jhvbhgvhgv u yguyguyg uy iuh'),
      ["iuh", "iuh", "lij", "b", "jhvbhgvhgv", "u", "yguyguyg", "uy", "iuh"],
    );
    // Example:
    //   match (a)(lol)?b against "ab"
    //   - ["ab", 0]
    //   - ["a", 0]
    //   match (a)(lol)?(b) against "ab"
    //   - ["ab", 0]
    //   - ["a", 0]
    //   - ["", -1]
    //   - ["b", 1]
    $a = Pattern::create('(a)(lol)?b')->matchOrThrow('ab');
    $b = Pattern::create('(a)(lol)?(b)')->matchOrThrow('ab');
    self::assertEqual($a->unwrap(), [["ab", 0], ["a", 0]]);
    self::assertEqual(
      $b->unwrap(),
      [0 => ["ab", 0], 1 => ["a", 0], 3 => ["b", 1]],
    );
    self::assertEqual($b->toArray(), [0 => "ab", 1 => "a", 3 => "b"]);
    self::assertEqual($b->get(), "ab");
    self::assertEqual($b->get(1), "a");
    self::assertEqual($b->get(3), "b");
    self::assertEqual($b->getOrNull(), "ab");
    self::assertEqual($b->getOrNull(1), "a");
    self::assertEqual($b->getOrNull(2), HU\NULL_STRING);
    self::assertEqual($b->getOrNull(3), "b");
    self::assertEqual($b->getOrEmpty(), "ab");
    self::assertEqual($b->getOrEmpty(1), "a");
    self::assertEqual($b->getOrEmpty(2), '');
    self::assertEqual($b->getOrEmpty(3), "b");
    self::assertEqual($b->has(0), true);
    self::assertEqual($b->has(1), true);
    self::assertEqual($b->has(2), false);
    self::assertEqual($b->has(3), true);
    self::assertEqual((string) $b, 'ab');
    self::assertEqual($b->toString(), 'ab');

    // Exhaust the escape cache
    for ($i = 0; $i < 10002; $i++) {
      Pattern::create(Pattern::quote((string) $i));
    }
  }
}

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

  public function matches(string $subject): bool {
    $ret =
      StrictErrors::start()
        ->finishAny(\preg_match($this->composed, $subject));
    self::checkLastError();
    return (bool) $ret;
  }

  public function match(string $subject, int $offset = 0): ?Match {
    $match = [];
    $flags = \PREG_OFFSET_CAPTURE;
    $count =
      StrictErrors::start()->finishAny(
        \preg_match($this->composed, $subject, $match, $flags, $offset),
      );
    self::checkLastError();
    return $count ? new Match($match) : HU\new_null();
  }

  public function matchOrThrow(string $subject, int $offset = 0): Match {
    $match = $this->match($subject, $offset);
    if (!$match) {
      throw new NoMatchException(
        "Failed to match $this->composed against string '$subject'",
      );
    }
    return $match;
  }

  public function matchAll(string $subject, int $offset = 0): array<Match> {
    $matches = [];
    $flags = \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE;
    StrictErrors::start()->finishAny(
      \preg_match_all($this->composed, $subject, $matches, $flags, $offset),
    );
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
    $result =
      StrictErrors::start()->finishAny(
        \preg_replace(
          $this->composed,
          $replacement,
          $subject,
          $limit === null ? -1 : HU\max(0, $limit),
        ),
      );
    self::checkLastError();
    return Exception::assertString($result);
  }

  public function split(string $subject, ?int $limit = null): array<string> {
    $pieces =
      StrictErrors::start()->finishAny(
        \preg_split(
          $this->composed,
          $subject,
          $limit === null ? -1 : max(1, $limit),
        ),
      );
    self::checkLastError();
    return Exception::assertArray($pieces);
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
    return $match ? $match[0] : HU\NULL_STRING;
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

  public function unwrap(): array<arraykey, (string, int)> {
    return $this->match;
  }
}

class Exception extends HU\Exception {}
class NoMatchException extends Exception {}

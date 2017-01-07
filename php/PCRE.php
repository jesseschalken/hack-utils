<?php
namespace HackUtils\PCRE {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils as HU;
  use \HackUtils\StrictErrors;
  class Test extends HU\Test {
    public function run() {
      self::assertEqual(Pattern::quote("/a/"), "/a/");
      self::assertEqual(
        Pattern::quote("\\^\044.|?*+()[]{}"),
        "\\\\\\^\\\044\\.\\|\\?\\*\\+\\(\\)\\[\\]\\{\\}"
      );
      self::assertEqual(
        Pattern::create("^a|bb\044")->matchOrThrow("woeijdwefbbb")->unwrap(),
        array(array("bb", 10))
      );
      self::assertException(
        function() {
          Pattern::create("<?\044(")->match("");
        },
        "preg_match(): Compilation failed: missing ) at offset 4"
      );
      self::assertException(
        function() {
          Pattern::create("b.*b")
            ->match("baab".HU\repeat_string("a", 10002400));
        },
        "Backtrack limit (pcre.backtrack_limit) was exhausted",
        \PREG_BACKTRACK_LIMIT_ERROR
      );
      self::assertException(
        function() {
          Pattern::create("foo bar")->matchOrThrow("baz");
        },
        "Failed to match /foo bar/ against string 'baz'"
      );
      $matches =
        Pattern::create("\\w+")
          ->matchAll("iuh iuh lij b   jhvbhgvhgv u yguyguyg uy iuh");
      self::assertEqual(
        HU\map(
          $matches,
          function($match) {
            return $match->unwrap();
          }
        ),
        array(
          array(array("iuh", 0)),
          array(array("iuh", 4)),
          array(array("lij", 8)),
          array(array("b", 12)),
          array(array("jhvbhgvhgv", 16)),
          array(array("u", 27)),
          array(array("yguyguyg", 29)),
          array(array("uy", 38)),
          array(array("iuh", 41))
        )
      );
      self::assertEqual($matches[4]->start(), 16);
      self::assertEqual($matches[4]->end(), 26);
      self::assertEqual($matches[4]->range(), array(16, 26));
      self::assertEqual($matches[4]->length(), 10);
      self::assertEqual(
        Pattern::create("\\w(\\w+)")->replace(
          "iuh iuh lij b   jhvbhgvhgv u yguyguyg uy iuh",
          "\\\044\0441"
        ),
        "\044uh \044uh \044ij b   \044hvbhgvhgv u \044guyguyg \044y \044uh"
      );
      self::assertEqual(
        Pattern::create("\\s+")
          ->split("iuh iuh lij b   jhvbhgvhgv u yguyguyg uy iuh"),
        array(
          "iuh",
          "iuh",
          "lij",
          "b",
          "jhvbhgvhgv",
          "u",
          "yguyguyg",
          "uy",
          "iuh"
        )
      );
      $a = Pattern::create("(a)(lol)?b")->matchOrThrow("ab");
      $b = Pattern::create("(a)(lol)?(b)")->matchOrThrow("ab");
      self::assertEqual($a->unwrap(), array(array("ab", 0), array("a", 0)));
      self::assertEqual(
        $b->unwrap(),
        array(0 => array("ab", 0), 1 => array("a", 0), 3 => array("b", 1))
      );
      self::assertEqual($b->toArray(), array(0 => "ab", 1 => "a", 3 => "b"));
      self::assertEqual($b->get(), "ab");
      self::assertEqual($b->get(1), "a");
      self::assertEqual($b->get(3), "b");
      self::assertEqual($b->getOrNull(), "ab");
      self::assertEqual($b->getOrNull(1), "a");
      self::assertEqual($b->getOrNull(2), HU\NULL_STRING);
      self::assertEqual($b->getOrNull(3), "b");
      self::assertEqual($b->getOrEmpty(), "ab");
      self::assertEqual($b->getOrEmpty(1), "a");
      self::assertEqual($b->getOrEmpty(2), "");
      self::assertEqual($b->getOrEmpty(3), "b");
      self::assertEqual($b->has(0), true);
      self::assertEqual($b->has(1), true);
      self::assertEqual($b->has(2), false);
      self::assertEqual($b->has(3), true);
      self::assertEqual((string) $b, "ab");
      self::assertEqual($b->toString(), "ab");
      for ($i = 0; $i < 10002; $i++) {
        Pattern::create(Pattern::quote((string) $i));
      }
    }
  }
  final class Pattern {
    public static function quote($text) {
      return \preg_quote($text);
    }
    public static function create($regex, $options = "") {
      return new self($regex, $options);
    }
    private static $escapeCache = array();
    private static function escape($regex) {
      $escaped = HU\get_or_null(self::$escapeCache, $regex);
      if ($escaped !== null) {
        return $escaped;
      }
      if (\count(self::$escapeCache) >= 10000) {
        self::$escapeCache = array();
      }
      return self::$escapeCache[$regex] = self::escapeImpl($regex);
    }
    private static function escapeImpl($regex) {
      $result = "";
      $length = \strlen($regex);
      $escape = false;
      for ($i = 0; $i < $length; $i++) {
        $char = $regex[$i];
        if (\hacklib_cast_as_boolean($escape)) {
          $escape = false;
        } else {
          if ($char === "/") {
            $result .= "\\";
          } else {
            if ($char === "\\") {
              $escape = true;
            }
          }
        }
        $result .= $char;
      }
      return $result;
    }
    private static
      $errors = array(
        \PREG_NO_ERROR => "No errors",
        \PREG_INTERNAL_ERROR => "Internal PCRE error",
        \PREG_BACKTRACK_LIMIT_ERROR =>
          "Backtrack limit (pcre.backtrack_limit) was exhausted",
        \PREG_RECURSION_LIMIT_ERROR =>
          "Recursion limit (pcre.recursion_limit) was exhausted",
        \PREG_BAD_UTF8_ERROR => "Malformed UTF-8 data",
        \PREG_BAD_UTF8_OFFSET_ERROR =>
          "The offset didn't correspond to the beginning of a valid UTF-8 code point",
        6 => "JIT stack space limit exceeded"
      );
    private static function checkLastError() {
      $error = \preg_last_error();
      if ($error !== \PREG_NO_ERROR) {
        $message = HU\get_or_default(self::$errors, $error, "Unknown error");
        throw new Exception($message, $error);
      }
    }
    private $composed;
    private function __construct($regex, $options = "") {
      $this->composed = "/".self::escape($regex)."/".$options;
    }
    public function matches($subject) {
      $ret =
        StrictErrors::start()
          ->finishAny(\preg_match($this->composed, $subject));
      self::checkLastError();
      return (bool) \hacklib_cast_as_boolean($ret);
    }
    public function match($subject, $offset = 0) {
      $match = array();
      $flags = \PREG_OFFSET_CAPTURE;
      $count =
        StrictErrors::start()->finishAny(
          \preg_match($this->composed, $subject, $match, $flags, $offset)
        );
      self::checkLastError();
      return
        \hacklib_cast_as_boolean($count)
          ? (new Match($match))
          : HU\new_null();
    }
    public function matchOrThrow($subject, $offset = 0) {
      $match = $this->match($subject, $offset);
      if (!\hacklib_cast_as_boolean($match)) {
        throw new NoMatchException(
          "Failed to match ".
          $this->composed.
          " against string '".
          $subject.
          "'"
        );
      }
      return $match;
    }
    public function matchAll($subject, $offset = 0) {
      $matches = array();
      $flags = \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE;
      StrictErrors::start()->finishAny(
        \preg_match_all(
          $this->composed,
          $subject,
          $matches,
          $flags,
          $offset
        )
      );
      return HU\map(
        $matches,
        function($match) {
          return new Match($match);
        }
      );
    }
    public function replace($subject, $replacement, $limit = null) {
      $result =
        StrictErrors::start()->finishAny(
          \preg_replace(
            $this->composed,
            $replacement,
            $subject,
            ($limit === null) ? (-1) : HU\max(0, $limit)
          )
        );
      self::checkLastError();
      return Exception::assertString($result);
    }
    public function split($subject, $limit = null) {
      $pieces =
        StrictErrors::start()->finishAny(
          \preg_split(
            $this->composed,
            $subject,
            ($limit === null) ? (-1) : max(1, $limit)
          )
        );
      self::checkLastError();
      return Exception::assertArray($pieces);
    }
  }
  final class Match {
    private $match;
    public function __construct($match) {
      $this->match = $match;
      foreach ($this->match as $k => $v) {
        if (\hacklib_equals($v[1], -1)) {
          unset($this->match[$k]);
        }
      }
    }
    public function get($group = 0) {
      return $this->match[$group][0];
    }
    public function getOrNull($group = 0) {
      $match = HU\get_or_null($this->match, $group);
      return \hacklib_cast_as_boolean($match) ? $match[0] : HU\NULL_STRING;
    }
    public function getOrEmpty($group = 0) {
      $match = HU\get_or_null($this->match, $group);
      return \hacklib_cast_as_boolean($match) ? $match[0] : "";
    }
    public function start($group = 0) {
      return $this->match[$group][1];
    }
    public function end($group = 0) {
      list($text, $offset) = $this->match[$group];
      return $offset + \strlen($text);
    }
    public function range($group = 0) {
      list($text, $offset) = $this->match[$group];
      return array($offset, $offset + \strlen($text));
    }
    public function length($group = 0) {
      return \strlen($this->get($group));
    }
    public function has($group) {
      return HU\key_exists($this->match, $group);
    }
    public function __toString() {
      return $this->get();
    }
    public function toString() {
      return $this->get();
    }
    public function toArray() {
      return HU\map_assoc(
        $this->match,
        function($x) {
          return $x[0];
        }
      );
    }
    public function unwrap() {
      return $this->match;
    }
  }
  class Exception extends HU\Exception {}
  class NoMatchException extends Exception {}
}

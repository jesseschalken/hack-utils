<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  function _assert_equal($actual, $expected) {
    if (!_is_equal($actual, $expected)) {
      throw new \Exception(
        \sprintf(
          "Expected %s, got %s",
          \var_export($expected, true),
          \var_export($actual, true)
        )
      );
    }
  }
  function _is_equal($a, $b) {
    if (\is_float($a) && \is_float($b)) {
      if (\is_nan($a) && \is_nan($b)) {
        return true;
      }
      if (($a === 0.0) && ($b === 0.0) && (((string) $a) !== ((string) $b))) {
        return false;
      }
    }
    if (\is_array($a) && \is_array($b)) {
      if (\count($a) !== \count($b)) {
        return false;
      }
      \reset($a);
      \reset($b);
      while (1) {
        $k1 = \key($a);
        $k2 = \key($b);
        if ($k1 !== $k2) {
          return false;
        }
        if ($k1 === null) {
          return true;
        }
        $v1 = \current($a);
        $v2 = \current($b);
        if (!_is_equal($v1, $v2)) {
          return false;
        }
        \next($a);
        \next($b);
      }
    }
    return $a === $b;
  }
  function _run_tests() {
    _assert_equal(to_hex("\000\377 "), "00ff20");
    _assert_equal(from_hex("00ff20"), "\000\377 ");
    _assert_equal(from_hex("00Ff20"), "\000\377 ");
    _assert_equal(length(str_shuffle("abc")), 3);
    _assert_equal(reverse_string("abc"), "cba");
    _assert_equal(reverse_string(""), "");
    _assert_equal(to_lower("ABC.1.2.3"), "abc.1.2.3");
    _assert_equal(to_upper("abc.1.2.3"), "ABC.1.2.3");
    _assert_equal(split(""), array());
    _assert_equal(split("a"), array("a"));
    _assert_equal(split("abc"), array("a", "b", "c"));
    _assert_equal(split("", "", 1), array());
    _assert_equal(split("a", "", 1), array("a"));
    _assert_equal(split("abc", "", 1), array("abc"));
    _assert_equal(split("abc", "", 2), array("a", "bc"));
    _assert_equal(split("abc", "", 3), array("a", "b", "c"));
    _assert_equal(split("", "b"), array(""));
    _assert_equal(split("abc", "b"), array("a", "c"));
    _assert_equal(split("abc", "b", 1), array("abc"));
    _assert_equal(split("abc", "b", 2), array("a", "c"));
    _assert_equal(chunk_string("abc", 1), array("a", "b", "c"));
    _assert_equal(chunk_string("abc", 2), array("ab", "c"));
    _assert_equal(chunk_string("abc", 3), array("abc"));
    _assert_equal(join(array()), "");
    _assert_equal(join(array("abc")), "abc");
    _assert_equal(join(array("a", "bc")), "abc");
    _assert_equal(join(array(), ","), "");
    _assert_equal(join(array("abc"), ","), "abc");
    _assert_equal(join(array("a", "bc"), ","), "a,bc");
    _assert_equal(replace_count("abc", "b", "lol"), array("alolc", 1));
    _assert_equal(replace_count("abc", "B", "lol"), array("abc", 0));
    _assert_equal(replace_count("abc", "B", "lol", true), array("alolc", 1));
    _assert_equal(splice("abc", 1, 1), "ac");
    _assert_equal(splice("abc", 1, 1, "lol"), "alolc");
    _assert_equal(slice("abc", 1, 1), "b");
    _assert_equal(slice("abc", -1, 1), "c");
    _assert_equal(slice("abc", 1, -1), "b");
    _assert_equal(slice("abc", 1), "bc");
    _assert_equal(slice("abc", -1), "c");
    _assert_equal(pad("abc", 3), "abc");
    _assert_equal(pad("abc", 4), "abc ");
    _assert_equal(pad("abc", 5), " abc ");
    _assert_equal(pad("abc", 6), " abc  ");
    _assert_equal(pad("1", 3, "ab"), "a1a");
    _assert_equal(pad("1", 4, "ab"), "a1ab");
    _assert_equal(pad_left("abc", 3), "abc");
    _assert_equal(pad_left("abc", 4), " abc");
    _assert_equal(pad_left("abc", 5), "  abc");
    _assert_equal(pad_left("abc", 6), "   abc");
    _assert_equal(pad_left("1", 3, "ab"), "ab1");
    _assert_equal(pad_left("1", 4, "ab"), "aba1");
    _assert_equal(pad_right("abc", 3), "abc");
    _assert_equal(pad_right("abc", 4), "abc ");
    _assert_equal(pad_right("abc", 5), "abc  ");
    _assert_equal(pad_right("abc", 6), "abc   ");
    _assert_equal(pad_right("1", 3, "ab"), "1ab");
    _assert_equal(pad_right("1", 4, "ab"), "1aba");
    _assert_equal(str_repeat("123", 3), "123123123");
    _assert_equal(from_char_code(128), "\200");
    _assert_equal(from_char_code(0), "\000");
    _assert_equal(from_char_code(255), "\377");
    _assert_equal(char_code_at("a"), 97);
    _assert_equal(char_code_at("a99"), 97);
    _assert_equal(str_cmp("a", "a"), 0);
    _assert_equal(str_cmp("a", "A"), 1);
    _assert_equal(str_cmp("", ""), 0);
    _assert_equal(str_cmp("", "a"), -1);
    _assert_equal(str_cmp("a", ""), 1);
    _assert_equal(str_cmp("a", "a", true), 0);
    _assert_equal(str_cmp("a", "A", true), 0);
    _assert_equal(str_cmp("", "", true), 0);
    _assert_equal(str_cmp("", "a", true), -1);
    _assert_equal(str_cmp("a", "", true), 1);
    _assert_equal(str_eq("a", "a"), true);
    _assert_equal(str_eq("a", "A"), false);
    _assert_equal(str_eq("", ""), true);
    _assert_equal(str_eq("", "a"), false);
    _assert_equal(str_eq("a", ""), false);
    _assert_equal(str_eq("a", "a", true), true);
    _assert_equal(str_eq("a", "A", true), true);
    _assert_equal(str_eq("", "", true), true);
    _assert_equal(str_eq("", "a", true), false);
    _assert_equal(str_eq("a", "", true), false);
    _assert_equal(find("a", "a"), 0);
    _assert_equal(find("a", "a", 1), null);
    _assert_equal(find("a", "a", -1), 0);
    _assert_equal(find("abc", "a"), 0);
    _assert_equal(find("abc", "b"), 1);
    _assert_equal(find("abc", "c"), 2);
    _assert_equal(find("abc", "a", -2), null);
    _assert_equal(find("abc", "b", -2), 1);
    _assert_equal(find("abc", "c", -2), 2);
    _assert_equal(find("abbb", "bb"), 1);
    _assert_equal(find("abbb", "bb", 2), 2);
    _assert_equal(find_last("a", "a"), 0);
    _assert_equal(find_last("a", "a", 1), null);
    _assert_equal(find_last("a", "a", -1), 0);
    _assert_equal(find_last("aba", "a"), 2);
    _assert_equal(find_last("aba", "b"), 1);
    _assert_equal(find_last("aba", "c"), null);
    _assert_equal(find_last("aba", "a", -2), 2);
    _assert_equal(find_last("aba", "b", -2), 1);
    _assert_equal(find_last("aba", "c", -2), null);
    _assert_equal(find_last("abbb", "bb"), 2);
    _assert_equal(find_last("abbb", "bb", 2), 2);
    _assert_equal(ends_with("abbb", "bb"), true);
    _assert_equal(ends_with("abbb", "ba"), false);
    _assert_equal(ends_with("abbb", ""), true);
    _assert_equal(ends_with("", ""), true);
    _assert_equal(ends_with("", "a"), false);
    _assert_equal(starts_with("abbb", "ab"), true);
    _assert_equal(starts_with("abbb", "bb"), false);
    _assert_equal(starts_with("abbb", ""), true);
    _assert_equal(starts_with("", ""), true);
    _assert_equal(starts_with("", "a"), false);
    echo ("round_half_down\n");
    _assert_equal(round_half_down(0.5), 0.0);
    _assert_equal(round_half_down(1.5), 1.0);
    _assert_equal(round_half_down(-0.5), -1.0);
    _assert_equal(round_half_down(-1.5), -2.0);
    _assert_equal(round_half_down(INF), INF);
    _assert_equal(round_half_down(-INF), -INF);
    _assert_equal(round_half_down(NAN), NAN);
    echo ("round_half_up\n");
    _assert_equal(round_half_up(0.5), 1.0);
    _assert_equal(round_half_up(1.5), 2.0);
    _assert_equal(round_half_up(-0.5), 0.0);
    _assert_equal(round_half_up(-1.5), -1.0);
    _assert_equal(round_half_up(INF), INF);
    _assert_equal(round_half_up(-INF), -INF);
    _assert_equal(round_half_up(NAN), NAN);
    echo ("round_half_to_inf\n");
    _assert_equal(round_half_to_inf(0.5), 1.0);
    _assert_equal(round_half_to_inf(1.5), 2.0);
    _assert_equal(round_half_to_inf(-0.5), -1.0);
    _assert_equal(round_half_to_inf(-1.5), -2.0);
    _assert_equal(round_half_to_inf(INF), INF);
    _assert_equal(round_half_to_inf(-INF), -INF);
    _assert_equal(round_half_to_inf(NAN), NAN);
    echo ("round_half_to_zero\n");
    _assert_equal(round_half_to_zero(0.5), 0.0);
    _assert_equal(round_half_to_zero(1.5), 1.0);
    _assert_equal(round_half_to_zero(-0.5), 0.0);
    _assert_equal(round_half_to_zero(-1.5), -1.0);
    _assert_equal(round_half_to_zero(INF), INF);
    _assert_equal(round_half_to_zero(-INF), -INF);
    _assert_equal(round_half_to_zero(NAN), NAN);
    echo ("round_half_to_even\n");
    _assert_equal(round_half_to_even(0.5), 0.0);
    _assert_equal(round_half_to_even(1.5), 2.0);
    _assert_equal(round_half_to_even(-0.5), 0.0);
    _assert_equal(round_half_to_even(-1.5), -2.0);
    _assert_equal(round_half_to_even(INF), INF);
    _assert_equal(round_half_to_even(-INF), -INF);
    _assert_equal(round_half_to_even(NAN), NAN);
    echo ("round_half_to_odd\n");
    _assert_equal(round_half_to_odd(0.5), 1.0);
    _assert_equal(round_half_to_odd(1.5), 1.0);
    _assert_equal(round_half_to_odd(-0.5), -1.0);
    _assert_equal(round_half_to_odd(-1.5), -1.0);
    _assert_equal(round_half_to_odd(INF), INF);
    _assert_equal(round_half_to_odd(-INF), -INF);
    _assert_equal(round_half_to_odd(NAN), NAN);
    echo ("okay\n");
  }
}

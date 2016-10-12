<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  require_once (__DIR__."/include.php");
  function assert_eqaul($actual, $expected) {
    if ($actual !== $expected) {
      throw new \Exception(
        \sprintf(
          "Expected %s, got %s",
          \var_export($expected, true),
          \var_export($actual, true)
        )
      );
    }
  }
  function run_tests() {
    assert_eqaul(to_hex("\000\377 "), "00ff20");
    assert_eqaul(from_hex("00ff20"), "\000\377 ");
    assert_eqaul(from_hex("00Ff20"), "\000\377 ");
    assert_eqaul(length(str_shuffle("abc")), 3);
    assert_eqaul(reverse_string("abc"), "cba");
    assert_eqaul(reverse_string(""), "");
    assert_eqaul(to_lower("ABC.1.2.3"), "abc.1.2.3");
    assert_eqaul(to_upper("abc.1.2.3"), "ABC.1.2.3");
    assert_eqaul(split(""), array());
    assert_eqaul(split("a"), array("a"));
    assert_eqaul(split("abc"), array("a", "b", "c"));
    assert_eqaul(split("", "", 1), array());
    assert_eqaul(split("a", "", 1), array("a"));
    assert_eqaul(split("abc", "", 1), array("abc"));
    assert_eqaul(split("abc", "", 2), array("a", "bc"));
    assert_eqaul(split("abc", "", 3), array("a", "b", "c"));
    assert_eqaul(split("", "b"), array(""));
    assert_eqaul(split("abc", "b"), array("a", "c"));
    assert_eqaul(split("abc", "b", 1), array("abc"));
    assert_eqaul(split("abc", "b", 2), array("a", "c"));
    assert_eqaul(chunk_string("abc", 1), array("a", "b", "c"));
    assert_eqaul(chunk_string("abc", 2), array("ab", "c"));
    assert_eqaul(chunk_string("abc", 3), array("abc"));
    assert_eqaul(join(array()), "");
    assert_eqaul(join(array("abc")), "abc");
    assert_eqaul(join(array("a", "bc")), "abc");
    assert_eqaul(join(array(), ","), "");
    assert_eqaul(join(array("abc"), ","), "abc");
    assert_eqaul(join(array("a", "bc"), ","), "a,bc");
    assert_eqaul(replace("abc", "b", "lol"), array("alolc", 1));
    assert_eqaul(replace("abc", "B", "lol"), array("abc", 0));
    assert_eqaul(replace("abc", "B", "lol", true), array("alolc", 1));
    assert_eqaul(splice("abc", 1, 1), "ac");
    assert_eqaul(splice("abc", 1, 1, "lol"), "alolc");
    assert_eqaul(slice("abc", 1, 1), "b");
    assert_eqaul(slice("abc", -1, 1), "c");
    assert_eqaul(slice("abc", 1, -1), "b");
    assert_eqaul(slice("abc", 1), "bc");
    assert_eqaul(slice("abc", -1), "c");
    assert_eqaul(pad("abc", 3), "abc");
    assert_eqaul(pad("abc", 4), "abc ");
    assert_eqaul(pad("abc", 5), " abc ");
    assert_eqaul(pad("abc", 6), " abc  ");
    assert_eqaul(pad("1", 3, "ab"), "a1a");
    assert_eqaul(pad("1", 4, "ab"), "a1ab");
    assert_eqaul(pad_left("abc", 3), "abc");
    assert_eqaul(pad_left("abc", 4), " abc");
    assert_eqaul(pad_left("abc", 5), "  abc");
    assert_eqaul(pad_left("abc", 6), "   abc");
    assert_eqaul(pad_left("1", 3, "ab"), "ab1");
    assert_eqaul(pad_left("1", 4, "ab"), "aba1");
    assert_eqaul(pad_right("abc", 3), "abc");
    assert_eqaul(pad_right("abc", 4), "abc ");
    assert_eqaul(pad_right("abc", 5), "abc  ");
    assert_eqaul(pad_right("abc", 6), "abc   ");
    assert_eqaul(pad_right("1", 3, "ab"), "1ab");
    assert_eqaul(pad_right("1", 4, "ab"), "1aba");
    assert_eqaul(str_repeat("123", 3), "123123123");
    assert_eqaul(from_char_code(128), "\200");
    assert_eqaul(from_char_code(0), "\000");
    assert_eqaul(from_char_code(255), "\377");
    assert_eqaul(char_code_at("a"), 97);
    assert_eqaul(char_code_at("a99"), 97);
    assert_eqaul(str_cmp("a", "a"), 0);
    assert_eqaul(str_cmp("a", "A"), 1);
    assert_eqaul(str_cmp("", ""), 0);
    assert_eqaul(str_cmp("", "a"), -1);
    assert_eqaul(str_cmp("a", ""), 1);
    assert_eqaul(str_cmp("a", "a", true), 0);
    assert_eqaul(str_cmp("a", "A", true), 0);
    assert_eqaul(str_cmp("", "", true), 0);
    assert_eqaul(str_cmp("", "a", true), -1);
    assert_eqaul(str_cmp("a", "", true), 1);
    assert_eqaul(str_eq("a", "a"), true);
    assert_eqaul(str_eq("a", "A"), false);
    assert_eqaul(str_eq("", ""), true);
    assert_eqaul(str_eq("", "a"), false);
    assert_eqaul(str_eq("a", ""), false);
    assert_eqaul(str_eq("a", "a", true), true);
    assert_eqaul(str_eq("a", "A", true), true);
    assert_eqaul(str_eq("", "", true), true);
    assert_eqaul(str_eq("", "a", true), false);
    assert_eqaul(str_eq("a", "", true), false);
    assert_eqaul(find("a", "a"), 0);
    assert_eqaul(find("a", "a", 1), null);
    assert_eqaul(find("a", "a", -1), 0);
    assert_eqaul(find("abc", "a"), 0);
    assert_eqaul(find("abc", "b"), 1);
    assert_eqaul(find("abc", "c"), 2);
    assert_eqaul(find("abc", "a", -2), null);
    assert_eqaul(find("abc", "b", -2), 1);
    assert_eqaul(find("abc", "c", -2), 2);
    assert_eqaul(find("abbb", "bb"), 1);
    assert_eqaul(find("abbb", "bb", 2), 2);
    assert_eqaul(find_last("a", "a"), 0);
    assert_eqaul(find_last("a", "a", 1), null);
    assert_eqaul(find_last("a", "a", -1), 0);
    assert_eqaul(find_last("aba", "a"), 2);
    assert_eqaul(find_last("aba", "b"), 1);
    assert_eqaul(find_last("aba", "c"), null);
    assert_eqaul(find_last("aba", "a", -2), 2);
    assert_eqaul(find_last("aba", "b", -2), 1);
    assert_eqaul(find_last("aba", "c", -2), null);
    assert_eqaul(find_last("abbb", "bb"), 2);
    assert_eqaul(find_last("abbb", "bb", 2), 2);
    assert_eqaul(ends_with("abbb", "bb"), true);
    assert_eqaul(ends_with("abbb", "ba"), false);
    assert_eqaul(ends_with("abbb", ""), true);
    assert_eqaul(ends_with("", ""), true);
    assert_eqaul(ends_with("", "a"), false);
    assert_eqaul(starts_with("abbb", "ab"), true);
    assert_eqaul(starts_with("abbb", "bb"), false);
    assert_eqaul(starts_with("abbb", ""), true);
    assert_eqaul(starts_with("", ""), true);
    assert_eqaul(starts_with("", "a"), false);
  }
  run_tests();
}

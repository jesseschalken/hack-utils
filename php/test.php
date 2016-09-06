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
    assert_eqaul(str\to_hex("\000\377 "), "00ff20");
    assert_eqaul(str\from_hex("00ff20"), "\000\377 ");
    assert_eqaul(str\from_hex("00Ff20"), "\000\377 ");
    assert_eqaul(str\len(str\shuffle("abc")), 3);
    assert_eqaul(str\reverse("abc"), "cba");
    assert_eqaul(str\reverse(""), "");
    assert_eqaul(str\lower("ABC.1.2.3"), "abc.1.2.3");
    assert_eqaul(str\upper("abc.1.2.3"), "ABC.1.2.3");
    assert_eqaul(str\split(""), array());
    assert_eqaul(str\split("a"), array("a"));
    assert_eqaul(str\split("abc"), array("a", "b", "c"));
    assert_eqaul(str\split("", "", 1), array());
    assert_eqaul(str\split("a", "", 1), array("a"));
    assert_eqaul(str\split("abc", "", 1), array("abc"));
    assert_eqaul(str\split("abc", "", 2), array("a", "bc"));
    assert_eqaul(str\split("abc", "", 3), array("a", "b", "c"));
    assert_eqaul(str\split("", "b"), array(""));
    assert_eqaul(str\split("abc", "b"), array("a", "c"));
    assert_eqaul(str\split("abc", "b", 1), array("abc"));
    assert_eqaul(str\split("abc", "b", 2), array("a", "c"));
    assert_eqaul(str\chunk("abc", 1), array("a", "b", "c"));
    assert_eqaul(str\chunk("abc", 2), array("ab", "c"));
    assert_eqaul(str\chunk("abc", 3), array("abc"));
    assert_eqaul(str\join(array()), "");
    assert_eqaul(str\join(array("abc")), "abc");
    assert_eqaul(str\join(array("a", "bc")), "abc");
    assert_eqaul(str\join(array(), ","), "");
    assert_eqaul(str\join(array("abc"), ","), "abc");
    assert_eqaul(str\join(array("a", "bc"), ","), "a,bc");
    assert_eqaul(str\replace("abc", "b", "lol"), array("alolc", 1));
    assert_eqaul(str\replace("abc", "B", "lol"), array("abc", 0));
    assert_eqaul(str\ireplace("abc", "B", "lol"), array("alolc", 1));
    assert_eqaul(str\splice("abc", 1, 1), "ac");
    assert_eqaul(str\splice("abc", 1, 1, "lol"), "alolc");
    assert_eqaul(str\slice("abc", 1, 1), "b");
    assert_eqaul(str\slice("abc", -1, 1), "c");
    assert_eqaul(str\slice("abc", 1, -1), "b");
    assert_eqaul(str\slice("abc", 1), "bc");
    assert_eqaul(str\slice("abc", -1), "c");
    assert_eqaul(str\pad("abc", 3), "abc");
    assert_eqaul(str\pad("abc", 4), "abc ");
    assert_eqaul(str\pad("abc", 5), " abc ");
    assert_eqaul(str\pad("abc", 6), " abc  ");
    assert_eqaul(str\pad("1", 3, "ab"), "a1a");
    assert_eqaul(str\pad("1", 4, "ab"), "a1ab");
    assert_eqaul(str\lpad("abc", 3), "abc");
    assert_eqaul(str\lpad("abc", 4), " abc");
    assert_eqaul(str\lpad("abc", 5), "  abc");
    assert_eqaul(str\lpad("abc", 6), "   abc");
    assert_eqaul(str\lpad("1", 3, "ab"), "ab1");
    assert_eqaul(str\lpad("1", 4, "ab"), "aba1");
    assert_eqaul(str\rpad("abc", 3), "abc");
    assert_eqaul(str\rpad("abc", 4), "abc ");
    assert_eqaul(str\rpad("abc", 5), "abc  ");
    assert_eqaul(str\rpad("abc", 6), "abc   ");
    assert_eqaul(str\rpad("1", 3, "ab"), "1ab");
    assert_eqaul(str\rpad("1", 4, "ab"), "1aba");
    assert_eqaul(str\repeat("123", 3), "123123123");
    assert_eqaul(str\chr(128), "\200");
    assert_eqaul(str\chr(0), "\000");
    assert_eqaul(str\chr(255), "\377");
    assert_eqaul(str\ord("a"), 97);
    assert_eqaul(str\ord("a99"), 97);
    assert_eqaul(str\cmp("a", "a"), 0);
    assert_eqaul(str\cmp("a", "A"), 1);
    assert_eqaul(str\cmp("", ""), 0);
    assert_eqaul(str\cmp("", "a"), -1);
    assert_eqaul(str\cmp("a", ""), 1);
    assert_eqaul(str\icmp("a", "a"), 0);
    assert_eqaul(str\icmp("a", "A"), 0);
    assert_eqaul(str\icmp("", ""), 0);
    assert_eqaul(str\icmp("", "a"), -1);
    assert_eqaul(str\icmp("a", ""), 1);
    assert_eqaul(str\eq("a", "a"), true);
    assert_eqaul(str\eq("a", "A"), false);
    assert_eqaul(str\eq("", ""), true);
    assert_eqaul(str\eq("", "a"), false);
    assert_eqaul(str\eq("a", ""), false);
    assert_eqaul(str\ieq("a", "a"), true);
    assert_eqaul(str\ieq("a", "A"), true);
    assert_eqaul(str\ieq("", ""), true);
    assert_eqaul(str\ieq("", "a"), false);
    assert_eqaul(str\ieq("a", ""), false);
    assert_eqaul(str\find("a", "a"), 0);
    assert_eqaul(str\find("a", "a", 1), null);
    assert_eqaul(str\find("a", "a", -1), 0);
    assert_eqaul(str\find("abc", "a"), 0);
    assert_eqaul(str\find("abc", "b"), 1);
    assert_eqaul(str\find("abc", "c"), 2);
    assert_eqaul(str\find("abc", "a", -2), null);
    assert_eqaul(str\find("abc", "b", -2), 1);
    assert_eqaul(str\find("abc", "c", -2), 2);
    assert_eqaul(str\find("abbb", "bb"), 1);
    assert_eqaul(str\find("abbb", "bb", 2), 2);
  }
  run_tests();
}

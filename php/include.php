<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
\spl_autoload_register(
  function($class) {
    static
      $map = array(
        "HackUtils\\ArrayIterator" => "ArrayIterator.php",
        "HackUtils\\ArrayStat" => "fs/Stat.php",
        "HackUtils\\CallbackSorter" => "sort.php",
        "HackUtils\\DateTime" => "DateTime.php",
        "HackUtils\\DateTimeException" => "DateTime.php",
        "HackUtils\\DateTimeFormatException" => "DateTime.php",
        "HackUtils\\DateTimeParseException" => "DateTime.php",
        "HackUtils\\Exception" => "Exception.php",
        "HackUtils\\FOpenStream" => "fs/FOpenStream.php",
        "HackUtils\\FileSystem" => "fs/FileSystem.php",
        "HackUtils\\FileSystemPathInterface" => "fs/FileSystem.php",
        "HackUtils\\FileSystemStreamWrapper" =>
          "fs/FileSystemStreamWrapper.php",
        "HackUtils\\Gettable" => "main.php",
        "HackUtils\\JSON" => "json.php",
        "HackUtils\\JSONException" => "json.php",
        "HackUtils\\LocalFileSystem" => "fs/LocalFileSystem.php",
        "HackUtils\\LocaleStringSorter" => "sort.php",
        "HackUtils\\MixedSorter" => "sort.php",
        "HackUtils\\NumSorter" => "sort.php",
        "HackUtils\\PCRE\\Exception" => "PCRE.php",
        "HackUtils\\PCRE\\Match" => "PCRE.php",
        "HackUtils\\PCRE\\NoMatchException" => "PCRE.php",
        "HackUtils\\PCRE\\Pattern" => "PCRE.php",
        "HackUtils\\PCRE\\Test" => "PCRE.php",
        "HackUtils\\Path" => "fs/Path.php",
        "HackUtils\\PosixPath" => "fs/PosixPath.php",
        "HackUtils\\Ref" => "main.php",
        "HackUtils\\SampleTest" => "test.php",
        "HackUtils\\Settable" => "main.php",
        "HackUtils\\Sorter" => "sort.php",
        "HackUtils\\Stat" => "fs/Stat.php",
        "HackUtils\\StatFailed" => "fs/FileSystem.php",
        "HackUtils\\Stream" => "fs/Stream.php",
        "HackUtils\\StreamWrapper" => "fs/StreamWrapper.php",
        "HackUtils\\StrictErrors" => "fs/StrictErrors.php",
        "HackUtils\\StringSorter" => "sort.php",
        "HackUtils\\SymlinkFileSystemInterface" => "fs/FileSystem.php",
        "HackUtils\\Test" => "test.php",
        "HackUtils\\TestArrayIterator" => "ArrayIterator.php",
        "HackUtils\\TestConcatMap" => "main.php",
        "HackUtils\\TestCtype" => "ctype.php",
        "HackUtils\\TestDateTime" => "DateTime.php",
        "HackUtils\\TestDaysInMonth" => "date_utils.php",
        "HackUtils\\TestException" => "Exception.php",
        "HackUtils\\TestFileSystem" => "fs/FileSystem.php",
        "HackUtils\\TestFloatRounding" => "math.php",
        "HackUtils\\TestFrac" => "math.php",
        "HackUtils\\TestFromHex" => "main.php",
        "HackUtils\\TestJSONEncode" => "json.php",
        "HackUtils\\TestLeapYear" => "date_utils.php",
        "HackUtils\\TestOverflowDate" => "date_utils.php",
        "HackUtils\\TestOverflowDateTime" => "date_utils.php",
        "HackUtils\\TestQuotRemDivMod" => "math.php",
        "HackUtils\\TestReverseString" => "main.php",
        "HackUtils\\TestStringCharCode" => "main.php",
        "HackUtils\\TestStringChunk" => "main.php",
        "HackUtils\\TestStringCompare" => "main.php",
        "HackUtils\\TestStringEndsWith" => "main.php",
        "HackUtils\\TestStringJoin" => "main.php",
        "HackUtils\\TestStringPad" => "main.php",
        "HackUtils\\TestStringRepeat" => "main.php",
        "HackUtils\\TestStringReplace" => "main.php",
        "HackUtils\\TestStringSearch" => "main.php",
        "HackUtils\\TestStringSetLength" => "main.php",
        "HackUtils\\TestStringShuffle" => "main.php",
        "HackUtils\\TestStringSlice" => "main.php",
        "HackUtils\\TestStringSplice" => "main.php",
        "HackUtils\\TestStringSplit" => "main.php",
        "HackUtils\\TestStringSplitAt" => "main.php",
        "HackUtils\\TestStringStartsWith" => "main.php",
        "HackUtils\\TestTests" => "test.php",
        "HackUtils\\TestToHex" => "main.php",
        "HackUtils\\TestToLower" => "main.php",
        "HackUtils\\TestToUpper" => "main.php",
        "HackUtils\\TestTypeof" => "typeof.php",
        "HackUtils\\TestValidDate" => "date_utils.php",
        "HackUtils\\TimeZone" => "DateTime.php",
        "HackUtils\\ToIntException" => "math.php",
        "HackUtils\\WindowsPath" => "fs/WindowsPath.php",
        "HackUtils\\_BuiltinSorter" => "sort.php",
        "HackUtils\\_Tests" => "test.php",
        "HackUtils\\_streamWrapper" => "fs/FileSystemStreamWrapper.php"
      );
    if (\hacklib_cast_as_boolean(isset($map[$class]))) {
      require_once (__DIR__."/".$map[$class]);
    }
  },
  true,
  false
);
require_once (__DIR__."/SRC_DIR.php");
require_once (__DIR__."/ctype.php");
require_once (__DIR__."/date_utils.php");
require_once (__DIR__."/fs/stat_utils.php");
require_once (__DIR__."/main.php");
require_once (__DIR__."/math.php");
require_once (__DIR__."/typeof.php");

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
        "HackUtils\\Path" => "fs/Path.php",
        "HackUtils\\PosixPath" => "fs/PosixPath.php",
        "HackUtils\\Ref" => "main.php",
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
        "HackUtils\\TestArrayIterator" => "test.php",
        "HackUtils\\TestConcatMap" => "test.php",
        "HackUtils\\TestDateTime" => "test.php",
        "HackUtils\\TestDaysInMonth" => "test.php",
        "HackUtils\\TestFileSystem" => "test.php",
        "HackUtils\\TestFloatRounding" => "test.php",
        "HackUtils\\TestFrac" => "test.php",
        "HackUtils\\TestFromHex" => "test.php",
        "HackUtils\\TestLeapYear" => "test.php",
        "HackUtils\\TestOverflowDate" => "test.php",
        "HackUtils\\TestQuotRemDivMod" => "test.php",
        "HackUtils\\TestReverseString" => "test.php",
        "HackUtils\\TestStringCharCode" => "test.php",
        "HackUtils\\TestStringChunk" => "test.php",
        "HackUtils\\TestStringCompare" => "test.php",
        "HackUtils\\TestStringEndsWith" => "test.php",
        "HackUtils\\TestStringJoin" => "test.php",
        "HackUtils\\TestStringPad" => "test.php",
        "HackUtils\\TestStringRepeat" => "test.php",
        "HackUtils\\TestStringReplace" => "test.php",
        "HackUtils\\TestStringSearch" => "test.php",
        "HackUtils\\TestStringSetLength" => "test.php",
        "HackUtils\\TestStringShuffle" => "test.php",
        "HackUtils\\TestStringSlice" => "test.php",
        "HackUtils\\TestStringSplice" => "test.php",
        "HackUtils\\TestStringSplit" => "test.php",
        "HackUtils\\TestStringSplitAt" => "test.php",
        "HackUtils\\TestStringStartsWith" => "test.php",
        "HackUtils\\TestTests" => "test.php",
        "HackUtils\\TestToHex" => "test.php",
        "HackUtils\\TestToLower" => "test.php",
        "HackUtils\\TestToUpper" => "test.php",
        "HackUtils\\TestTypeof" => "test.php",
        "HackUtils\\TestValidDate" => "test.php",
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

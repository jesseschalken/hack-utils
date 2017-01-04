<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
\spl_autoload_register(
  function($class) {
    static
      $map = array(
        "HackUtils\\ArrayIterator" => "ArrayIterator.php",
        "HackUtils\\ArrayStat" => "Stat.php",
        "HackUtils\\CallbackSorter" => "sort.php",
        "HackUtils\\DateTime" => "DateTime.php",
        "HackUtils\\DateTimeException" => "DateTime.php",
        "HackUtils\\DateTimeFormatException" => "DateTime.php",
        "HackUtils\\DateTimeParseException" => "DateTime.php",
        "HackUtils\\ErrorAssert" => "ErrorAssert.php",
        "HackUtils\\FOpenStream" => "Stream.php",
        "HackUtils\\FileSystem" => "FileSystem.php",
        "HackUtils\\FileSystemInterface" => "FileSystemIntefaces.php",
        "HackUtils\\FileSystemPathInterface" => "FileSystemIntefaces.php",
        "HackUtils\\FileSystemStreamWrapper" =>
          "FileSystemStreamWrapper.php",
        "HackUtils\\Gettable" => "main.php",
        "HackUtils\\JSON" => "json.php",
        "HackUtils\\JSONException" => "json.php",
        "HackUtils\\LocalFileSystem" => "FileSystem.php",
        "HackUtils\\LocalStreamWrapper" => "FileSystem.php",
        "HackUtils\\LocaleStringSorter" => "sort.php",
        "HackUtils\\MixedSorter" => "sort.php",
        "HackUtils\\NumSorter" => "sort.php",
        "HackUtils\\PCRE\\Exception" => "PCRE.php",
        "HackUtils\\PCRE\\Match" => "PCRE.php",
        "HackUtils\\PCRE\\NoMatchException" => "PCRE.php",
        "HackUtils\\PCRE\\Pattern" => "PCRE.php",
        "HackUtils\\Path" => "Path.php",
        "HackUtils\\PosixPath" => "Path.php",
        "HackUtils\\Ref" => "main.php",
        "HackUtils\\Settable" => "main.php",
        "HackUtils\\Sorter" => "sort.php",
        "HackUtils\\Stat" => "Stat.php",
        "HackUtils\\StatInterface" => "FileSystemIntefaces.php",
        "HackUtils\\Stream" => "Stream.php",
        "HackUtils\\StreamInterface" => "FileSystemIntefaces.php",
        "HackUtils\\StreamWrapperFileSystem" => "FileSystem.php",
        "HackUtils\\StreamWrapperInterface" => "FileSystemIntefaces.php",
        "HackUtils\\StringSorter" => "sort.php",
        "HackUtils\\SymlinkFileSystemInterface" =>
          "FileSystemIntefaces.php",
        "HackUtils\\TimeZone" => "DateTime.php",
        "HackUtils\\ToIntException" => "math.php",
        "HackUtils\\WindowsPath" => "Path.php",
        "HackUtils\\_BuiltinSorter" => "sort.php",
        "HackUtils\\_Tests" => "test.php",
        "HackUtils\\_streamWrapper" => "FileSystemStreamWrapper.php"
      );
    if (isset($map[$class])) {
      require_once (__DIR__."/".$map[$class]);
    }
  },
  true,
  false
);
require_once (__DIR__."/SRC_DIR.php");
require_once (__DIR__."/Stat.php");
require_once (__DIR__."/ctype.php");
require_once (__DIR__."/date_utils.php");
require_once (__DIR__."/main.php");
require_once (__DIR__."/math.php");
require_once (__DIR__."/typeof.php");

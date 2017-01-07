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
        "HackUtils\\FOpenStream" => "fs/Stream.php",
        "HackUtils\\FileSystem" => "fs/FileSystem.php",
        "HackUtils\\FileSystemInterface" => "fs/FileSystemIntefaces.php",
        "HackUtils\\FileSystemPathInterface" =>
          "fs/FileSystemIntefaces.php",
        "HackUtils\\FileSystemStreamWrapper" =>
          "fs/FileSystemStreamWrapper.php",
        "HackUtils\\Gettable" => "main.php",
        "HackUtils\\JSON" => "json.php",
        "HackUtils\\JSONException" => "json.php",
        "HackUtils\\LocalFileSystem" => "fs/LocalFileSystem.php",
        "HackUtils\\LocalStreamWrapper" => "fs/LocalFileSystem.php",
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
        "HackUtils\\Stat" => "fs/Stat.php",
        "HackUtils\\StatFailed" => "fs/FileSystemIntefaces.php",
        "HackUtils\\StatInterface" => "fs/FileSystemIntefaces.php",
        "HackUtils\\Stream" => "fs/Stream.php",
        "HackUtils\\StreamInterface" => "fs/FileSystemIntefaces.php",
        "HackUtils\\StreamWrapperFileSystem" =>
          "fs/StreamWrapperFileSystem.php",
        "HackUtils\\StreamWrapperInterface" =>
          "fs/FileSystemIntefaces.php",
        "HackUtils\\StrictErrors" => "fs/StrictErrors.php",
        "HackUtils\\StringSorter" => "sort.php",
        "HackUtils\\SymlinkFileSystemInterface" =>
          "fs/FileSystemIntefaces.php",
        "HackUtils\\TimeZone" => "DateTime.php",
        "HackUtils\\ToIntException" => "math.php",
        "HackUtils\\WindowsPath" => "Path.php",
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
require_once (__DIR__."/fs/Stat.php");
require_once (__DIR__."/main.php");
require_once (__DIR__."/math.php");
require_once (__DIR__."/typeof.php");

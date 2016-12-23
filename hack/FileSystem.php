<?hh // strict

namespace HackUtils\FS;

use HackUtils as HU;
use HackUtils\PCRE\Pattern;

abstract class FileSystem {
  public function __construct() {}
  public function __destruct() {}

  public abstract function mkdir(string $path, int $mode = 0777): void;
  public abstract function readdir(string $path): array<string>;
  public abstract function rmdir(string $path): void;

  public function readdir_recursive(string $path): array<string> {
    $ret = [];
    foreach ($this->readdir($path) as $p) {
      $ret[] = $p;
      $full = $this->join($path, $p);
      $stat = $this->stat($full);
      if ($stat && $stat->isDir()) {
        foreach ($this->readdir_recursive($full) as $p2) {
          $ret[] = $this->join($p, $p2);
        }
      }
    }
    return $ret;
  }

  public function mkdir_recursive(string $path, int $mode = 0777): void {
    list($base, $child) = $this->split($path);
    if ($child !== null) {
      $this->ensure_dir($base, $mode);
    }
    $this->mkdir($path, $mode);
  }

  private function ensure_dir(string $path, int $mode): void {
    $stat = $this->stat($path);
    if (!$stat || !$stat->isDir()) {
      $this->mkdir_recursive($path, $mode);
    }
  }

  public abstract function rename(string $oldpath, string $newpath): void;
  public abstract function unlink(string $path): void;

  public abstract function stat(string $path): ?Stat;
  public abstract function chmod(string $path, int $mode): void;
  public abstract function chown(string $path, int $uid): void;
  public abstract function chgrp(string $path, int $gid): void;
  public abstract function utime(string $path, int $atime, int $mtime): void;

  public abstract function open(string $path, string $mode): Stream;

  public abstract function symlink(string $path, string $contents): void;
  public abstract function readlink(string $path): string;

  public abstract function lstat(string $path): ?Stat;
  public abstract function lchown(string $path, int $uid): void;
  public abstract function lchgrp(string $path, int $gid): void;

  public abstract function realpath(string $path): string;

  /** Split a path into dirname and basename */
  public abstract function split(string $path): (string, ?string);
  /** Join an absolute or relative path and relative path together */
  public abstract function join(string $path, string $child): string;

  public function readFile(string $path): string {
    return $this->open($path, 'rb')->getContents();
  }

  public function writeFile(string $path, string $contents): void {
    $this->open($path, 'wb')->write($contents);
  }

  public function appendFile(string $path, string $contents): void {
    $this->open($path, 'ab')->write($contents);
  }

  public function toStreamWrapper(): StreamWrapper {
    return new FileSystemStreamWrapper($this);
  }
}

class Exception extends \RuntimeException {
  public static function checkFalse<T>(string $name, T $ret): T {
    if ($ret === false) {
      $error = \error_get_last();
      if ($error) {
        $e = new self($error['message']);
        $e->file = $error['file'];
        $e->line = $error['line'];
        throw $e;
      }
      throw new self($name.'() failed');
    }
    return $ret;
  }
}

abstract class Stream implements \Psr\Http\Message\StreamInterface {
  // public abstract function chmod(int $mode): void;
  // public abstract function chown(int $uid, int $gid): void;
  public abstract function truncate(int $len): void;
  public abstract function tell(): int;
  public abstract function eof(): bool;
  public abstract function seek(int $offset, int $whence = \SEEK_SET): void;
  public abstract function read(int $length): string;
  public abstract function write(string $data): int;
  public abstract function close(): void;
  public abstract function stat(): Stat;
  public function flush(): void {}
  public function lock(int $flags): bool {
    return true;
  }
  public function getContents(): string {
    $ret = '';
    while (!$this->eof())
      $ret .= $this->read(4096);
    return $ret;
  }
  public function rewind(): void {
    $this->seek(0);
  }
  public function __toString(): string {
    $this->rewind();
    return $this->getContents();
  }
  public function getSize(): int {
    return $this->stat()->size();
  }
  public function getMetadata(?string $key = null): mixed {
    throw new \Exception(__METHOD__.' is not supported');
  }
  public function detach(): ?resource {
    return null;
  }
  public function isSeekable(): bool {
    return true;
  }
}

abstract class Stat {
  public abstract function mtime(): int;
  public abstract function atime(): int;
  public abstract function ctime(): int;
  public abstract function size(): int;
  public abstract function mode(): int;
  public abstract function uid(): int;
  public abstract function gid(): int;
  public function toArray(): stat_array {
    return shape(
      'dev' => 0,
      'ino' => 0,
      'mode' => $this->mode(),
      'nlink' => 1,
      'uid' => $this->uid(),
      'gid' => $this->gid(),
      'rdev' => -1,
      'size' => $this->size(),
      'atime' => $this->atime(),
      'mtime' => $this->mtime(),
      'ctime' => $this->ctime(),
      'blksize' => -1,
      'blocks' => -1,
    );
  }
  public final function isFile(): bool {
    return S_ISREG($this->mode());
  }
  public final function isDir(): bool {
    return S_ISDIR($this->mode());
  }
  public final function isLink(): bool {
    return S_ISLNK($this->mode());
  }
  public final function isSocket(): bool {
    return S_ISSOCK($this->mode());
  }
  public final function isFIFO(): bool {
    return S_ISFIFO($this->mode());
  }
  public final function isCharDevice(): bool {
    return S_ISCHR($this->mode());
  }
  public final function isBlockDevice(): bool {
    return S_ISBLK($this->mode());
  }
}

final class ArrayStat extends Stat {
  public function __construct(private stat_array $stat) {}
  public function mtime(): int {
    return $this->stat['mtime'];
  }
  public function atime(): int {
    return $this->stat['atime'];
  }
  public function ctime(): int {
    return $this->stat['ctime'];
  }
  public function size(): int {
    return $this->stat['size'];
  }
  public function mode(): int {
    return $this->stat['mode'];
  }
  public function uid(): int {
    return $this->stat['uid'];
  }
  public function gid(): int {
    return $this->stat['gid'];
  }
  public function toArray(): stat_array {
    return $this->stat;
  }
}

function format_mode(int $mode): string {
  $s = '';

  $type = $mode & S_IFMT;
  if ($type == S_IFWHT)
    $s .= 'w'; else if ($type == S_IFDOOR)
    $s .= 'D'; else if ($type == S_IFSOCK)
    $s .= 's'; else if ($type == S_IFLNK)
    $s .= 'l'; else if ($type == S_IFNWK)
    $s .= 'n'; else if ($type == S_IFREG)
    $s .= '-'; else if ($type == S_IFBLK)
    $s .= 'b'; else if ($type == S_IFDIR)
    $s .= 'd'; else if ($type == S_IFCHR)
    $s .= 'c'; else if ($type == S_IFIFO)
    $s .= 'p'; else
    $s .= '?';

  $s .= $mode & S_IRUSR ? 'r' : '-';
  $s .= $mode & S_IWUSR ? 'w' : '-';
  if ($mode & S_ISUID)
    $s .= $mode & S_IXUSR ? 's' : 'S'; else
    $s .= $mode & S_IXUSR ? 'x' : '-';

  $s .= $mode & S_IRGRP ? 'r' : '-';
  $s .= $mode & S_IWGRP ? 'w' : '-';
  if ($mode & S_ISGID)
    $s .= $mode & S_IXGRP ? 's' : 'S'; else
    $s .= $mode & S_IXGRP ? 'x' : '-';

  $s .= $mode & S_IROTH ? 'r' : '-';
  $s .= $mode & S_IWOTH ? 'w' : '-';
  if ($mode & S_ISVTX)
    $s .= $mode & S_IXOTH ? 't' : 'T'; else
    $s .= $mode & S_IXOTH ? 'x' : '-';

  return $s;
}

type stat_array = shape(
  // 'dev' => int,
  // 'ino' => int,
  'mode' => int,
  // 'nlink' => int,
  'uid' => int,
  'gid' => int,
  // 'rdev' => int,
  'size' => int,
  'atime' => int,
  'mtime' => int,
  'ctime' => int,
// 'blksize' => int,
// 'blocks' => int,
);

abstract class StreamWrapper extends FileSystem {
  private static function notFalse<T>(string $name, T $ret): T {
    return Exception::checkFalse($name, $ret);
  }
  public final function open(string $path, string $mode): Stream {
    $path = $this->fixPath($path);
    return new StreamWrapperStream($path, $mode, $this->ctx());
  }
  public final function stat(string $path): ?Stat {
    $path = $this->fixPath($path);
    \clearstatcache();
    // We have to avoid calling stat() if the file doesn't exist so we
    // can return null without throwing an exception or triggering a PHP error.
    // PHP's stat cache should ensure only one stat call is actually made
    // to the underlying stream wrapper if the file does exist.
    if (!\file_exists($path))
      return null;
    return new ArrayStat(self::notFalse('stat', \stat($path)));
  }
  public final function rename(string $from, string $to): void {
    $from = $this->fixPath($from);
    $to = $this->fixPath($to);
    self::notFalse('rename', \rename($from, $to, $this->ctx()));
  }
  public final function readdir(string $path): array<string> {
    $path = $this->fixPath($path);
    $ret = [];
    for (
      $dir = self::notFalse('opendir', \opendir($path, $this->ctx()));
      $p = \readdir($dir);
      $p !== false
    ) {
      if ($p === '.' || $p === '..')
        continue;
      $ret[] = $p;
    }
    \closedir($dir);
    return $ret;
  }
  public final function mkdir(string $path, int $mode = 0777): void {
    $path = $this->fixPath($path);
    self::notFalse('mkdir', \mkdir($path, $mode, false, $this->ctx()));
  }
  public final function mkdir_recursive(string $path, int $mode = 0777): void {
    $path = $this->fixPath($path);
    self::notFalse('mkdir', \mkdir($path, $mode, true, $this->ctx()));
  }
  public final function unlink(string $path): void {
    $path = $this->fixPath($path);
    self::notFalse('unlink', \unlink($path, $this->ctx()));
  }
  public final function lstat(string $path): ?Stat {
    $path = $this->fixPath($path);
    \clearstatcache();
    // We have to avoid calling lstat() if the file doesn't exist
    // so we can return null without throwing an Exception
    // or raising a PHP error.
    // PHP's stat cache should ensure only one stat is actually done
    // on the underlying stream wrapper.
    if (!\file_exists($path) && !\is_link($path))
      return null;
    return new ArrayStat(self::notFalse('lstat', \lstat($path)));
  }
  public final function rmdir(string $path): void {
    $path = $this->fixPath($path);
    self::notFalse('rmdir', \rmdir($path, $this->ctx()));
  }
  public final function chmod(string $path, int $mode): void {
    $path = $this->fixPath($path);
    self::notFalse('chmod', \chmod($path, $mode));
  }
  public final function chown(string $path, int $uid): void {
    $path = $this->fixPath($path);
    self::notFalse('chown', \chown($path, (int) $uid));
  }
  public final function chgrp(string $path, int $gid): void {
    $path = $this->fixPath($path);
    self::notFalse('chgrp', \chgrp($path, (int) $gid));
  }
  public final function utime(string $path, int $atime, int $mtime): void {
    $path = $this->fixPath($path);
    self::notFalse('touch', \touch($path, $mtime, $atime));
  }
  public final function readFile(string $path): string {
    $path = $this->fixPath($path);
    return self::notFalse(
      'file_get_contents',
      \file_get_contents($path, false, $this->ctx()),
    );
  }
  public final function writeFile(string $path, string $contents): void {
    $path = $this->fixPath($path);
    self::notFalse(
      'file_put_contents',
      \file_put_contents($path, $contents, 0, $this->ctx()),
    );
  }
  public final function appendFile(string $path, string $contents): void {
    $path = $this->fixPath($path);
    self::notFalse(
      'file_put_contents',
      \file_put_contents($path, $contents, \FILE_APPEND, $this->ctx()),
    );
  }
  public final function toStreamWrapper(): StreamWrapper {
    return $this;
  }
  public abstract function wrapPath(string $path): string;
  public function context(): ?resource {
    return null;
  }
  private function fixPath(string $path): string {
    return $this->wrapPath($path);
  }
  private function ctx(): ?resource {
    return $this->context();
  }
}

final class _streamWrapper {
  public static function className(): string {
    return __NAMESPACE__.'\\_streamWrapper';
  }

  public ?resource $context;
  private array<string> $readdir = [];
  private int $readdir_i = 0;
  private ?Stream $stream;

  public function __construct() {}
  public function __destruct() {}
  public function dir_closedir(): bool {
    return true;
  }
  public function dir_opendir(string $path, int $options): bool {
    list($fs, $path) = $this->unwrap($path);
    $this->readdir = HU\concat(['.', '..'], $fs->readdir($path));
    $this->readdir_i = 0;
    return true;
  }
  public function dir_readdir(): mixed /* string|false */ {
    $i = $this->readdir_i++;
    if ($i >= \count($this->readdir))
      return false;
    return $this->readdir[$i];
  }
  public function dir_rewinddir(): bool {
    $this->readdir_i = 0;
    return true;
  }
  public function mkdir(string $path, int $mode, int $options): bool {
    list($fs, $path) = $this->unwrap($path);
    if ($options & \STREAM_MKDIR_RECURSIVE)
      $fs->mkdir_recursive($path, $mode); else
      $fs->mkdir($path, $mode);
    return true;
  }
  public function rename(string $path_from, string $path_to): bool {
    list($fs, $path_from) = $this->unwrap($path_from);
    list($_, $path_to) = $this->unwrap($path_to);
    $fs->rename($path_from, $path_to);
    return true;
  }
  public function rmdir(string $path, int $options): bool {
    list($fs, $path) = $this->unwrap($path);
    $fs->rmdir($path);
    return true;
  }
  // public function stream_cast(int $cast_as): mixed /* resource|false */ {
  //   return false;
  // }
  public function stream_close(): void {
    $this->stream()->close();
  }
  public function stream_eof(): bool {
    return $this->stream()->eof();
  }
  public function stream_flush(): bool {
    $this->stream()->flush();
    return true;
  }
  public function stream_lock(int $operation): bool {
    // Stream wrappers don't support the $wouldblock flock() parameter :((((
    return $this->stream()->lock($operation);
  }
  public function stream_metadata(
    string $path,
    int $option,
    mixed $value,
  ): bool {
    list($fs, $path) = $this->unwrap($path);
    switch ($option) {
      case \STREAM_META_TOUCH:
        list($mtime, $atime) = \is_array($value) ? $value : [];
        $fs->utime($path, $atime, $mtime);
        return true;
      case \STREAM_META_OWNER_NAME:
        $fs->chown($path, $this->name2uid((string) $value));
        return true;
      case \STREAM_META_OWNER:
        $fs->chown($path, (int) $value);
        return true;
      case \STREAM_META_GROUP_NAME:
        $fs->chgrp($path, $this->name2gid((string) $value));
        return true;
      case \STREAM_META_GROUP:
        $fs->chgrp($path, (int) $value);
        return true;
      case \STREAM_META_ACCESS:
        $fs->chmod($path, (int) $value);
        return true;
      default:
        return false;
    }
  }
  private function name2gid(string $name): int {
    $data = \posix_getgrnam($name);
    if (!$data)
      throw new Exception(\posix_strerror(\posix_get_last_error()));
    return $data['gid'];
  }
  private function name2uid(string $name): int {
    $data = \posix_getpwnam($name);
    if (!$data)
      throw new Exception(\posix_strerror(\posix_get_last_error()));
    return $data['uid'];
  }
  public function stream_open(
    string $path,
    string $mode,
    int $options,
    string $opened_path,
  ): bool {
    if ($options & \STREAM_USE_PATH) {
      throw new Exception('STREAM_USE_PATH is not supported');
    }
    // TODO What if STREAM_REPORT_ERRORS is not set?
    list($fs, $path) = $this->unwrap($path);
    $this->stream = $fs->open($path, $mode);
    return true;
  }
  public function stream_read(int $count): string {
    return $this->stream()->read($count);
  }
  public function stream_seek(int $offset, int $whence = \SEEK_SET): bool {
    $this->stream()->seek($offset, $whence);
    return true;
  }
  // public function stream_set_option(
  //   int $option,
  //   int $arg1,
  //   int $arg2,
  // ): bool {
  //   switch ($option) {
  //     case \STREAM_OPTION_BLOCKING:
  //       // TODO
  //       return true;
  //     case \STREAM_OPTION_READ_TIMEOUT:
  //       // TODO
  //       return true;
  //     case \STREAM_OPTION_WRITE_BUFFER:
  //       // TODO
  //       return true;
  //     default:
  //       return false;
  //   }
  // }
  public function stream_stat(): stat_array {
    return $this->stream()->stat()->toArray();
  }
  public function stream_tell(): int {
    return $this->stream()->tell();
  }
  public function stream_truncate(int $new_size): bool {
    $this->stream()->truncate($new_size);
    return true;
  }
  public function stream_write(string $data): int {
    return $this->stream()->write($data);
  }
  public function unlink(string $path): bool {
    list($fs, $path) = $this->unwrap($path);
    $fs->unlink($path);
    return true;
  }
  public function url_stat(
    string $path,
    int $flags,
  ): mixed /*stat_array|false*/ {
    list($fs, $path) = $this->unwrap($path);

    if ($flags & \STREAM_URL_STAT_LINK)
      $stat = $fs->lstat($path); else
      $stat = $fs->stat($path);

    if (!$stat) {
      // For file_exists(), is_file() etc
      if ($flags & \STREAM_URL_STAT_QUIET)
        return false;
      throw new Exception("Cannot stat '$path', path does not exist");
    }

    return $stat->toArray();
  }
  private function unwrap(string $path): (FileSystem, string) {
    return FileSystemStreamWrapper::unwrapPath($path);
  }
  private function stream(): Stream {
    if (!$this->stream)
      throw new Exception('No stream is open');
    return $this->stream;
  }
}

final class FileSystemStreamWrapper extends StreamWrapper {
  private static int $next = 1;
  private static array<arraykey, FileSystem> $fss = [];
  private static bool $registered = false;

  public static function unwrapPath(string $path): (FileSystem, string) {
    $match =
      Pattern::create('^hu-fs://(.*?):(.*)$', 'xDsS')->matchOrThrow($path);
    return tuple(self::$fss[$match->get(1)], $match->get(2));
  }

  private int $id;

  public function __construct(private FileSystem $fs) {
    parent::__construct();
    if (!self::$registered) {
      \stream_wrapper_register('hu-fs', _streamWrapper::className());
      self::$registered = true;
    }
    $this->id = self::$next++;
    self::$fss[$this->id] = $fs;
  }
  public function __destruct() {
    unset(self::$fss[$this->id]);
  }

  public function wrapPath(string $path): string {
    return 'hu-fs://'.$this->id.':'.$path;
  }
  public function unwrap(): FileSystem {
    return $this->fs;
  }

  public function split(string $path): (string, ?string) {
    return $this->fs->split($path);
  }
  public function join(string $path, string $child): string {
    return $this->fs->join($path, $child);
  }
  public function symlink(string $path, string $contents): void {
    $this->fs->symlink($path, $contents);
  }
  public function readlink(string $path): string {
    return $this->fs->readlink($path);
  }
  public function lchown(string $path, int $uid): void {
    $this->fs->lchown($path, $uid);
  }
  public function lchgrp(string $path, int $gid): void {
    $this->fs->lchgrp($path, $gid);
  }
  public function realpath(string $path): string {
    return $this->fs->realpath($path);
  }
}

final class LocalFileSystem extends StreamWrapper {
  const string DIR_SEP = \DIRECTORY_SEPARATOR;

  public final function symlink(string $path, string $target): void {
    $path = $this->wrapPath($path);
    Exception::checkFalse('symlink', \symlink($path, $target));
  }

  public final function readlink(string $path): string {
    $path = $this->wrapPath($path);
    return Exception::checkFalse('readlink', \readlink($path));
  }

  public final function realpath(string $path): string {
    $path = $this->wrapPath($path);
    \clearstatcache();
    return Exception::checkFalse('realpath', \realpath($path));
  }

  public final function lchown(string $path, int $uid): void {
    $path = $this->wrapPath($path);
    Exception::checkFalse('lchown', \lchown($path, (int) $uid));
  }

  public final function lchgrp(string $path, int $gid): void {
    $path = $this->wrapPath($path);
    Exception::checkFalse('lchgrp', \lchgrp($path, (int) $gid));
  }

  public function join(string $base, string $path): string {
    if ($this->isAbsolute($path))
      throw new \Exception('Path must be relative');
    if ($base === '')
      return $path;
    if ($this->isWindows()) {
      if (!$this->regex('(^[A-Za-z]:|[/\\])$')->matches($base))
        $base .= '\\';
    } else {
      if (HU\slice($base, -1) !== '/')
        $base .= '/';
    }
    $base .= $path;
    return $base;
  }

  public function split(string $path): (string, ?string) {
    $regex =
      $this->isWindows() ? '^
        (
          (?:
            [A-Za-z]:
            |
            [\\/]{2}
            [^\\/]+
          )?
          [\\/]*
          .*?
        )
        [\\/]*
        ([^\\/]+)?
        [\\/]*
      $' : '^
        (
          /*
          .*?
        )
        /*
        ([^/]+)?
        /*
      $';

    $match = $this->regex($regex)->matchOrThrow($path);
    return tuple($match->get(1), $match->getOrNull(2));
  }

  public function wrapPath(string $path): string {
    // Look at php_stream_locate_url_wrapper() in PHP source
    // or Stream::getWrapperProtocol() in HHVM
    //
    // Basically, any path that matches this regex is likely to be considered a
    // URL for another stream wrapper.
    //
    // Handily, a path that matches this regex is guaranteed not to be an
    // absolute path on POSIX or Windows, so if it matches we can safely
    // force it not to match by prefixing it with ./ on POSIX and .\ on
    // Windows.

    if ($this->regex('^([a-zA-Z0-9+\\-.]{2,}:\\/\\/|data:|zlib:)')
          ->matches($path)) {
      return '.'.self::DIR_SEP.$path;
    }

    return $path;
  }

  private function isAbsolute(string $path): bool {
    if ($this->isWindows())
      return $this->regex('^([A-Za-z]:|[\\/])')->matches($path);
    return $path && $path[0] === '/';
  }

  private function isWindows(): bool {
    return self::DIR_SEP === '\\';
  }

  private function regex(string $regex): Pattern {
    return Pattern::create($regex, 'xDsS');
  }
}

final class StreamWrapperStream extends Stream {
  private static function notFalse<T>(string $name, T $ret): T {
    return Exception::checkFalse($name, $ret);
  }
  private resource $handle;
  public function __construct(string $url, string $mode, ?resource $ctx) {
    parent::__construct();
    $this->handle = self::notFalse('fopen', \fopen($url, $mode, false, $ctx));
  }
  public function read(int $length): string {
    return self::notFalse('fread', \fread($this->handle, $length));
  }
  public function write(string $data): int {
    return self::notFalse('fwrite', \fwrite($this->handle, $data));
  }
  public function eof(): bool {
    return \feof($this->handle);
  }
  public function seek(int $offset, int $whence = \SEEK_SET): void {
    self::notFalse('fseek', \fseek($this->handle, $offset, $whence));
  }
  public function tell(): int {
    return self::notFalse('ftell', \ftell($this->handle));
  }
  public function close(): void {
    self::notFalse('fclose', \fclose($this->handle));
  }
  public function flush(): void {
    self::notFalse('fflush', \fflush($this->handle));
  }
  public function lock(int $flags): bool {
    $wb = false;
    $ret = \flock($this->handle, $flags, $wb);
    // An EWOULDBLOCK should quietly return false
    if ($wb)
      return false;
    return self::notFalse('flock', $ret);
  }
  public function truncate(int $length): void {
    self::notFalse('ftruncate', \ftruncate($this->handle, $length));
  }
  public function stat(): Stat {
    return new ArrayStat(self::notFalse('fstat', \fstat($this->handle)));
  }
  public function getContents(): string {
    return self::notFalse(
      \stream_get_contents($this->handle),
      'stream_get_contents',
    );
  }
  public function isReadable(): bool {
    $ret = self::notFalse(
      'stream_get_meta_data',
      \stream_get_meta_data($this->handle),
    );
    $mode = $ret['mode'];
    return \strstr($mode, 'r') || \strstr($mode, '+');
  }
  public function isWritable(): bool {
    $ret = self::notFalse(
      'stream_get_meta_data',
      \stream_get_meta_data($this->handle),
    );
    $mode = $ret['mode'];
    return
      \strstr($mode, 'x') ||
      \strstr($mode, 'w') ||
      \strstr($mode, 'c') ||
      \strstr($mode, 'a') ||
      \strstr($mode, '+');
  }
  public function getMetadata(?string $key = null): mixed {
    $ret = self::notFalse(
      'stream_get_meta_data',
      \stream_get_meta_data($this->handle),
    );
    if ($key === null)
      return $ret;
    /* HH_IGNORE_ERROR[4051] */
    return $ret[$key];
  }
}

<?hh // strict

namespace HackUtils;

final class _streamWrapper {
  public static function className(): string {
    return __CLASS__;
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
    $this->readdir = concat(['.', '..'], $fs->readdir($path));
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
      $fs->mkdir_rec($path, $mode); else
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
      throw new \RuntimeException(\posix_strerror(\posix_get_last_error()));
    return $data['gid'];
  }

  private function name2uid(string $name): int {
    $data = \posix_getpwnam($name);
    if (!$data)
      throw new \RuntimeException(\posix_strerror(\posix_get_last_error()));
    return $data['uid'];
  }

  public function stream_open(
    string $path,
    string $mode,
    int $options,
    string $opened_path,
  ): bool {
    if ($options & \STREAM_USE_PATH) {
      throw new \Exception('STREAM_USE_PATH is not supported');
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

  public function stream_set_option(int $option, int $arg1, int $arg2): bool {
    switch ($option) {
      case \STREAM_OPTION_BLOCKING:
        // TODO
        throw new \Exception('STREAM_OPTION_BLOCKING is not supported');
      case \STREAM_OPTION_READ_TIMEOUT:
        // TODO
        throw new \Exception('STREAM_OPTION_READ_TIMEOUT is not supported');
      case \STREAM_OPTION_WRITE_BUFFER:
        $this->stream()->setbuf($arg1 === \STREAM_BUFFER_NONE ? 0 : $arg2);
        return true;
      default:
        return false;
    }
  }

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
      throw new \RuntimeException("Cannot stat '$path', path does not exist");
    }

    return $stat->toArray();
  }

  private function unwrap(string $path): (FileSystem, string) {
    return FileSystemStreamWrapper::unwrapPath($path);
  }

  private function stream(): Stream {
    if (!$this->stream)
      throw new \Exception('No stream is open');
    return $this->stream;
  }
}

final class FileSystemStreamWrapper extends StreamWrapper {
  private static int $next = 1;
  private static array<arraykey, FileSystem> $fss = [];
  private static bool $registered = false;

  public static function unwrapPath(string $path): (FileSystem, string) {
    $match =
      PCRE\Pattern::create('^hu-fs://(.*?):(.*)$', 'xDsS')
        ->matchOrThrow($path);
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

  public function sep(): string {
    return $this->fs->sep();
  }

  public function unwrap(): FileSystem {
    return $this->fs;
  }

  public function path(string $path): Path {
    return $this->fs->path($path);
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

  public function pwd(): string {
    return $this->fs->pwd();
  }
}

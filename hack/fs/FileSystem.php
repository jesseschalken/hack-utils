<?hh // strict

namespace HackUtils;

class TestFileSystem extends Test {
  public function run(): void {
    $fs = new LocalFileSystem();
    $path = '/tmp/hufs-test-'.\mt_rand();
    self::testFilesystem($fs, $path);
    $fs = new FileSystemStreamWrapper($fs);
    self::testFilesystem($fs, $path);
  }

  private static function testFilesystem(FileSystem $fs, string $base): void {
    self::assertEqual($fs->trystat($base), NULL_INT);
    $fs->mkdir($base);
    self::assertEqual($fs->stat($base)->modeSymbolic(), 'drwxr-xr-x');

    $file = $fs->join($base, 'foo');
    $fs->writeFile($file, 'contents');
    self::assertEqual($fs->readFile($file), 'contents');

    $open = $fs->open($file, 'rb');
    self::assertEqual($open->eof(), false);
    self::assertEqual($open->tell(), 0);
    self::assertEqual($open->read(4), 'cont');
    self::assertEqual($open->eof(), false);
    self::assertEqual($open->tell(), 4);
    $open->seek(2);
    self::assertEqual($open->tell(), 2);
    $open->seek(2, \SEEK_CUR);
    self::assertEqual($open->tell(), 4);
    self::assertEqual($open->eof(), false);
    self::assertEqual($open->read(100), 'ents');
    self::assertEqual($open->read(100), '');
    self::assertEqual($open->eof(), true);
    self::assertEqual($open->getSize(), 8);
    self::assertEqual($open->stat()->modeSymbolic(), '-rw-r--r--');
    self::assertEqual($open->getContents(), '');
    self::assertEqual($open->__toString(), 'contents');
    self::assertEqual($open->getContents(), '');
    $open->rewind();
    self::assertEqual($open->getContents(), 'contents');
    self::assertEqual($open->tell(), 8);
    self::assertEqual($open->isReadable(), true);
    self::assertEqual($open->isWritable(), false);
    self::assertEqual($open->isSeekable(), true);
    $open->close();

    $open = $fs->open($file, 'wb+');
    self::assertEqual($open->tell(), 0);
    self::assertEqual($open->eof(), false);
    self::assertEqual($open->getSize(), 0);
    self::assertEqual($open->getContents(), '');
    self::assertEqual($open->__toString(), '');
    self::assertEqual($open->write('hello'), 5);
    self::assertEqual($open->tell(), 5);
    self::assertEqual($open->eof(), true);
    self::assertEqual($open->getContents(), '');
    self::assertEqual($open->__toString(), 'hello');
    $open->rewind();
    self::assertEqual($open->getContents(), 'hello');
    self::assertEqual($open->getContents(), '');
    $open->seek(2);
    self::assertEqual($open->tell(), 2);
    self::assertEqual($open->write('__'), 2);
    self::assertEqual($open->tell(), 4);
    self::assertEqual($open->getContents(), 'o');
    self::assertEqual($open->tell(), 5);
    self::assertEqual($open->__toString(), 'he__o');
    self::assertEqual($open->tell(), 5);
    self::assertEqual($open->eof(), true);

    if ($fs instanceof SymlinkFileSystemInterface) {
      $fs->symlink($file.'2', $file);
      self::assertEqual($fs->stat($file)->modeSymbolic(), '-rw-r--r--');
      self::assertEqual($fs->stat($file.'2')->modeSymbolic(), '-rw-r--r--');
      self::assertEqual($fs->lstat($file)->modeSymbolic(), '-rw-r--r--');
      self::assertEqual($fs->lstat($file.'2')->modeSymbolic(), 'lrwxrwxrwx');
    }

    $fs->unlink($file);

    $fs->rmdirRec($base);
  }
}

interface FileSystemPathInterface {
  /**
   * Split a path into two at the given offset into the list of path
   * components. The first path has the same root as the input path and the
   * second path is always relative. Negative offsets are supported.
   *
   * Example:
   * list($root, $path) = $fs->split($path, 0);
   * list($path, $basename) = $fs->split($path, -1);
   */
  public function split(string $path, int $i): (string, string);

  /**
   * Join two paths together. If the second path is absolute, it is returned.
   */
  public function join(string $path1, string $path2): string;
}

interface SymlinkFileSystemInterface {
  require extends FileSystem;

  public function symlink(string $path, string $target): void;
  public function readlink(string $path): string;
  public function realpath(string $path): string;
  public function lchown(string $path, int $uid): void;
  public function lchgrp(string $path, int $gid): void;
}

class StatFailed extends Exception {}

abstract class FileSystem implements FileSystemPathInterface {

  public abstract function mkdir(string $path, int $mode = 0777): void;
  public abstract function readdir(string $path): array<string>;
  public abstract function rmdir(string $path): void;

  public abstract function open(string $path, string $mode): Stream;
  public abstract function rename(string $oldpath, string $newpath): void;
  public abstract function unlink(string $path): void;

  /** Must throw a StatFailed in the case of failure. */
  public abstract function stat(string $path): Stat;
  /** Must throw a StatFailed in the case of failure. */
  public abstract function lstat(string $path): Stat;

  public abstract function trystat(string $path): ?Stat;
  public abstract function trylstat(string $path): ?Stat;

  public abstract function chmod(string $path, int $mode): void;
  public abstract function chown(string $path, int $uid): void;
  public abstract function chgrp(string $path, int $gid): void;
  public abstract function utime(string $path, int $atime, int $mtime): void;

  /**
   * Remvoe a file or directory (unlink/rmdir).
   */
  public final function remove(string $path): void {
    $stat = $this->lstat($path);
    if ($stat && $stat->isDir())
      $this->rmdir($path); else
      $this->unlink($path);
  }

  /**
   * Same as readdir() but returns complete file paths instead of just names.
   * If the given path is relative, the returned paths will also be relative.
   */
  public final function readdirPaths(string $path): array<string> {
    $ret = [];
    foreach ($this->readdir($path) as $p) {
      $ret[] = $this->join($path, $p);
    }
    return $ret;
  }

  public final function readdirPathsRec(string $path): array<string> {
    $ret = [];
    foreach ($this->readdirPaths($path) as $p) {
      $ret[] = $p;
      $stat = $this->stat($p);
      if ($stat && $stat->isDir()) {
        foreach ($this->readdirPathsRec($p) as $p2) {
          $ret[] = $p2;
        }
      }
    }
    return $ret;
  }

  /**
   * All of the non-directory descendants of the given directory, relative to
   * the directory.
   */
  public final function readdirRec(string $path): array<string> {
    $ret = [];
    foreach ($this->readdir($path) as $p) {
      foreach ($this->expandDirs($this->join($path, $p)) as $p2) {
        $ret[] = $this->join($p, $p2);
      }
    }
    return $ret;
  }

  /**
   * Same as readdirRec(), except if the input is not a directory, returns
   * an empty relative path (the empty string).
   * This is useful if your program takes input that can either be a directory
   * (to be scanned recursively) or a single file.
   */
  public final function expandDirs(string $path): array<string> {
    $stat = $this->stat($path);
    if ($stat && $stat->isDir())
      return $this->readdirRec($path);
    return [''];
  }

  public final function removeRec(string $path): int {
    $stat = $this->lstat($path);
    if ($stat && $stat->isDir()) {
      return $this->rmdirRec($path);
    }
    $this->unlink($path);
    return 1;
  }

  public final function rmdirRec(string $path): int {
    $ret = 0;
    foreach ($this->readdirPaths($path) as $p) {
      $ret += $this->removeRec($p);
    }
    $this->rmdir($path);
    $ret++;
    return $ret;
  }

  public final function createDirs(string $path, int $mode = 0777): void {
    list($dir, $child) = $this->split($path, -1);
    if ($child !== '' && !$this->lexists($dir)) {
      $this->mkdirRec($dir, $mode);
    }
  }

  public final function exists(string $path): bool {
    return $this->stat($path) ? true : false;
  }

  public final function lexists(string $path): bool {
    return $this->lstat($path) ? true : false;
  }

  public final function readFile(string $path): string {
    return $this->open($path, 'rb')->getContents();
  }

  public final function writeFile(string $path, string $contents): int {
    return $this->open($path, 'wb')->write($contents);
  }

  public final function appendFile(string $path, string $contents): int {
    return $this->open($path, 'ab')->write($contents);
  }

  public final function writeFileRec(string $path, string $contents): int {
    $this->createDirs($path);
    return $this->writeFile($path, $contents);
  }

  public final function appendFileRec(string $path, string $contents): int {
    $this->createDirs($path);
    return $this->appendFile($path, $contents);
  }

  public final function mkdirRec(string $path, int $mode = 0777): void {
    $this->createDirs($path, $mode);
    $this->mkdir($path, $mode);
  }
}

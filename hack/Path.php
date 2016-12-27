<?hh // strict

namespace HackUtils;

abstract class Path {
  public static function parse(string $path): Path {
    return is_windows() ? WindowsPath::parse($path) : PosixPath::parse($path);
  }

  public final function relativeTo<T super this as Path>(T $base): T {
    if (!$this->hasSameRoot($base))
      return $this;

    $l = min($this->len(), $base->len());
    $i = 0;
    while ($i < $l && $this->name($i) === $base->name($i))
      $i++;

    $names = concat(
      repeat('..', $base->len() - $i),
      slice_array($this->names(), $i),
    );
    return $this->withNames($names, true);
  }

  public final function len(): int {
    return \count($this->names());
  }

  public final function name(int $i): string {
    return get_offset($this->names(), $i);
  }

  public function normalize(): this {
    $names = [];
    foreach ($this->names() as $name) {
      if ($name === '' || $name === '.')
        continue;
      // How the ".." (parent) of a root is handled depends on the file system.
      // For now we'll just collapse ".." when the parent isn't a root.
      // Subclasses can override this for more specific behaviour.
      if ($name === '..' && $names) {
        list($rest, $last) = pop($names);
        // Only collapse ".." if the last name wasn't "..", otherwise
        // "../../.." would turn into "..".
        if ($last !== '..') {
          $names = $rest;
          continue;
        }
      }
      $names[] = $name;
    }
    return $this->withNames($names);
  }

  public final function join_str(string $name): this {
    return $this->join($this->reparse($name));
  }

  public abstract function isAbsolute(): bool;

  public final function join<T super this as Path>(T $path): T {
    if ($path->isAbsolute())
      return $path;
    return $this->withNames(concat($this->names(), $path->names()));
  }

  public final function dir(): ?this {
    if (!$this->len())
      return new_null();
    return $this->withNames(slice_array($this->names(), 0, -1));
  }

  public final function base(): ?string {
    if (!$this->len())
      return NULL_STRING;
    return $this->name(-1);
  }

  public final function ext(): ?string {
    $name = $this->base();
    if ($name === null)
      return NULL_STRING;
    $pos = find_last($name, '.');
    if ($pos === null || $pos == 0)
      return NULL_STRING;
    return slice($name, $pos + 1);
  }

  public abstract function names(): array<string>;
  public abstract function format(): string;
  public abstract function hasSameRoot(Path $path): bool;
  public abstract function withNames(
    array<string> $names,
    bool $relative = false,
  ): this;
  public abstract function reparse(string $path): this;
}

final class PosixPath extends Path {
  public static function parse(string $path): PosixPath {
    $self = new self();
    $self->fromString($path);
    return $self;
  }

  private bool $absolute = false;
  private array<string> $names = [];

  private function __construct() {}

  public function normalize(): this {
    $ret = parent::normalize();
    // POSIX has a specific policy of "/../../.." being equivelant to "/".
    // On Windows the story is less clear, since there are so many different
    // types of path roots, so it isn't done by default in the parent class.
    if ($ret->absolute) {
      $i = 0;
      $l = \count($this->names);
      while ($i < $l && $this->names[$i] === '..')
        $i++;
      if ($i)
        $ret->names = slice_array($this->names, $i);
    }
    return $ret;
  }

  public function format(): string {
    $ret = join($this->names, '/');
    if ($this->absolute)
      $ret = '/'.$ret;
    if ($ret === '')
      $ret = '.';
    return $ret;
  }

  public function names(): array<string> {
    return $this->names;
  }

  public function withNames(
    array<string> $names,
    bool $relative = false,
  ): this {
    $clone = clone $this;
    $clone->names = $names;
    if ($relative)
      $clone->absolute = false;
    return $clone;
  }

  public function isAbsolute(): bool {
    return $this->absolute;
  }

  public function hasSameRoot(Path $path): bool {
    return $path instanceof PosixPath && $path->absolute === $this->absolute;
  }

  public function reparse(string $path): this {
    $clone = clone $this;
    $clone->fromString($path);
    return $clone;
  }

  private function fromString(string $path): void {
    if ($path === '') {
      $this->absolute = false;
      $this->names = [];
    } else {
      $this->absolute = $path[0] === '/';
      $this->names = [];
      foreach (split($path, '/') as $name) {
        if ($name !== '')
          $this->names[] = $name;
      }
    }
  }
}

final class WindowsPath extends Path {
  public static function parse(string $path): WindowsPath {
    $self = new self();
    $self->fromString($path);
    return $self;
  }

  private static function regex(string $regex): PCRE\Pattern {
    return PCRE\Pattern::create($regex, 'xDsS');
  }

  private string $root = '';
  private array<string> $names = [];

  private function __construct() {}

  public function format(): string {
    $ret = $this->root.join($this->names, '\\');
    if ($ret === '')
      return '.';
    return $ret;
  }

  public function names(): array<string> {
    return $this->names;
  }

  public function withNames(
    array<string> $names,
    bool $relative = false,
  ): this {
    $clone = clone $this;
    $clone->names = $names;
    if ($relative)
      $clone->root = '';
    return $clone;
  }

  public function isAbsolute(): bool {
    return $this->root !== '';
  }

  public function hasSameRoot(Path $path): bool {
    return $path instanceof WindowsPath && $path->root === $this->root;
  }

  public function reparse(string $path): this {
    $clone = clone $this;
    $clone->fromString($path);
    return $clone;
  }

  private function fromString(string $path): void {
    $regex = self::regex('^
      (
        [A-Za-z]:[\\\\/]?
        |
        [\\\\/]{0,2}
        (?![\\\\/])
      )
      (.*)
    $');
    $match = $regex->matchOrThrow($path);
    $root = $match->get(1);
    $path = $match->get(2);
    $this->root = replace($root, '/', '\\');
    $this->names = [];
    foreach (self::regex('[^\\\\/]+')->matchAll($path) as $match) {
      $this->names[] = $match->toString();
    }
  }
}

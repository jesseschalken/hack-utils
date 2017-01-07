<?hh // strict

namespace HackUtils;

/**
 * A path is a sequence of names or path components relative to some root.
 * On POSIX, the possible roots are "/" and "" (".").
 * On Windows, there are many possible roots. See
 *   https://en.wikipedia.org/wiki/Path_(computing)#Representations_of_paths_by_operating_system_and_shell
 */
<<__ConsistentConstruct>>
abstract class Path {
  public static function parse(string $path): Path {
    return
      \DIRECTORY_SEPARATOR === '\\'
        ? WindowsPath::parse($path)
        : PosixPath::parse($path);
  }

  public function __construct(protected array<string> $names = []) {}

  public final function relativeTo<T super this as Path>(T $base): T {
    // There's no way we can make a path relative to another if they
    // have different roots.
    if (!$this->hasSameRoot($base))
      return $this;

    $l = min(\count($this->names), \count($base->names));
    $i = 0;
    while ($i < $l && $this->names[$i] === $base->names[$i]) {
      $i++;
    }

    return new static(
      concat(
        repeat('..', \count($base->names) - $i),
        slice_array($this->names, $i),
      ),
    );
  }

  public function normalize(): this {
    $names = [];
    foreach ($this->names as $name) {
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
    return new static($names);
  }

  public final function split(int $i): (this, this) {
    list($left, $right) = split_array_at($this->names, $i);
    $clone = clone $this;
    $clone->names = $left;
    return tuple($clone, new static($right));
  }

  public final function join<T super this as Path>(T $path): T {
    if ($path->isAbsolute())
      return $path;
    $clone = clone $this;
    $clone->names = concat($this->names, $path->names);
    return $clone;
  }

  public abstract function isAbsolute(): bool;
  public abstract function format(): string;
  public abstract function hasSameRoot(Path $path): bool;
}

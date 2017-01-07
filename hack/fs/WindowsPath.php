<?hh // strict

namespace HackUtils;

final class WindowsPath extends Path {
  public static function parse(string $path): WindowsPath {
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

    $self = new self();
    $self->root = replace($root, '/', '\\');
    foreach (self::regex('[^\\\\/]+')->matchAll($path) as $match) {
      $self->names[] = $match->toString();
    }
    return $self;
  }

  private static function regex(string $regex): PCRE\Pattern {
    return PCRE\Pattern::create($regex, 'xDsS');
  }

  private string $root = '';

  public function format(): string {
    return $this->root.join($this->names, '\\');
  }

  public function isAbsolute(): bool {
    return $this->root !== '';
  }

  public function hasSameRoot(Path $path): bool {
    return $path instanceof WindowsPath && $path->root === $this->root;
  }
}

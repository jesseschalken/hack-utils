<?hh // strict

namespace HackUtils;

final class PosixPath extends Path {
  public static function parse(string $path): PosixPath {
    $self = new self();
    if ($path === '')
      return $self;
    $self->absolute = $path[0] === '/';
    foreach (split($path, '/') as $name) {
      if ($name !== '')
        $self->names[] = $name;
    }
    return $self;
  }

  private bool $absolute = false;

  public function normalize(): this {
    $ret = parent::normalize();
    // POSIX has a specific policy of "/../../.." being equivelant to "/".
    // On Windows the story is less clear, since there are so many different
    // types of path roots, so it isn't done by default in the parent class.
    if ($ret->absolute) {
      $i = 0;
      $l = \count($ret->names);
      while ($i < $l && $ret->names[$i] === '..')
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
    return $ret;
  }

  public function isAbsolute(): bool {
    return $this->absolute;
  }

  public function hasSameRoot(Path $path): bool {
    return $path instanceof PosixPath && $path->absolute === $this->absolute;
  }
}

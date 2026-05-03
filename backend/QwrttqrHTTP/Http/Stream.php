<?php

namespace QwrttqrHTTP\Http;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{

  private $resource;

  public function __construct($resource)
  {
    if (!is_resource($resource)) {
      throw new \InvalidArgumentException("Stream must be a resource");
    }
    $this->resource = $resource;
  }

  public function __toString(): string
  {
    if (!$this->resource) return '';

    try {
      $this->rewind();
      return stream_get_contents($this->resource);
    } catch (\Throwable $e) {
      return '';
    }
  }

  public function close(): void
  {
    if ($this->resource) {
      fclose($this->resource);
      $this->resource = null;
    }
  }

  public function detach()
  {
    $res = $this->resource;
    $this->resource = null;
    return $res;
  }

  public function getSize(): ?int
  {
    if (!$this->resource) return null;
    $stats = fstat($this->resource);
    return $stats['size'] ?? null;
  }

  public function tell(): int
  {
    if (!$this->resource) {
      throw new \RuntimeException('No resource to tell');
    }

    $pos = ftell($this->resource);
    if ($pos === false) {
      throw new \RuntimeException('Cannot tell position');
    }
    return $pos;
  }

  public function eof(): bool
  {
    return !$this->resource || feof($this->resource);
  }

  public function isSeekable(): bool
  {
    if (!$this->resource) return false;

    $meta = stream_get_meta_data($this->resource);
    return $meta['seekable'];
  }

  public function seek(int $offset, int $whence = SEEK_SET): void
  {
    if (!$this->resource || $this->isSeekable()) {
      throw new \RuntimeException('Resource is not seekable');
    }
    if (fseek($this->resource, $offset, $whence) === -1) {
      throw new \RuntimeException('Seek failed');
    }
  }

  public function rewind(): void
  {
    $this->seek(0);
  }

  public function isWritable(): bool
  {
    if (!$this->resource) return false;

    $mode = $this->getMetadata('mode');

    return strpbrk($mode, 'waxc+') !== false;
  }

  public function write(string $string): int
  {
    if (!$this->isWritable()) {
      throw new \RuntimeException('Stream is not writable');
    }

    $res = fwrite($this->resource, $string);
    if ($res === false) {
      throw new \RuntimeException('Write failed');
    }

    return $res;
  }

  public function isReadable(): bool
  {
    if (!$this->resource) return false;

    $mode = $this->getMetadata('mode');

    return strpbrk($mode, 'r+') !== false;
  }

  public function read(int $length): string
  {
    if (!$this->isReadable()) {
      throw new \RuntimeException('Stream is not readable');
    }

    $data = fread($this->resource, $length);
    if ($data === false) {
      throw new \RuntimeException('Read failed');
    }

    return $data;
  }

  public function getContents(): string
  {
    if (!$this->resource) {
      throw new \RuntimeException('No resource');
    }

    $contents = stream_get_contents($this->resource);

    if ($contents === false) {
      throw new \RuntimeException('Get contents failed');
    }

    return $contents;
  }

  public function getMetadata(?string $key = null)
  {
    if (!$this->resource) return $key ? null : [];

    $meta = stream_get_meta_data($this->resource);

    return $key ? ($meta[$key] ?? null) : $meta;
  }
}
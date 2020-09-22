<?php
declare(strict_types = 1);
namespace App\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * @Annotation
 * @Target({"CLASS","METHOD","PROPERTY"})
 */
class Protocol extends AbstractAnnotation
{

    public $name;

    public function collectClass(string $className): void
    {
        AnnotationCollector::collectClass($className, static::class, $this);
    }

    public function collectMethod(string $className, ?string $target): void
    {
        AnnotationCollector::collectMethod($className, $target, static::class, $this);
    }

    public function collectProperty(string $className, ?string $target): void
    {
        AnnotationCollector::collectProperty($className, $target, static::class, $this);
    }
}



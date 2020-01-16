<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\File\Type;

abstract class SpecializedFileType extends FileType
{
    /**
     * Returns the extension that this file type supports.
     */
    abstract public static function supportsExtension () : string;
}

<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\File\Type;

use Becklyn\AssetsBundle\Asset\Asset;

trait GenericFileHeaderTrait
{
    /**
     * Returns a generic file header.
     */
    private function generateGenericFileDebugInfo (Asset $asset, string $filePath, string $openingComment, string $closingComment) : string
    {
        // keep the blank line at the end, as php strips blank lines at the end of HEREDOC
        return <<<HEADER
{$openingComment}
    Embed asset
        {$asset->getAssetPath()}
    from file 
        {$filePath}
{$closingComment}

HEADER;
    }
}

<?php

namespace Becklyn\AssetsBundle\File\Type;

use Becklyn\AssetsBundle\Asset\Asset;


trait GenericFileHeaderTrait
{
    /**
     * Returns a generic file header
     *
     * @param Asset  $asset
     * @param string $filePath
     * @param string $openingComment
     * @param string $closingComment
     * @return string
     */
    private function generateGenericFileHeader (Asset $asset, string $filePath, string $openingComment, string $closingComment) : string
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

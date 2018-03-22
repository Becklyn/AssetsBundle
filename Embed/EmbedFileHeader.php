<?php

namespace Becklyn\AssetsBundle\Embed;

use Becklyn\AssetsBundle\Asset\Asset;


/**
 * Embeds a debug file header in the embedded files
 */
class EmbedFileHeader
{
    /**
     * Generates the file header
     *
     * @param string $assetPath
     * @param string $filePath
     * @return string
     */
    public function getFileHeader (Asset $asset, string $filePath) : string
    {
        switch ($asset->getFileType())
        {
            case "css":
            case "js":
                return $this->getGenericFileHeader($asset, $filePath, '/*', '*/');

            case "svg":
                return $this->getGenericFileHeader($asset, $filePath, '<!--', '-->');

            default:
                return "";
        }
    }


    /**
     * Returns a generic file header
     *
     * @param Asset  $asset
     * @param string $filePath
     * @param string $openingComment
     * @param string $closingComment
     * @return string
     */
    private function getGenericFileHeader (Asset $asset, string $filePath, string $openingComment, string $closingComment) : string
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

<?php

namespace Becklyn\AssetsBundle\Embed;


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
    public function getFileHeader (string $assetPath, string $filePath) : string
    {
        switch (\pathinfo($assetPath, \PATHINFO_EXTENSION))
        {
            case "css":
            case "js":
                return $this->getGenericFileHeader($assetPath, $filePath, '/*', '*/');

            case "svg":
                return $this->getGenericFileHeader($assetPath, $filePath, '<!--', '-->');

            default:
                return "";
        }
    }


    /**
     * Returns a generic file header
     *
     * @param string $assetPath
     * @param string $filePath
     * @param string $openingComment
     * @param string $closingComment
     * @return string
     */
    private function getGenericFileHeader (string $assetPath, string $filePath, string $openingComment, string $closingComment) : string
    {
        // keep the blank line at the end, as php strips blank lines at the end of HEREDOC
        return <<<HEADER
{$openingComment}
    Embed asset
        {$assetPath}
    from file 
        {$filePath}
{$closingComment}

HEADER;
    }
}

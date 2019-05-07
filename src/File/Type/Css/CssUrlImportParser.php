<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\File\Type\Css;


class CssUrlImportParser
{
    /**
     * Replaces all valid imports with the result of the $contentReplaceFunction.
     * The function receives the cleaned path as argument (without the quotes and the function must not add quotes).
     *
     * @param string   $fileContent
     * @param callable $contentReplaceFunction
     * @return string
     */
    public function replaceValidImports (string $fileContent, callable $contentReplaceFunction) : string
    {
        return \preg_replace_callback(
            '~url\\(\s*(?<path>.*?)\s*\\)~i',
            function (array $matches) use ($contentReplaceFunction)
            {
                return $this->ensureValidImportAndReplace($matches, $contentReplaceFunction);
            },
            $fileContent
        );
    }


    /**
     * Replaces all valid imports with the result of the content replace function
     *
     * @param array    $matches
     * @param callable $contentReplaceFunction
     * @return string
     */
    private function ensureValidImportAndReplace (array $matches, callable $contentReplaceFunction) : string
    {
        $path = $matches["path"];
        $openingQuote = substr($matches["path"], 0, 1);
        $closingQuote = \substr($matches["path"], -1);
        $usedQuotes = "";

        // check if quoted and whether valid quoted
        if ($openingQuote === "'" || $openingQuote === '"')
        {
            if ($openingQuote !== $closingQuote)
            {
                // looks like invalid CSS, as there is a leading quote, but no closing one, so bail
                return $matches[0];
            }

            // strip quotes from path
            $path = \substr($path, 1, -1);
            $usedQuotes = $openingQuote;
        }

        $path = $contentReplaceFunction($path);
        return "url({$usedQuotes}{$path}{$usedQuotes})";
    }
}

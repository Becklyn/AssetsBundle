<?php

namespace Becklyn\AssetsBundle\Data;


/**
 * Interfaces that marks a displayable interface
 */
interface DisplayableAssetInterface
{
    /**
     * Returns the relative URL to display the asset.
     * Can be completed to a full path using the twig `{{ asset() }}` function.
     *
     * @return string
     */
    public function getRelativeUrl () : string;
}

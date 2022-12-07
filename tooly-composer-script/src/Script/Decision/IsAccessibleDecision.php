<?php

namespace Tooly\Script\Decision;

use Tooly\Model\Tool;

/**
 * @package Tooly\Script\Decision
 */
class IsAccessibleDecision extends AbstractDecision
{
    /**
     * @param Tool $tool
     *
     * @return bool
     */
    public function canProceed(Tool $tool)
    {
        if (false === $this->helper->getDownloader()->isAccessible($tool->getUrl())) {
            return $this->fallbackUrlIsAccessible($tool);
        }

        if (empty($tool->getSignUrl())) {
            return true;
        }

        if (false === $this->helper->getDownloader()->isAccessible($tool->getSignUrl())) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return '<error>At least one given URL are not accessible!</error>';
    }

    /**
     * @param Tool $tool
     *
     * @return bool
     */
    private function fallbackUrlIsAccessible(Tool $tool)
    {
        $fallbackUrl = $tool->getFallbackUrl();

        return false === empty($fallbackUrl) && true === $this->helper->getDownloader()->isAccessible($fallbackUrl);
    }
}

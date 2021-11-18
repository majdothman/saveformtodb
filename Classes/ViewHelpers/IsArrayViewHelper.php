<?php

declare(strict_types=1);

namespace Othman\SaveFormToDb\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * Class IsArrayViewHelper
 * check if variable is array or not
 */
class IsArrayViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('object', 'mix', 'Arguments', false, []);
    }

    /**
     * @return string|bool
     */
    public function render()
    {
        $result = false;
        if ($this->hasArgument('object')) {
            return is_array($this->arguments['object']);
        }

        return $result;
    }
}

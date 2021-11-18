<?php

declare(strict_types=1);

namespace Othman\SaveFormToDb\ViewHelpers\Pagination;

use Closure;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\PaginationInterface;
use TYPO3\CMS\Core\Pagination\PaginatorInterface;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class PaginateViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('objects', 'mixed', 'array or queryresult', true);
        $this->registerArgument('as', 'string', 'new variable name', true);
        $this->registerArgument('name', 'string', 'unique identification - will take "as" as fallback', false, '');
        $this->registerArgument('itemsPerPage', 'int', 'items per page', false, 25);
    }

    /**
     * @param array $arguments
     * @param Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        if ($arguments['objects'] === null) {
            return $renderChildrenClosure();
        }
        $templateVariableContainer = $renderingContext->getVariableProvider();
        $templateVariableContainer->add($arguments['as'], [
            'pagination' => self::getPagination($arguments, $renderingContext),
            'paginator' => self::getPaginator($arguments, $renderingContext),
            'name' => self::getName($arguments),
        ]);
        $output = $renderChildrenClosure();
        $templateVariableContainer->remove($arguments['as']);
        return $output;
    }

    /**
     * @param array $arguments
     * @param RenderingContextInterface $renderingContext
     * @return PaginationInterface
     */
    protected static function getPagination(
        array $arguments,
        RenderingContextInterface $renderingContext
    ): PaginationInterface {
        $paginator = self::getPaginator($arguments, $renderingContext);
        return GeneralUtility::makeInstance(SimplePagination::class, $paginator);
    }

    /**
     * @param array $arguments
     * @param RenderingContextInterface $renderingContext
     * @return PaginatorInterface
     */
    protected static function getPaginator(
        array $arguments,
        RenderingContextInterface $renderingContext
    ): PaginatorInterface {
        $paginatorClass = null;
        if (is_array($arguments['objects'])) {
            $paginatorClass = ArrayPaginator::class;
        } elseif (is_a($arguments['objects'], QueryResultInterface::class)) {
            $paginatorClass = QueryResultPaginator::class;
        }
        return GeneralUtility::makeInstance(
            $paginatorClass,
            $arguments['objects'],
            self::getPageNumber($arguments, $renderingContext),
            $arguments['itemsPerPage']
        );
    }

    /**
     * @param array $arguments
     * @param RenderingContextInterface $renderingContext
     * @return int
     */
    protected static function getPageNumber(array $arguments, RenderingContextInterface $renderingContext): int
    {
        $extensionName = $renderingContext->getControllerContext()->getRequest()->getControllerExtensionName();// @phpstan-ignore-line
        $pluginName = $renderingContext->getControllerContext()->getRequest()->getPluginName();// @phpstan-ignore-line
        $extensionService = GeneralUtility::makeInstance(ExtensionService::class);
        $pluginNamespace = $extensionService->getPluginNamespace($extensionName, $pluginName);
        $variables = GeneralUtility::_GP($pluginNamespace);
        if ($variables !== null) {
            if (!empty($variables[self::getName($arguments)]['currentPage'])) {
                return (int)$variables[self::getName($arguments)]['currentPage'];
            }
        }
        return 1;
    }

    /**
     * @param array $arguments
     * @return string
     */
    protected static function getName(array $arguments): string
    {
        return $arguments['name'] ?: $arguments['as'];
    }
}

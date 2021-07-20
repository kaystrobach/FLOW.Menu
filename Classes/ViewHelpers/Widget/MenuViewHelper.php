<?php
namespace KayStrobach\Menu\ViewHelpers\Widget;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Neos\Flow\Annotations as Flow;

class MenuViewHelper extends \Neos\FluidAdaptor\Core\Widget\AbstractWidgetViewHelper {

	/**
	 * @Flow\Inject
	 * @var \KayStrobach\Menu\ViewHelpers\Widget\Controller\MenuController
	 */
	protected $controller;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('menu', 'string', 'MenuIdentifier', 0, 'Default');
        $this->registerArgument('debug', 'boolean', 'Debug or not', 0, false);
        $this->registerArgument('class', 'string', 'additional class', 0, null);
    }

    /**
     * Render this view helper
     *
     * @param string $menu
     * @param bool $debug
     * @param string $class
     * @return string
     * @throws \Neos\Flow\Mvc\Exception\InfiniteLoopException
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     * @throws \Neos\FluidAdaptor\Core\Widget\Exception\InvalidControllerException
     * @throws \Neos\FluidAdaptor\Core\Widget\Exception\MissingControllerException
     */
	public function render() {
		$response = $this->initiateSubRequest();
		if (is_string($response)) {
		    return $response;
        }
		return $response->getContent();
	}
}

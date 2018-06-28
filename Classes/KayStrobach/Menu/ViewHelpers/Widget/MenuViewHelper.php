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

class MenuViewHelper extends \TYPO3\Fluid\Core\Widget\AbstractWidgetViewHelper {

	/**
	 * @Flow\Inject
	 * @var \KayStrobach\Menu\ViewHelpers\Widget\Controller\MenuController
	 */
	protected $controller;

	/**
	 * Render this view helper
	 *
	 * @param string $menu
	 * @param bool $debug
	 * @param string $class
	 * @return string
	 */
	public function render($menu = 'Default', $debug = false, $class = null) {
		$response = $this->initiateSubRequest();
		return $response->getContent();
	}
}

?>
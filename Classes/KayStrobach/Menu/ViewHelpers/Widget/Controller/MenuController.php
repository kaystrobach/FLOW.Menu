<?php

namespace KayStrobach\Menu\ViewHelpers\Widget\Controller;

use TYPO3\Flow\Annotations as Flow;

class MenuController extends \TYPO3\Fluid\Core\Widget\AbstractWidgetController {
	/**
	 * @var \TYPO3\Flow\Configuration\ConfigurationManager
	 * @FLOW\Inject
	 */
	public $configurationManager;

	/**
	 * @FLOW\Inject
	 * @var \TYPO3\Flow\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * stores the items
	 */
	protected $items = array();

	/**
	 * stores the settings
	 */
	protected $settings = array();

	/**
	 *
	 */
	public function initializeAction() {
		//@todo move reading into menuitems repository
		$this->items = $this->configurationManager->getConfiguration(
			\TYPO3\FLOW\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
			'KayStrobach.Menu.Menus.' . $this->widgetConfiguration['menu'] . '.Items'
		);
		$this->settings = $this->configurationManager->getConfiguration(
			\TYPO3\FLOW\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
			'KayStrobach.Menu.Menus.' . $this->widgetConfiguration['menu'] . '.Configuration'
		);
	}

	public function indexAction() {
		$this->aggregateNodes($this->items);
		$this->view->assign('settings', $this->settings);
		$this->view->assign('items',    $this->items);
	}

	protected function aggregateNodes(&$items) {
		foreach($items as $item) {
			if(array_key_exists('aggregator', $item)) {
				$object = $this->objectManager->get($item['aggregator']);
				if(is_a($object, '\\KayStrobach\\Menu\\Domain\\Model\\MenuItemInterface')) {
					$item['items'] = $object->getItems();
				} else {
					throw new \Exception('Sry, but "' . get_class($object) . '" is does not implement "\\KayStrobach\\Menu\\Domain\\Model\\MenuItemInterface", this is mandatory for menu aggregators.');
				}
			}
			if(array_key_exists('items', $item)) {
				$this->aggregateNodes($item['items']);
			}
		}
		ksort($items);
	}
}
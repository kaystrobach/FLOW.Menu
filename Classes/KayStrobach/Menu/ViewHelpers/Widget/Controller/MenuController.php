<?php

namespace KayStrobach\Menu\ViewHelpers\Widget\Controller;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPoint;

class MenuController extends \TYPO3\Fluid\Core\Widget\AbstractWidgetController {
	/**
	 * @var \TYPO3\Flow\Log\SystemLoggerInterface
	 * @Flow\Inject
	 */
	protected $logger;

	/**
	 * @var \TYPO3\Flow\Configuration\ConfigurationManager
	 * @FLOW\Inject
	 */
	public $configurationManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Authorization\AccessDecisionVoterManager
	 */
	protected $accessDecisionVoterManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Package\PackageManagerInterface
	 */
	protected $packageManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Context
	 */
	protected $securityContext;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Object\Proxy\Compiler
	 */
	protected $compiler;

	/**
	 * stores the items
	 */
	protected $items = array();

	/**
	 * stores the settings
	 */
	protected $settings = array();

	/**
	 * @var null|array
	 */
	protected $debug = NULL;
	/**
	 *
	 */
	public function initializeAction() {
		//@todo move reading into menuitems repository
		$itemsFromSettings = $this->configurationManager->getConfiguration(
			\TYPO3\FLOW\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
			'KayStrobach.Menu.Menus.' . $this->widgetConfiguration['menu'] . '.Items'
		);
		$itemsFromMenus = $this->configurationManager->getConfiguration(
			'Menus',
			'KayStrobach.Menu.Menus.' . $this->widgetConfiguration['menu'] . '.Items'
		);
		if(is_array($itemsFromMenus) && is_array($itemsFromSettings)) {
			$this->items = array_merge_recursive($itemsFromSettings, $itemsFromMenus);
		} elseif(is_array($itemsFromMenus)) {
			$this->items = $itemsFromMenus;
		} else {
			$this->items = $itemsFromSettings;
		}

		$this->settings = $this->configurationManager->getConfiguration(
			\TYPO3\FLOW\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
			'KayStrobach.Menu.Menus.' . $this->widgetConfiguration['menu'] . '.Configuration'
		);
		if($this->widgetConfiguration['debug']) {
			$this->debug = $this->configurationManager->getConfiguration(
				\TYPO3\FLOW\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
				'KayStrobach.Menu.Menus'
			);
		}
	}

	public function indexAction() {
		$this->aggregateNodes($this->items);
		$this->view->assign('settings', $this->settings);
		$this->view->assign('items',    $this->items);
		if($this->widgetConfiguration['debug']) {
			$this->view->assign('debug', print_r($this->debug, TRUE));
		}
	}

	/**
	 * @param array $items
	 * @throws \Exception
	 */
	protected function aggregateNodes(&$items) {
		if(is_array($items)) {
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
		} else {
			$items = array();
		}
	}

}
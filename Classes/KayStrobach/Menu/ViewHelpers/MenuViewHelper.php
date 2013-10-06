<?php
namespace KayStrobach\Menu\ViewHelpers;

use TYPO3\Flow\Annotations as Flow;

/**
 *
 * <code title="inline notation">
 * {namespace dev=KayStrobach\DevelopmentTools\Controller}
 * {name -> dev:actionname()}
 * </code>
 *
 * @package KayStrobach\DevelopmentTools\Controller
 */
class MenuViewHelper extends  \TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {
	/**
	 * @var string
	 */
	protected $tagName = 'ul';

	/**
	 * stores the items
	 */
	protected $items = array();

	/**
	 * stores the settings
	 */
	protected $settings = array();

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
	 * The current view, as resolved by resolveView()
	 *
	 * @Flow\Inject
	 * @var \TYPO3\Fluid\View\StandaloneView
	 * @api
	 */
	protected $view = NULL;

	/**
	 *
	 */
	public function initializeArguments() {
		$this->registerUniversalTagAttributes();
		$this->registerArgument('menu', 'string','defines, which type of menu to render', false, 'default');
	}

	/**
	 *
	 * @return string
	 */
	protected  function render() {
		$this->initializeRendering();

		$this->aggregateNodes($this->items);

		$this->view->assign('settings', $this->settings);
		$this->view->assign('items',    $this->items);

		$this->tag->addAttribute('class', 'nav navbar-nav' . $this->arguments['class']);
		$this->tag->setContent($this->view->render());

		return $this->tag->render();
	}

	protected function initializeRendering() {
		$this->items = $this->configurationManager->getConfiguration(
			\TYPO3\FLOW\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
			'KayStrobach.Menu.Menus.' . $this->arguments['menu'] . '.Items'
		);
		$this->settings = $this->configurationManager->getConfiguration(
			\TYPO3\FLOW\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
			'KayStrobach.Menu.Menus.' . $this->arguments['menu'] . '.Configuration'
		);

		$this->view->setTemplatePathAndFilename(
			$this->settings['TemplatePathAndFileName'] ? $this->settings['TemplatePathAndFileName'] : 'resource://KayStrobach.Menu/Private/Templates/ViewHelpers/Menu/Default.html'
		);

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

?>
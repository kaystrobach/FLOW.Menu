<?php

namespace KayStrobach\Menu\ViewHelpers\Widget\Controller;

use KayStrobach\Menu\Domain\Model\MenuItemInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPoint;
use Neos\Flow\Security\Authorization\Privilege\Method\MethodPrivilege;
use Neos\Flow\Security\Authorization\Privilege\Method\MethodPrivilegeSubject;
use Neos\Flow\Security\Exception\AccessDeniedException;


class MenuController extends \Neos\FluidAdaptor\Core\Widget\AbstractWidgetController
{
    /**
     * @var \Neos\Flow\Log\SystemLoggerInterface
     * @Flow\Inject
     */
    protected $logger;

    /**
     * @var \Neos\Flow\Configuration\ConfigurationManager
     * @FLOW\Inject
     */
    public $configurationManager;

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Security\Authorization\PrivilegeManagerInterface
     */
    protected $privilegeManager;

    /**
     * @Flow\Inject
     * @var \Neos\Flow\ObjectManagement\ObjectManagerInterface
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
     * @var null|array
     */
    protected $debug = NULL;

    /**
     *
     */
    public function initializeAction()
    {
        //@todo move reading into menuitems repository
        $itemsFromSettings = $this->configurationManager->getConfiguration(
            \Neos\Flow\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
            'KayStrobach.Menu.Menus.' . $this->widgetConfiguration['menu'] . '.Items'
        );
        $itemsFromMenus = $this->configurationManager->getConfiguration(
            'Menus',
            'KayStrobach.Menu.Menus.' . $this->widgetConfiguration['menu'] . '.Items'
        );

        // @todo optimize merging
        $this->items = $itemsFromSettings;
        if (is_array($itemsFromMenus) && is_array($itemsFromSettings)) {
            $this->items = array_merge_recursive($itemsFromSettings, $itemsFromMenus);
        } elseif (is_array($itemsFromMenus)) {
            $this->items = $itemsFromMenus;
        }

        $this->settings = $this->configurationManager->getConfiguration(
            \Neos\Flow\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
            'KayStrobach.Menu.Menus.' . $this->widgetConfiguration['menu'] . '.Configuration'
        );
        if ($this->widgetConfiguration['debug']) {
            $this->debug = $this->configurationManager->getConfiguration(
                \Neos\Flow\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
                'KayStrobach.Menu.Menus'
            );
        }
    }

    /**
     * @throws \Exception
     */
    public function indexAction()
    {
        $this->aggregateNodes($this->items);
        $this->items = $this->getAllowedNodesAndNonEmptySections($this->items);
        $this->view->assign('settings', $this->settings);
        $this->view->assign('items', $this->items);
        $this->view->assign('config', $this->widgetConfiguration);
        if ($this->widgetConfiguration['debug']) {
            $this->view->assign('debug', print_r($this->debug, TRUE));
        }
    }

    /**
     * @param array $items
     * @throws \Exception
     */
    protected function aggregateNodes(&$items)
    {
        if (is_array($items)) {
            foreach ($items as $key => $item) {
                if (array_key_exists('aggregator', $item)) {
                    $object = $this->objectManager->get($item['aggregator']);
                    if (is_a($object, MenuItemInterface::class)) {
                        $this->logger->log('Dynamic Menu Config ' . json_encode($item), LOG_DEBUG);
                        $item['items'] = $object->getItems($item);
                        $items[$key] = $item;
                        $this->logger->log('Dynamic Menu after aggregation ' . json_encode($item), LOG_DEBUG);
                    } else {
                        throw new \Exception('Sry, but "' . get_class($object) . '" is does not implement "\\KayStrobach\\Menu\\Domain\\Model\\MenuItemInterface", this is mandatory for menu aggregators.');
                    }
                }
                if (array_key_exists('items', $item)) {
                    $this->aggregateNodes($item['items']);
                }
            }
            ksort($items);
        } else {
            $items = array();
        }
    }

    /**
     * @param array $items
     * @return array
     */
    protected function getAllowedNodesAndNonEmptySections($items)
    {
        $thisLevelItems = array();
        foreach ($items as $item) {
            if (array_key_exists('privilegeTarget', $item) && !$this->hasAccessToPriviledgeTarget($item['privilegeTarget'])) {
                continue;
            }
            if (array_key_exists('items', $item)) {
                $subItems = $this->getAllowedNodesAndNonEmptySections($item['items']);
                if ((array_key_exists('section', $item)) && ($item['section'] === 1) && (count($subItems) > 0)) {
                    $item['items'] = $subItems;
                    $thisLevelItems[] = $item;
                }
            } elseif (array_key_exists('url', $item)) {
                $thisLevelItems[] = $item;
            } elseif ((array_key_exists('package', $item)) && (array_key_exists('controller', $item)) && (array_key_exists('action', $item))) {
                if (!isset($item['subpackage'])) {
                    $item['subpackage'] = NULL;
                }
                if ($this->hasAccessToAction($item['package'], $item['subpackage'], $item['controller'], $item['action'])) {
                    $thisLevelItems[] = $item;
                }
            }
        }
        return $thisLevelItems;
    }

    /**
     * Check if we currently have access to the given resource
     *
     * @param $packageKey
     * @param $subpackageKey
     * @param $controllerName
     * @param $actionName
     * @return boolean TRUE if we currently have access to the given action
     */
    protected function hasAccessToAction($packageKey, $subpackageKey, $controllerName, $actionName)
    {
        $actionControllerName = $this->getControllerObjectName($packageKey, $subpackageKey, $controllerName);
        try {
            return $this->privilegeManager->isGranted(
                MethodPrivilege::class,
                new MethodPrivilegeSubject(
                    new JoinPoint(
                        NULL,
                        $actionControllerName,
                        $actionName . 'Action',
                        array()
                    )
                )
            );
        } catch (AccessDeniedException $e) {
            return FALSE;
        }
    }

    protected function hasAccessToPriviledgeTarget($target)
    {
        try {
            return $this->privilegeManager->isPrivilegeTargetGranted(
                $target
            );
        } catch (AccessDeniedException $exception) {
            return false;
        }
    }

    /**
     * @param string $packageKey
     * @param string $subPackageKey
     * @param string $controllerName
     * @return string|null
     */
    protected function getControllerObjectName($packageKey, $subPackageKey, $controllerName)
    {
        $possibleObjectName = str_replace('.', '\\', $packageKey) . '\\';
        if (($subPackageKey !== NULL) && (strlen($subPackageKey) > 0)) {
            $possibleObjectName .= $subPackageKey . '\\';
        }
        $possibleObjectName .= 'Controller\\' . $controllerName . 'Controller';

        $controllerObjectName = $this->objectManager->getCaseSensitiveObjectName($possibleObjectName);
        return ($controllerObjectName !== FALSE) ? $controllerObjectName : NULL;
    }
}

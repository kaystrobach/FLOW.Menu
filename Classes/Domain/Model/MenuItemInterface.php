<?php

namespace KayStrobach\Menu\Domain\Model;

interface MenuItemInterface {
	/**
	 * @param array $parentItem
	 * @return array
	 */
	public function getItems(array $parentItem);
}
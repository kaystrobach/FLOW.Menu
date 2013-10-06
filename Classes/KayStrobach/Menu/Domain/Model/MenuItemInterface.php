<?php

namespace KayStrobach\Menu\Domain\Model;

interface MenuItemInterface {
	/**
	 * @param array $item
	 * @return array
	 */
	public function getItems(array $item);
}
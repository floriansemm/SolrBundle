<?php

namespace FS\SolrBundle\Event\Listener;

/**
 * Description of DefaultEntityHydrateListener
 *
 * @author Volker von Hoesslin <volker.von.hoesslin@empora.com>
 */
class DefaultEntityHydrateListener {

	public function onHydrate(\FS\SolrBundle\Event\EntityHydrate $event) {
		$entityClassName = $event->getMetaInformation()->getClassName();
		$targetEntity = new $entityClassName;

		$reflectionClass = new \ReflectionClass($targetEntity);
		foreach ($event->getDocument() as $property => $value) {
			try {
				$classProperty = $reflectionClass->getProperty($this->removeFieldSuffix($property));
			} catch (\ReflectionException $e) {
				try {
					$classProperty = $reflectionClass->getProperty(
							$this->toCamelCase($this->removeFieldSuffix($property))
					);
				} catch (\ReflectionException $e) {
					continue;
				}
			}

			$classProperty->setAccessible(true);
			$classProperty->setValue($targetEntity, $value);
		}

		$event->setEntity($targetEntity);
	}

	/**
	 * returns the clean fieldname without type-suffix
	 *
	 * eg: title_s => title
	 *
	 * @param string $property
	 * @return string
	 */
	private function removeFieldSuffix($property) {
		if (($pos = strrpos($property, '_')) !== false) {
			return substr($property, 0, $pos);
		}

		return $property;
	}

	/**
	 * returns field name camelcased if it has underlines
	 *
	 * eg: user_id => userId
	 *
	 * @param string $fieldname
	 * @return string
	 */
	private function toCamelCase($fieldname) {
		$words = str_replace('_', ' ', $fieldname);
		$words = ucwords($words);
		$pascalCased = str_replace(' ', '', $words);

		return lcfirst($pascalCased);
	}

}

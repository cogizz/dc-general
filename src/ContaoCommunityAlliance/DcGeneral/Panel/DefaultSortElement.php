<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace ContaoCommunityAlliance\DcGeneral\Panel;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewTemplateInterface;

/**
 * Default implementation of a sort element.
 *
 * @package DcGeneral\Panel
 */
class DefaultSortElement
	extends AbstractElement
	implements SortElementInterface
{
	/**
	 * The default flag to use.
	 *
	 * @var int
	 */
	public $intDefaultFlag;

	/**
	 * The sorting properties including the direction.
	 *
	 * @var array
	 */
	protected $arrSorting = array();

	/**
	 * The selected sorting value.
	 *
	 * @var mixed
	 */
	protected $strSelected;

	/**
	 * Retrieve the persistent value from the input provider.
	 *
	 * @return array
	 */
	protected function getPersistent()
	{
		$arrValue = array();
		if ($this->getInputProvider()->hasPersistentValue('sorting'))
		{
			$arrValue = $this->getInputProvider()->getPersistentValue('sorting');
		}

		if (array_key_exists($this->getEnvironment()->getDataDefinition()->getName(), $arrValue))
		{
			return $arrValue[$this->getEnvironment()->getDataDefinition()->getName()];
		}

		return array();
	}

	/**
	 * Store the persistent value in the input provider.
	 *
	 * @param string $strProperty The name of the property to sort by.
	 *
	 * @return void
	 */
	protected function setPersistent($strProperty)
	{
		$arrValue       = array();
		$definitionName = $this->getEnvironment()->getDataDefinition()->getName();

		if ($this->getInputProvider()->hasPersistentValue('sorting'))
		{
			$arrValue = $this->getInputProvider()->getPersistentValue('sorting');
		}

		if ($strProperty)
		{
			if (!is_array($arrValue[$definitionName]))
			{
				$arrValue[$definitionName] = array();
			}
			$arrValue[$definitionName] = $strProperty;
		}
		else
		{
			unset($arrValue[$definitionName]);
		}

		$this->getInputProvider()->setPersistentValue('sorting', $arrValue);
	}

	/**
	 * Retrieve the sorting flag to use for a property.
	 *
	 * @param string $strProperty The property.
	 *
	 * @return int
	 */
	protected function lookupFlag($strProperty)
	{
		return isset($this->arrSorting[$strProperty])
			? $this->arrSorting[$strProperty]
			: $this->getDefaultFlag();
	}

	/**
	 * Calculate the direction from a flag.
	 *
	 * @param int $intFlag The flag.
	 *
	 * @return string
	 */
	protected function flagToDirection($intFlag)
	{
		return ($intFlag % 2) ? 'ASC' : 'DESC';
	}

	/**
	 * Retrieve the additional sorting properties from the data definition.
	 *
	 * @return array
	 */
	protected function getAdditionalSorting()
	{
		/** @var Contao2BackendViewDefinitionInterface $view */
		$view = $this->getEnvironment()
			->getDataDefinition()
			->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
		$tmp  = $view->getListingConfig()->getDefaultSortingFields();
		if (!$tmp)
		{
			return array();
		}

		$arrReturn = array();
		foreach ($tmp as $strProperty => $strDirection)
		{
			if ($this->getSelected() == $strProperty)
			{
				continue;
			}

			$arrReturn[$strProperty] = $strDirection;
		}
		return $arrReturn;
	}

	/**
	 * {@inheritDoc}
	 */
	public function initialize(ConfigInterface $objConfig, PanelElementInterface $objElement = null)
	{
		if (is_null($objElement))
		{
			$input = $this->getInputProvider();
			$value = null;

			if ($this->getPanel()->getContainer()->updateValues() && $input->hasValue('tl_sort'))
			{
				$value = $input->getValue('tl_sort');

				$this->setPersistent($value);

				$this->setSelected($this->getPersistent());
			}
			else
			{
				$this->setSelected($this->getPersistent());
			}
		}

		$current = $objConfig->getSorting();

		if (!is_array($current))
		{
			$current = array();
		}

		$arrSecondOrder = $this->getAdditionalSorting();

		if (!$this->getSelected())
		{
			if ($arrSecondOrder)
			{
				$filtered = array_intersect(array_keys($arrSecondOrder), $this->getPropertyNames());

				$this->setSelected($filtered[0]);
			}

			// Still nothing selected? - use the first.
			if (!$this->getSelected())
			{
				$all = $this->getPropertyNames();
				$this->setSelected($all[0]);
			}
		}

		if ($this->getSelected())
		{
			$current[$this->getSelected()] = $this->flagToDirection($this->getFlag());
		}

		$objConfig->setSorting($current);
	}

	/**
	 * {@inheritDoc}
	 */
	public function render(ViewTemplateInterface $objTemplate)
	{
		$arrOptions = array();
		foreach ($this->getPropertyNames() as $field)
		{
			$arrLabel = $this->getEnvironment()->getDataDefinition()->getPropertiesDefinition()->getProperty($field)->getLabel();

			$arrOptions[] = array(
				'value'      => specialchars($field),
				'attributes' => ($this->getSelected() == $field) ? ' selected="selected"' : '',
				'content'    => is_array($arrLabel) ? $arrLabel[0] : $arrLabel
			);
		}

		// Sort by option values.
		uksort($arrOptions, 'strcasecmp');

		$objTemplate->options = $arrOptions;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefaultFlag($intFlag)
	{
		$this->intDefaultFlag = $intFlag;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefaultFlag()
	{
		return $this->intDefaultFlag;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addProperty($strPropertyName, $intFlag)
	{
		$this->arrSorting[$strPropertyName] = $intFlag;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPropertyNames()
	{
		return array_keys($this->arrSorting);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setSelected($strPropertyName)
	{
		$this->strSelected = $strPropertyName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSelected()
	{
		return $this->strSelected;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFlag()
	{
		return $this->lookupFlag($this->getSelected());
	}
}

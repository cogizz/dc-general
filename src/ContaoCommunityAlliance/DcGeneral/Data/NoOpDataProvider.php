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

namespace ContaoCommunityAlliance\DcGeneral\Data;

/**
 * Class NoOpDataProvider.
 *
 * Base implementation of an no operational data provider. This data provider is simply a stub endpoint without any
 * logic at all. It is useful as parent class for drivers that only implement a fraction of all DcGeneral features.
 *
 * @package DcGeneral\Data
 */
class NoOpDataProvider implements DataProviderInterface
{
	/**
	 * The configuration data for this instance.
	 *
	 * @var array
	 */
	protected $arrBaseConfig;

	/**
	 * {@inheritdoc}
	 */
	public function setBaseConfig(array $arrConfig)
	{
		$this->arrBaseConfig = $arrConfig;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEmptyConfig()
	{
		return DefaultConfig::init();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEmptyModel()
	{
		$model = new DefaultModel();
		$model->setProviderName(
			isset($this->arrBaseConfig['name'])
				? $this->arrBaseConfig['name']
				: $this->arrBaseConfig['source']
		);
		return $model;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEmptyCollection()
	{
		return new DefaultCollection();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEmptyFilterOptionCollection()
	{
		return new DefaultFilterOptionCollection();
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetch(ConfigInterface $objConfig)
	{
		return $this->getEmptyModel();
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetchAll(ConfigInterface $objConfig)
	{
		return $this->getEmptyCollection();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilterOptions(ConfigInterface $objConfig)
	{
		return $this->getEmptyFilterOptionCollection();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCount(ConfigInterface $objConfig)
	{
		return 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save(ModelInterface $objItem)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function saveEach(CollectionInterface $objItems)
	{
		foreach ($objItems as $objItem)
		{
			$this->save($objItem);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete($item)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function saveVersion(ModelInterface $objModel, $strUsername)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function getVersion($mixID, $mixVersion)
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getVersions($mixID, $blnOnlyActive = false)
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setVersionActive($mixID, $mixVersion)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function getActiveVersion($mixID)
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function resetFallback($strField)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function isUniqueValue($strField, $varNew, $intId = null)
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function fieldExists($strField)
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function sameModels($objModel1 , $objModel2)
	{
		$arrProperties1 = $objModel1->getPropertiesAsArray();
		$arrProperties2 = $objModel2->getPropertiesAsArray();

		$arrKeys = array_merge(array_keys($arrProperties1), array_keys($arrProperties2));
		$arrKeys = array_unique($arrKeys);
		foreach ($arrKeys as $strKey)
		{
			if (!array_key_exists($strKey, $arrProperties1) ||
				!array_key_exists($strKey, $arrProperties2) ||
				$arrProperties1[$strKey] != $arrProperties2[$strKey]
			)
			{
				return false;
			}
		}

		return true;
	}
}

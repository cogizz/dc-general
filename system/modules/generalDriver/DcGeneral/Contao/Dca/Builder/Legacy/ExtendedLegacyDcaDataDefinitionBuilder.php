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

namespace DcGeneral\Contao\Dca\Builder\Legacy;

use ContaoDataProviderInformation;
use DcGeneral\DataDefinition\ContainerInterface;
use DcGeneral\DataDefinition\Section\DefaultDataProviderSection;

/**
 * Build the container config from legacy DCA syntax.
 */
class ExtendedLegacyDcaDataDefinitionBuilder extends DcaReadingDataDefinitionBuilder
{
	const PRIORITY = 100;

	protected $dca;

	/**
	 * {@inheritdoc}
	 */
	public function build(ContainerInterface $container)
	{
		if (!$this->loadDca($container->getName()))
		{
			return;
		}

		// TODO parse $localDca variable into $container
		$this->parseDataProvider($container);
	}

	/**
	 * Read the specified sub path from the dca.
	 *
	 * @param string $path
	 *
	 * @return mixed
	 *
	 * @internal
	 */
	protected function getFromDca($path)
	{
		$chunks = explode('/', trim($path, '/'));
		$dca    = $this->dca;

		while (($chunk = array_shift($chunks)) !== null)
		{
			if (!array_key_exists($chunk, $dca))
			{
				return null;
			}

			$dca = $dca[$chunk];
		}

		return $dca;
	}

	/**
	 * This method parses all data provider related information from Contao legacy data container arrays.
	 *
	 * @param ContainerInterface $container
	 *
	 * @return void
	 */
	protected function parseDataProvider(ContainerInterface $container)
	{
		// parse data provider
		if ($container->hasDataProviderSection())
		{
			$config = $container->getDataProviderSection();
		}
		else
		{
			$config = new DefaultDataProviderSection();
			$container->setDataProviderSection($config);
		}

		// First check if we are using the "new" notation used in DcGeneral 0.9.
		if ($this->getFromDca('dca_config/data_provider') === null)
		{
			return;
		}

		// determine the "local" data provider (if any) and if we know the driver for it.
		if (($defaultProvider = $this->getFromDca('dca_config/data_provider/default')) !== null)
		{
			// Determine the name.
			if (($defaultProviderSource = $this->getFromDca('dca_config/data_provider/default/source')) !== null)
			{
				$providerName = $defaultProviderSource;
			}
			else
			{
				$providerName =$container->getName();
			}

			// Check config if it already exists, if not, add it.
			if (!$config->hasInformation($providerName))
			{
				$providerInformation = new ContaoDataProviderInformation();
				$providerInformation->setName($providerName);
				$config->addInformation($providerInformation);
			}
			else
			{
				$providerInformation = $config->getInformation($providerName);
			}

			if ($providerInformation instanceof ContaoDataProviderInformation)
			{
				// Set versioning information.
				$providerInformation
					->setTableName($providerName)
					->setInitializationData($defaultProvider)
					->isVersioningEnabled((bool)$this->getFromDca('config/enableVersioning'));

				// TODO: add additional information here.

				$container->getBasicSection()->setDataProvider($providerInformation->getName());
			}
		}

		// Determine the parent data provider (if any configured).
		if (($parentProvider = $this->getFromDca('dca_config/data_provider/parent')) !== null)
		{
			// Determine the name.
			if (($parentProviderSource = $this->getFromDca('dca_config/data_provider/parent/source')) !== null)
			{
				$providerName = $parentProviderSource;
			}
			else
			{
				$providerName = $container->getName();
			}

			// Check config if it already exists, if not, add it.
			if (!$config->hasInformation($providerName))
			{
				$providerInformation = new ContaoDataProviderInformation();
				$providerInformation->setName($providerName);
				$config->addInformation($providerInformation);
			}
			else
			{
				$providerInformation = $config->getInformation($providerName);
			}

			if ($providerInformation instanceof ContaoDataProviderInformation)
			{
				$providerInformation
					->setTableName($parentProviderSource)
					->setInitializationData($parentProvider);

				// TODO: add additional information here.

				$container->getBasicSection()->setRootDataProvider($providerInformation->getName());
			}
		}
	}
}

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

namespace ContaoCommunityAlliance\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Event\EventPropagatorInterface;
use ContaoCommunityAlliance\DcGeneral\Panel\PanelContainerInterface;
use ContaoCommunityAlliance\DcGeneral\View\ViewInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use ContaoCommunityAlliance\DcGeneral\Controller\ControllerInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BaseView;

/**
 * Default implementation of an environment.
 *
 * @package DcGeneral
 */
class DefaultEnvironment implements EnvironmentInterface
{
	/**
	 * The controller.
	 *
	 * @var ControllerInterface
	 */
	protected $objController;

	/**
	 * The view in use.
	 *
	 * @var ViewInterface
	 */
	protected $objView;

	/**
	 * The data container definition.
	 *
	 * @var ContainerInterface
	 */
	protected $objDataDefinition;

	/**
	 * The data container definition of the parent table.
	 *
	 * @var ContainerInterface
	 */
	protected $objParentDataDefinition;

	/**
	 * The data container definition of the root table.
	 *
	 * @var ContainerInterface
	 */
	protected $objRootDataDefinition;

	/**
	 * The attached input provider.
	 *
	 * @var InputProviderInterface
	 */
	protected $objInputProvider;

	/**
	 * The registered data providers.
	 *
	 * @var DataProviderInterface[]
	 */
	protected $arrDataProvider;

	/**
	 * The clipboard in use.
	 *
	 * @var ClipboardInterface
	 */
	protected $objClipboard;

	/**
	 * The translator in use.
	 *
	 * @var \ContaoCommunityAlliance\Translator\TranslatorInterface
	 */
	protected $translator;

	/**
	 * The event propagator in use.
	 *
	 * @var EventPropagatorInterface
	 */
	protected $eventPropagator;

	/**
	 * {@inheritdoc}
	 */
	public function setController($objController)
	{
		$this->objController = $objController;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getController()
	{
		return $this->objController;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setView($objView)
	{
		$this->objView = $objView;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getView()
	{
		return $this->objView;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDataDefinition($objDataDefinition)
	{
		$this->objDataDefinition = $objDataDefinition;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDataDefinition()
	{
		return $this->objDataDefinition;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setParentDataDefinition($objParentDataDefinition)
	{
		$this->objParentDataDefinition = $objParentDataDefinition;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParentDataDefinition()
	{
		return $this->objParentDataDefinition;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRootDataDefinition($objRootDataDefinition)
	{
		$this->objRootDataDefinition = $objRootDataDefinition;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRootDataDefinition()
	{
		return $this->objRootDataDefinition;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setInputProvider($objInputProvider)
	{
		$this->objInputProvider = $objInputProvider;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getInputProvider()
	{
		return $this->objInputProvider;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasDataProvider($strSource = null)
	{
		if ($strSource === null)
		{
			$strSource = $this->getDataDefinition()->getBasicDefinition()->getDataProvider();
		}

		return (isset($this->arrDataProvider[$strSource]));
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws DcGeneralRuntimeException when an undefined provider is requested.
	 */
	public function getDataProvider($strSource = null)
	{
		if ($strSource === null)
		{
			$strSource = $this->getDataDefinition()->getBasicDefinition()->getDataProvider();
		}

		if (isset($this->arrDataProvider[$strSource]))
		{
			return $this->arrDataProvider[$strSource];
		}

		throw new DcGeneralRuntimeException(sprintf('Data provider %s not defined', $strSource));
	}

	/**
	 * {@inheritdoc}
	 */
	public function addDataProvider($strSource, $dataProvider)
	{
		// Force removal of an potentially registered data provider to ease sub-classing.
		$this->removeDataProvider($strSource);

		$this->arrDataProvider[$strSource] = $dataProvider;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeDataProvider($strSource)
	{
		if (isset($this->arrDataProvider[$strSource]))
		{
			unset($this->arrDataProvider[$strSource]);
		}

		return $this;
	}

	/**
	 * Retrieve the data provider for the named source.
	 *
	 * If a source name is given, the named data provider will get returned, if not given, the default data provider
	 * will get returned, the default is to be determined via: getEnvironment()->getDataDefinition()->getDataProvider()
	 *
	 * @param string|null $strSource The name of the source.
	 *
	 * @return DataProviderInterface
	 *
	 * @deprecated Use getDataProvider() instead!
	 */
	public function getDataDriver($strSource = null)
	{
		trigger_error(
			__CLASS__ . '::getDataDriver() is deprecated - please use ' . __CLASS__ . '::getDataProvider().',
			E_USER_DEPRECATED
		);
		return $this->getDataProvider($strSource);
	}

	/**
	 * Register a data provider to the environment.
	 *
	 * @param string                $strSource    The name of the source.
	 *
	 * @param DataProviderInterface $dataProvider The data provider instance to register under the given name.
	 *
	 * @return EnvironmentInterface
	 *
	 * @deprecated Use addDataProvider() instead!
	 */
	public function addDataDriver($strSource, $dataProvider)
	{
		trigger_error(
			__CLASS__ . '::addDataDriver() is deprecated - please use ' . __CLASS__ . '::addDataProvider().',
			E_USER_DEPRECATED
		);
		// Force removal of an potentially registered data provider to ease sub-classing.
		$this->addDataProvider($strSource, $dataProvider);

		return $this;
	}

	/**
	 * Remove a data provider from the environment.
	 *
	 * @param string $strSource The name of the source.
	 *
	 * @return EnvironmentInterface
	 *
	 * @deprecated use removeDataProvider() instead!
	 */
	public function removeDataDriver($strSource)
	{
		trigger_error(
			__CLASS__ . '::removeDataDriver() is deprecated - please use ' . __CLASS__ . '::removeDataProvider().',
			E_USER_DEPRECATED
		);
		$this->removeDataProvider($strSource);

		return $this;
	}

	/**
	 * Store the panel container in the view.
	 *
	 * @param PanelContainerInterface $objPanelContainer The panel container.
	 *
	 * @throws DcGeneralInvalidArgumentException When an invalid view instance is stored in the environment.
	 *
	 * @return EnvironmentInterface
	 *
	 * @deprecated use the proper interface in the view!
	 */
	public function setPanelContainer($objPanelContainer)
	{
		trigger_error(
			__CLASS__ . '::setPanelContainer() is deprecated - please use the proper interface in the view.',
			E_USER_DEPRECATED
		);

		if (!(($view = $this->getView()) instanceof BaseView))
		{
			throw new DcGeneralInvalidArgumentException(
				__CLASS__ . '::setPanelContainer() got an invalid view instance passed.'
			);
		}

		/** @var BaseView $view */
		$view->setPanel($objPanelContainer);
		return $this;
	}

	/**
	 * Retrieve the panel container.
	 *
	 * @return PanelContainerInterface
	 *
	 * @throws DcGeneralInvalidArgumentException When an invalid view instance is stored in the environment.
	 *
	 * @deprecated use the proper interface in the view!
	 */
	public function getPanelContainer()
	{
		trigger_error(
			__CLASS__ . '::setPanelContainer() is deprecated - please use the proper interface in the view.',
			E_USER_DEPRECATED
		);

		if (!(($view = $this->getView()) instanceof BaseView))
		{
			throw new DcGeneralInvalidArgumentException(
				__CLASS__ . '::setPanelContainer() got an invalid view instance passed.'
			);
		}

		/** @var BaseView $view */
		return $view->getPanel();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getClipboard()
	{
		return $this->objClipboard;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setClipboard($objClipboard)
	{
		if (is_null($objClipboard))
		{
			unset($this->objClipboard);
		}
		else
		{
			$this->objClipboard = $objClipboard;
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setTranslator(TranslatorInterface $translator)
	{
		$this->translator = $translator;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTranslator()
	{
		return $this->translator;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setEventPropagator($propagator)
	{
		$this->eventPropagator = $propagator;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEventPropagator()
	{
		return $this->eventPropagator;
	}
}

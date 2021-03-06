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

namespace ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralRuntimeException;

/**
 * This class is an abstract base for defining model relationship conditions.
 *
 * It implements a basic condition check.
 *
 * @package DcGeneral\DataDefinition\ModelRelationship
 */
abstract class AbstractCondition
{
	/**
	 * Check if an AND condition filter matches.
	 *
	 * @param ModelInterface $model  The model to check the condition against.
	 *
	 * @param array          $filter The filter rules to be applied.
	 *
	 * @return bool
	 */
	protected static function checkAndFilter($model, $filter)
	{
		foreach ($filter as $child)
		{
			// AND => first false means false.
			if (!self::checkCondition($model, $child))
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Check if an AND condition filter matches.
	 *
	 * @param ModelInterface $model  The model to check the condition against.
	 *
	 * @param array          $filter The filter rules to be applied.
	 *
	 * @return bool
	 */
	protected static function checkOrFilter($model, $filter)
	{
		foreach ($filter as $child)
		{
			// OR => first true means true.
			if (self::checkCondition($model, $child))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Extract a condition value depending if it is a remote value or property.
	 *
	 * @param array          $condition The condition array.
	 *
	 * @param ModelInterface $parent    The parent model.
	 *
	 * @return mixed
	 */
	protected static function getConditionValue($condition, $parent)
	{
		if (isset($condition['remote_value']))
		{
			return $condition['remote_value'];
		}

		return $parent->getProperty($condition['property']);
	}

	/**
	 * Check if the passed filter rules apply to the given model.
	 *
	 * @param ModelInterface $objParentModel The model to check the condition against.
	 *
	 * @param array          $arrFilter      The condition filter to be applied.
	 *
	 * @return bool
	 *
	 * @throws DcGeneralRuntimeException When an unknown filter operation is encountered.
	 */
	public static function checkCondition(ModelInterface $objParentModel, $arrFilter)
	{
		// FIXME: backwards compat - remove when done.
		if (isset($arrFilter['childs']) && is_array($arrFilter['childs']))
		{
			trigger_error('Filter array uses deprecated entry "childs", please use "children" instead.', E_USER_DEPRECATED);
			$arrFilter['children'] = $arrFilter['childs'];
		}

		switch ($arrFilter['operation'])
		{
			case 'AND':
				return self::checkAndFilter($objParentModel, $arrFilter['children']);

			case 'OR':
				return self::checkOrFilter($objParentModel, $arrFilter['children']);

			case '=':
				return (self::getConditionValue($arrFilter, $objParentModel) == $arrFilter['value']);

			case '>':
				return (self::getConditionValue($arrFilter, $objParentModel) > $arrFilter['value']);

			case '<':
				return (self::getConditionValue($arrFilter, $objParentModel) < $arrFilter['value']);

			case 'IN':
				return in_array($objParentModel->getProperty($arrFilter['property']), $arrFilter['value']);

			case 'LIKE':
				throw new DcGeneralRuntimeException('LIKE unsupported as of now.');

			default:
		}

		throw new DcGeneralRuntimeException(
			'Error processing filter array - unknown operation ' . var_export($arrFilter, true),
			1
		);
	}
}

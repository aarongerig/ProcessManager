<?php
/**
 * Process Manager.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2020 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://github.com/dpfaffenbauer/ProcessManager/blob/master/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace ProcessManagerBundle\Model\Executable\Listing;

use Pimcore\Db\ZendCompatibility\Expression;
use ProcessManagerBundle\Model\Executable;
use Pimcore\Model\Listing;

class Dao extends Listing\Dao\AbstractDao
{
    /**
     * @var string
     */
    protected $tableName = 'process_manager_executables';

    /**
     * Get tableName, either for localized or non-localized data.
     *
     * @return string
     */
    protected function getTableName()
    {
        return $this->tableName;
    }

    /**
     * get select query.
     */
    public function getQuery()
    {

        // init
        $select = $this->db->select();

        // create base
        $field = $this->getTableName().'.id';
        $select->from(
            [$this->getTableName()], [
                new Expression(sprintf('SQL_CALC_FOUND_ROWS %s as id', $field, 'o_type')),
            ]
        );

        // add condition
        $this->addConditions($select);

        // group by
        $this->addGroupBy($select);

        // order
        $this->addOrder($select);

        // limit
        $this->addLimit($select);

        return $select;
    }

    /**
     * Loads objects from the database.
     *
     * @return Log[]
     */
    public function load()
    {
        // load id's
        $list = $this->loadIdList();

        $objects = array();
        foreach ($list as $o_id) {
            if ($object = Executable::getById($o_id)) {
                $objects[] = $object;
            }
        }

        $this->model->setObjects($objects);

        return $objects;
    }

    /**
     * Loads a list for the specicifies parameters, returns an array of ids.
     *
     * @return array
     * @throws \Exception
     */
    public function loadIdList()
    {
        try {
            $query = $this->getQuery();
            $objectIds = $this->db->fetchCol($query, $this->model->getConditionVariables());
            $this->totalCount = (int) $this->db->fetchOne('SELECT FOUND_ROWS()');

            return $objectIds;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Count.
     *
     * @return int
     *
     * @throws \Exception
     */
    public function getCount()
    {
        $amount = (int) $this->db->fetchOne('SELECT COUNT(*) as amount FROM '.$this->getTableName().$this->getCondition().$this->getOffsetLimit(), $this->model->getConditionVariables());

        return $amount;
    }

    /**
     * Get Total Count.
     *
     * @return int
     *
     * @throws \Exception
     */
    public function getTotalCount()
    {
        $amount = (int) $this->db->fetchOne('SELECT COUNT(*) as amount FROM '.$this->getTableName().$this->getCondition(), $this->model->getConditionVariables());

        return $amount;
    }
}

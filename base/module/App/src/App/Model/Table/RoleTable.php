<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace App\Model\Table;
use Zend\Db\Sql\Predicate\Expression;

class RoleTable extends TableModel
{
    protected $tableName = 'm_role';
    protected $primary = 'role_no';
    protected $priName = 'role_name';

    protected $role_no;        /* int(11) */
    protected $branch_no;      /* mediumint(9) */
    protected $role_name;      /* varchar(40) */
    protected $update_time;    /* timestamp */

    public function __construct($adapter = null)
    {
        $this->reset();
        parent::__construct($adapter);
    }

   /**
     * reset
     */
    public function reset()
    {
        $this->role_no = "";
        $this->branch_no = "";
        $this->role_name = "";
        $this->update_time = "";
    }

    /**
     * store array for each property
     * @param array $_data
     */
    public function exchanegArray($_data)
    {
        $this->role_no = (int) gv('role_no', $_data);
        $this->branch_no = (int) gv('branch_no', $_data);
        $this->role_name = (string) gv('role_name', $_data);
        $this->update_time = (string) gv('update_time', $_data);
    }

    /**
     * for show list
     * @param array $_where
     * @param array $_order
     * @param int $_page
     * @param int $_num
     * @return \Zend\Paginator\Paginator
     */
    public function getPageList($_where = array(), $_order = array(), $_page = 1, $_num = 25)
    {
        $sql = $this->getSql();
        $select = $sql->select();
        $select->columns(array(
            'role_no',
            'branch_no',
            'role_name',
            'deleted',
            'create_time' => new Expression(
                    'DATE_ADD(' . $this->tableName . '.create_time, INTERVAL ' . TIME_DIFF . ' SECOND)'),
            'update_time' => new Expression(
                    'DATE_ADD(' . $this->tableName . '.update_time, INTERVAL ' . TIME_DIFF . ' SECOND)'),
        ));
        $select->join(array(
            'm1' => 'm_branch'),
            'm1.branch_no = ' . $this->tableName . '.branch_no',
            array(
                'branch_name',
            ),
            'left'
        );
        $select->join(array(
            'm1a' => 'm_user'),
            'm1a.user_no = ' . $this->tableName . '.create_user',
            array(
                'create_user' => 'user_name',
            ),
            'left'
        );
        $select->join(array(
            'm1b' => 'm_user'),
            'm1b.user_no = ' . $this->tableName . '.update_user',
            array(
                'update_user' => 'user_name',
            ),
            'left'
        );

        // where
        $select->where($this->tableName . "." . $this->primary . " <> 0");
        if ($_where) {
            foreach ($_where as $key => $val) {
                switch ($key) {
                    case 'branch_no':
                    case 'deleted':
                        $select->where(array($this->tableName . ".{$key}" => $val));
                        break;
                    case 'role_name':
                        $select->where($this->tableName . ".{$key}"
                                . " like " . $this->adapter->platform->quoteValue( '%' . $val . '%'));
                        break;
                    default:
                        break;
                }
            }
        }

        // order
        if ($_order) {
            list($key, $val) = $_order;
            if (($val == 'asc' || $val == 'desc') && (
                $key == 'role_no'|| $key == 'branch_no'
                    || $key == 'role_name'|| $key == 'create_user'
                    || $key == 'create_time'|| $key == 'update_user'
                    || $key == 'update_time'
            )) {
                $select->order(array(
                    $this->tableName . ".{$key}" => $val
                ));
            }
        }

        $adapter = new \Zend\Paginator\Adapter\DbSelect($select, $sql);
        $paginator = new \Zend\Paginator\Paginator($adapter);

        $paginator->setCurrentPageNumber((int) $_page);
        $paginator->setItemCountPerPage((int) $_num);

        return $paginator;
    }

    /**
     * insert into m_role
     * @param int $_user_no
     * @return int|boolean
     */
    public function insertRecord($_user_no)
    {
        if (!$_user_no) {
            return false;
        }

        // get max value and add 1
        $maxId = $this->getMaxId() + 1;

        $values = array(
            'role_no' => $maxId,
            'branch_no' => $this->branch_no,
            'role_name' => $this->role_name,
            'create_user' => (int) $_user_no,
            'create_time' => new Expression('UTC_TIMESTAMP'),
            'update_user' => (int) $_user_no,
            'update_time' => new Expression('UTC_TIMESTAMP'),
        );

        $ret = $this->insert($values);
        if ($ret) {
            return $maxId;
        }
        else {
            return $ret;
        }
    }

    /**
     * update m_role
     * @param int $_user_no
     * @return int|boolean
     */
    public function updateRecord($_user_no)
    {
        if (!$_user_no) {
            return false;
        }

        $primary = $this->primary;
        $primaryNo =  $this->$primary;

        $values = array(
            'role_no' => $this->role_no,
            'branch_no' => $this->branch_no,
            'role_name' => $this->role_name,
            'update_user' => (int) $_user_no,
            'update_time' => new Expression('UTC_TIMESTAMP'),
        );

        $where = array(
            $this->primary => $primaryNo,
            'update_time' => $this->update_time,
        );

        return $this->update($values, $where);
    }

    /**
     * get user_no and user_name by role
     * @param int $_role_no
     * @return array|boolean
     */
    public function getRoleUserPairs($_role_no)
    {
        if (!$_role_no) {
            return false;
        }

        $sql = "SELECT"
                . " m1.user_no,"
                . " CONCAT(m1.user_name, ' (', m2.position_name, ')') AS user_name"
                . " FROM m_user AS m1"
                . " INNER JOIN m_position AS m2"
                . " ON m1.position_no = m2.position_no"
                . " INNER JOIN r_user_role AS r1"
                . " ON m1.user_no = r1.user_no"
                . " WHERE"
                . " m1.deleted = 0"
                . " AND m1.valid = 1"
                . " AND m2.deleted = 0"
                . " AND r1.role_no = ?";
        $rows = $this->adapter->query($sql, array($_role_no));
        $res = array();
        foreach ($rows as $row) {
            $res[$row->user_no] = $row->user_name;
        }
        return $res;
    }

    /**
     * get user and section by role
     * @param int $_role_no
     * @return object|boolean
     */
    public function getRoleSectionUser($_role_no)
    {
        if (!$_role_no) {
            return false;
        }

        $sql = "SELECT"
                . " m1.user_no, m1.user_name, m1.section_no, m2.section_name"
                . " FROM m_user AS m1"
                . " INNER JOIN m_section AS m2 ON m1.section_no = m2.section_no"
                . " INNER JOIN r_user_role AS r1 ON m1.user_no = r1.user_no"
                . " WHERE"
                . " m1.deleted = 0"
                . " AND m1.valid = 1"
                . " AND m2.deleted = 0"
                . " AND r1.role_no = ?"
                . " ORDER BY m2.section_name ASC, m1.user_name ASC";
        return $this->adapter->query($sql, array($_role_no));
    }

    /**
     * get auth level by role
     * @param int $_role_no
     * @return object|boolean
     */
    public function getRoleCtrl($_role_no)
    {
        if (!$_role_no) {
            return false;
        }

        $sql = "SELECT"
                . " m.controller_no, m.controller_name, r.level"
                . " FROM"
                . " m_controller AS m"
                . " LEFT JOIN r_controller_role AS r"
                . " ON m.controller_no = r.controller_no"
                . " AND r.role_no = ?"
                . " WHERE"
                . " m.deleted = 0"
                . " ORDER BY"
                . " m.controller_no ASC"
            ;
        return $this->adapter->query($sql, array($_role_no));
    }
}

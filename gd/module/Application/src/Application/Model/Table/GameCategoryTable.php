<?php

namespace Application\Model\Table;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class GameCategoryTable extends TableModel
{
    protected $tableName = 'm_game_category';
    protected $primary = 'game_category_id';
    protected $priName = 'game_category_name';

    protected $game_category_id;
    protected $game_category_name; 
    protected $update_time;

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
        $this->game_category_id = "";
        $this->game_category_name = "";
        $this->update_time = "";
    }

    public function exchangeArray($_data)
    {
        $this->game_category_id     = (isset($_data['game_category_id']))     ? $_data['game_category_id']     : null;
        $this->game_category_name   = (isset($_data['game_category_name']))   ? $_data['game_category_name']   : null;
        $this->update_time     = (isset($_data['update_time']))     ? $_data['update_time']     : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
    
    public function getPageList($_where = array(), $_order = array(), $_page = 1, $_num = 25)
    {
        $sql = $this->getSql();
        $select = $sql->select();
        $select->columns(array(
        'game_category_id',
        'game_category_name',
        'update_user_id',
        'update_time'
        ));

        $select->join('m_user', 'm_game_category.update_user_id = m_user.user_id', array('first_name','last_name'));
        $select->order('update_time DESC');
        $adapter = new DbSelect($select, $sql);
        $paginator = new Paginator($adapter);

        return $paginator;
    }
    public function insertRecord($_user_no)
    {
        if (!$_user_no) {
            return false;
        }

        $values = array(
            'game_category_id' => $this->game_category_id,
            'game_category_name' => $this->game_category_name,
            'create_user_id' => (int) $_user_no,
            'create_time' => new Expression('UTC_TIMESTAMP'),
            'update_user_id' => (int) $_user_no,
            'update_time' => new Expression('UTC_TIMESTAMP'),
        );

        return $this->insert($values);
    }
    
    public function getRecord($game_category_id){
        return $this->select(array('game_category_id'=>$game_category_id))->current();
    }
    
     public function updateRecord($_user_no)
    {
        if (!$_user_no) {
            return false;
        }

        $primary = $this->primary;
        $primaryNo =  $this->$primary;

        $values = array(
            'game_category_name' => $this->game_category_name,
            'update_user_id' => (int) $_user_no,
            'update_time' => new Expression('UTC_TIMESTAMP'),
        );

        $where = array(
            $this->primary => $primaryNo,
            'update_time' => $this->update_time,
        );

        return $this->update($values, $where);
    }
}

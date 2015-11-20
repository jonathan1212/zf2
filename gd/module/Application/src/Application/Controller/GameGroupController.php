<?php 

namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Application\Form\GameGroupForm;
use Application\FormFilters\GameGroupFilter;
use Application\Model\Entity\GameGroupEntity;

class GameGroupController extends AbstractActionController{

    public function indexAction()
    {

        $user_info =  $this->authPlugin()->getUserInfo();
        $db = new GameGroupEntity();
        $page = $db->db()->getPageList();
       
        $page->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
	$page->setItemCountPerPage(10);
        
        return new ViewModel(array(
            'userInfo'=>$user_info,
            'rows' => $page,
            ));

    }

    public function addAction(){
        $user_info =  $this->authPlugin()->getUserInfo();

        $form = new GameGroupForm();
        $form->setEditForm('add');

        $db = new GameGroupEntity();

        $filter = new GameGroupFilter();

        $request = $this->getRequest();
        if($request->isPost()){
            $filter->setInputFilter('add');
            $form->setInputFilter($filter->getInputFilter());
            $form->setData($request->getPost());

            $success = false;
            if ($form->isValid()) {

                $success = $db->insertRecord($user_info['user_id'], $form->getData());
            }
            else {
                $data = $form->getInputFilter()->getValues();
            }
            if ($success) {

                return $this->redirect()->toRoute('game-group', array(
                    'action' => 'index',
                ));
            }
        }
        $form->get('submit')->setAttribute('value', 'Save');
        $form->get('reset')->setAttribute('value', 'Reset');

        $values = array(
            'id' => '',
            'action' => 'edit',
            'form' => $form,
            'userInfo'=>$user_info
        );

        return new ViewModel($values);
    }
    
    public function editAction(){

        $user_info =  $this->authPlugin()->getUserInfo();

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            $success = false;
        }

        $db = new GameGroupEntity();

        $filter = new GameGroupFilter();

        try{
            $row = $db->db()->getFetchOne($id);
        }
        catch (\Exception $e) {
            return $this->redirect()->toRoute('game-group', array(
                         'action' => 'index'
            ));
        }

        $form = new GameGroupForm();
        $form->setEditForm();
        $form->bind($row); 

        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $form->setInputFilter($filter->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                
                $form->setInputFilter($filter->getInputFilter());
                $form->setData($request->getPost());

                $success = $form->isValid();

                if ($success) {
                    $success = $db->updateRecord($user_info['user_id'], $form->getData());
                }
                else {
                    $data = $form->getInputFilter()->getValues();
                }

                if (false !== $success) {
                    $this->flashMessenger()->addSuccessMessage("Success");
                    return $this->redirect()->toRoute('game-group', array(
                        'action' => 'index',
                    ));
                }
            }
        }

        $form->get('submit')->setAttribute('value', 'Update');
        $form->get('reset')->setAttribute('value', 'Reset');

        $values = array(
         'id' => $id,
         'action' => 'edit',
         'form' => $form,
         'auth'=>$user_info,
        );

        return new ViewModel($values);
    }
        
}
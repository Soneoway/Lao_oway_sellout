<?php
class Application_Model_Tag extends Zend_Db_Table_Abstract
{
	protected $_name = 'tag';

    public function add($tags, $object_id, $type)
    {
        $QTagObject = new Application_Model_TagObject();

        // remove old record on tag_object
        $where = array();
        $where[] = $QTagObject->getAdapter()->quoteInto('object_id = ?', $object_id);
        $where[] = $QTagObject->getAdapter()->quoteInto('type = ?', $type);

        $QTagObject->delete($where);

        if ($tags and $object_id){

            foreach ($tags as $t){
                $where = $this->getAdapter()->quoteInto('name = ?', $t);
                $existed_tag = $this->fetchRow($where);

                if ($existed_tag){
                    $tag_id = $existed_tag['id'];
                } else {
                    $tag_id = $this->insert(array('name'=>$t));
                }

                $QTagObject->insert(
                    array(
                        'tag_id'    => $tag_id,
                        'object_id' => $object_id, //order sn
                        'type'      => $type,
                    )
                );


            }
        }
    }
}                                                      

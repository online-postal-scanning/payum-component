<?php
declare(strict_types=1);

namespace OLPS\PayumComponent\Storage;

use Payum\Core\Storage\AbstractStorage;
use Payum\Core\Exception\LogicException;
use Payum\Core\Model\Identity;
use PDO;

class PDOStorage extends AbstractStorage
{
    protected array $identityMap;

    public function __construct(
        protected PDO $pdo,
        protected string $table,
        protected string $idkey
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria)
    {
        throw new LogicException('Method is not supported by the storage.');
    }

    /**
     * {@inheritDoc}
     */
    protected function doFind($id)
    {
        if (isset($this->identityMap[$id])) {
            return $this->identityMap[$id];
        }

        $data = $this->_queryModel($id);

        if($data !== false) {
            return $this->identityMap[$id] = $data;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doUpdateModel($model)
    {
        $ro = new \ReflectionObject($model);

        if (false == $ro->hasProperty($this->idkey)) {
            $model->{$this->idkey} = null;
        }

        $rp = new \ReflectionProperty($model, $this->idkey);
        $rp->setAccessible(true);

        $id = $rp->getValue($model);
        if (false == $id) {
            $rp->setValue($model, $id = uniqid());
        }

        $rp->setAccessible(false);

        $this->identityMap[$id] = $model;

        $this->_insertOrUpdateModel($id, $model);
    }

    /**
     * {@inheritDoc}
     */
    protected function doDeleteModel($model)
    {
        $rp = new \ReflectionProperty($model, $this->idkey);
        $rp->setAccessible(true);

        if ($id = $rp->getValue($model)) {
            $this->_deleteModel($id);
            unset($this->identityMap[$id]);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetIdentity($model)
    {
        $rp = new \ReflectionProperty($model, $this->idkey);
        $rp->setAccessible(true);

        if (false == $id = $rp->getValue($model)) {
            throw new LogicException('The model must be persisted before usage of this method');
        }

        return new Identity($id, $model);
    }

    /**
     * @param mixed $id
     *
     * @return object|false
     */
    private function _queryModel($id)
    {
        $res = $this-pdo->query("SELECT `payum-data-model` FROM `$this->table` WHERE `$this->idkey` = '$id' LIMIT 1", \PDO::FETCH_ASSOC);
        $data = $res->fetch(\PDO::FETCH_ASSOC);

        if($data !== false && count($data) === 1){
            return unserialize($data['payum-data-model']);
        }else{
            return false;
        }
    }

    /**
     * @param mixed $id
     * @param object $model
     */
    private function _insertOrUpdateModel($id, $model)
    {
        $data = $this->_queryModel($id);
        $model = serialize($model);

        if($data !== false){
            $this-pdo->exec("UPDATE `$this->table` SET `payum-data-model` = '$model' WHERE `$this->idkey` = '$id'");
        }else{
            $this-pdo->exec("INSERT INTO `$this->table` (`$this->idkey`, `payum-data-model`) VALUES ('$id', '$model')");
        }
    }

    /**
     * @param mixed $id
     */
    private function _deleteModel($id)
    {
        $this-pdo->exec("DELETE FROM `$this->table` WHERE `$this->idkey` = '$id'");
    }
}
<?php
namespace Wsh\LapiBundle\Entity;

trait ApiEntityTrait {
    public function populateFromObject(\stdClass $object) {
        foreach($object as $key => $value) {
            $methodName = 'set'.ucfirst($key);
            if(method_exists($this, $methodName)) {
                $this->$methodName($value);
            }
        }
        return $this;
    }
}
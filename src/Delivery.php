<?php
namespace Entrego;

class Delivery
{
    protected $RESTEntrego;

    final public function setRest($RESTEntrego)
    {

        $this->RESTEntrego = $RESTEntrego;
    }

    final private function getRest()
    {
        if(!is_object($this->RESTEntrego)) $this->RESEntrego = new $this->RESTEntrego;

        return $this->RESTEntrego;
    }

    final public function createNewDelivery(array $delivery)
    {
    	return $this->getRest()->request("delivery", array(
            'method' => "PUT",
            'data' => $delivery
        ));
    }

    final public function deliveryConfirmationRequest(int $idDelivery)
    {
        return $this->getRest()->request("delivery/{$idDelivery}/confirm", array(
            'method' => "POST"
        ));
    }
}

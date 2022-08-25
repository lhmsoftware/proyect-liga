<?php

namespace App\Classes;

use JMS\Serializer\Annotation as Serializer;

/**
 *
 * @Serializer\Discriminator(field = "type",
 * map = {
 * "Person": "App\Entity\Person",
 * "Coach": "App\Entity\Coach",
 * "PLayer": "App\Entity\Player",
 * "Club": "App\Entity\Club", 
 * },
 * groups = { "all", "person","player","coach","club"})
 *
 */
abstract class DataPoly
{

    /**
     * @param type $type
     * @return \self
     */
    public function setType($type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     *
     * @return type
     */
    public function getType()
    {
        return $this->type;
    }

}

<?php

/**
 * Description of PHibernateArray
 *
 * @author Pascal Muenst
 * @copyright Copyright (c) 2012, Actra AG
 * @version 1.0
 */
class PHibernateResultset extends ArrayObject {
    public function __construct($config = array()) {
        // Allow accessing properties as either array keys or object properties:
        parent::__construct($config, ArrayObject::ARRAY_AS_PROPS);
    }
}

?>

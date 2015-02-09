<?php
/**
 *
 * @author Marcel Djaman <marceldjaman@gmail.com>
 * @copyright Copyright (c) 2015 Institut Pasteur de Cote d'Ivoire
 * @license WebCorpor8
 */

namespace Application\Validator;

/**
 * Description of NoRecordExists
 *
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class NoRecordExists extends AbstractRecord
{
    public function isValid($value)
    {
        $valid = true;
        $this->setValue($value);

        $result = $this->query($value);
        if ($result) {
            $valid = false;
            $this->error(self::ERROR_RECORD_FOUND);
        }

        return $valid;
    }
}

{
    //put your code here
}

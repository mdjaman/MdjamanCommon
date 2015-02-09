<?php
/**
 *
 * @author Marcel Djaman <marceldjaman@gmail.com>
 * @copyright Copyright (c) 2015 Institut Pasteur de Cote d'Ivoire
 * @license WebCorpor8
 */

namespace Application\Validator;

/**
 * Description of NoRecordExistsEdit
 *
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
class NoRecordExistsEdit extends NoObjectExists
{
    public function isValid($value, $context = null)
    {
        $valid = true;
        $this->setValue($value);

        /** @var $result \ZfcUser\Entity\UserInterface|null */
        $result = $this->query($value);
        if ($result && $result->getId() != $context['userId']) {
            $valid = false;
            $this->error(self::ERROR_RECORD_FOUND);
        }

        return $valid;
    }
}

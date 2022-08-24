<?php
/**
 * This file is part of the RIRGH project
 * Copyright (c) 2022 RIGRH
 * @author Marcel Djaman <marceldjaman@gmail.com>
 * @author Fabrys Sahiry <fsahiry@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace MdjamanCommon\Utils;

/**
 * Description of ExceptionUtils
 *
 * @author Marcel Djaman <marceldjaman@gmail.com>
 */
abstract class ExceptionUtils
{
    const FORM_VALIDATE_ERR = 'Une erreur est survenue lors de la validation du formulaire %s!';
    const PERSISTENCE_ERR = 'Une erreur est survenue lors de l\'enregistrement de l\'objet %s';
    const OBJECT_NOT_FOUND = 'Objet %s ayant pour identifiant "%s" n\'a pu être trouvé!';
    const OBJECT_PERSISTENCE_ERR = 'Objet %s ayant pour identifiant "%s" n\'a pu être enregistré!';
    const OBJECT_DELETION_ERR = 'Objet %s ayant pour identifiant "%s" n\'a pu être supprimé!';
    const ID_NOT_FOUND_ERR = 'ID "id" est un champ obligatoire!';
    const PERMISSIONS_DENIED = 'Accès à la ressource %s a été refusé à l\'utilisateur %s';
    const SYSTEM_MSG = '%s:%d %s (%d) [%s]\n';


    /**
     * @param \Exception $e
     * @return string
     */
    public static function sysMsg(\Exception $e)
    {
        return sprintf(
            static::SYSTEM_MSG,
            $e->getFile(),
            $e->getLine(),
            $e->getMessage(),
            $e->getCode(),
            get_class($e)
        );
    }

    /**
     * @param array $messages
     * @return string
     */
    public static function filterValidationMessage(array $messages)
    {
        $errMessage = static::FORM_VALIDATE_ERR;
        foreach ($messages as $k => $invalid) {
            $errMessage .= sprintf(' [%s] %s.', $k, array_shift($invalid));
        }
        return $errMessage;
    }
}

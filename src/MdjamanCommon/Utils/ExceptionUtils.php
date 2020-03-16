<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2020 Marcel Djaman
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author Marcel Djaman <marceldjaman@gmail.com>
 * @copyright 2020 Marcel Djaman
 * @license http://www.opensource.org/licenses/MIT MIT License
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

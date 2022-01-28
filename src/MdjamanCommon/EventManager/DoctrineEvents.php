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

namespace MdjamanCommon\EventManager;

/**
 * Class DoctrineEvents
 * @package MdjamanCommon\EventManager
 * @author Marcel DJAMAN <marceldjaman@gmail.com>
 */
final class DoctrineEvents
{
    /**
     * The preRemove event occurs for a given document/entity before the respective
     * DocumentManager remove operation for that document/entity is executed.
     *
     * This is a document/entity lifecycle event.
     *
     * @var string
     */
    const PRE_REMOVE = 'preRemove';

    /**
     * The postRemove event occurs for a document/entity after the document/entity has
     * been deleted. It will be invoked after the database delete operations.
     *
     * This is a document/entity lifecycle event.
     *
     * @var string
     */
    const POST_REMOVE = 'postRemove';

    /**
     * The prePersist event occurs for a given document/entity before the respective
     * DocumentManager persist operation for that document/entity is executed.
     *
     * This is a document/entity lifecycle event.
     *
     * @var string
     */
    const PRE_PERSIST = 'prePersist';

    /**
     * The postPersist event occurs for a document/entity after the document/entity has
     * been made persistent. It will be invoked after the database insert operations.
     * Generated primary key values are available in the postPersist event.
     *
     * This is a document/entity lifecycle event.
     *
     * @var string
     */
    const POST_PERSIST = 'postPersist';

    /**
     * The preUpdate event occurs before the database update operations to
     * document/entity data.
     *
     * This is a document/entity lifecycle event.
     *
     * @var string
     */
    const PRE_UPDATE = 'preUpdate';

    /**
     * The postUpdate event occurs after the database update operations to
     * document/entity data.
     *
     * This is a document/entity lifecycle event.
     *
     * @var string
     */
    const POST_UPDATE = 'postUpdate';

    /**
     * The preLoad event occurs for a document/entity before the document/entity has been loaded
     * into the current DocumentManager from the database or before the refresh operation
     * has been applied to it.
     *
     * This is a document/entity lifecycle event.
     *
     * @var string
     */
    const PRE_LOAD = 'preLoad';

    /**
     * The postLoad event occurs for a document/entity after the document/entity has been loaded
     * into the current DocumentManager from the database or after the refresh operation
     * has been applied to it.
     *
     * Note that the postLoad event occurs for a document/entity before any associations have been
     * initialized. Therefore it is not safe to access associations in a postLoad callback
     * or event handler.
     *
     * This is a document/entity lifecycle event.
     *
     * @var string
     */
    const POST_LOAD = 'postLoad';

    /**
     * The preFlush event occurs when the DocumentManager#flush() operation is invoked,
     * but before any changes to managed documents/entities have been calculated. This event is
     * always raised right after DocumentManager#flush() call.
     */
    const PRE_FLUSH = 'preFlush';

    /**
     * The onFlush event occurs when the DocumentManager#flush() operation is invoked,
     * after any changes to managed documents/entitys have been determined but before any
     * actual database operations are executed. The event is only raised if there is
     * actually something to do for the underlying UnitOfWork. If nothing needs to be done,
     * the onFlush event is not raised.
     *
     * @var string
     */
    const ON_FLUSH = 'onFlush';

    /**
     * The postFlush event occurs when the DocumentManager#flush() operation is invoked and
     * after all actual database operations are executed successfully. The event is only raised if there is
     * actually something to do for the underlying UnitOfWork. If nothing needs to be done,
     * the postFlush event is not raised. The event won't be raised if an error occurs during the
     * flush operation.
     *
     * @var string
     */
    const POST_FLUSH = 'postFlush';

    /**
     * The onClear event occurs when the DocumentManager#clear() operation is invoked,
     * after all references to documents/entities have been removed from the unit of work.
     *
     * @var string
     */
    const ON_CLEAR = 'onClear';
}

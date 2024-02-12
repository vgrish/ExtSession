<?php

namespace ExtSession;

use xPDO\Om\xPDOQueryCondition;
use MODX\Revolution\modX;
use MODX\Revolution\modSession;
use MODX\Revolution\modSessionHandler;
use ExtSession\Model\Session;

class ExtSessionHandler extends modSessionHandler
{

    /**
     * @var modX A reference to the modX instance controlling this session
     * handler.
     * @access public
     */
    public $modx = null;
    /**
     * @var int The maximum lifetime of the session
     */
    public $gcMaxLifetime = 0;
    /**
     * @var int The maximum lifetime of the cache of the session
     */
    public $cacheLifetime = false;

    /**
     * @var modSession The Session object
     */
    private $session = null;

    /**
     * @var int The maximum lifetime of a bot session
     */
    public $botGcMaxLifeTime = 0;

    /**
     * @var int The maximum lifetime of a empty user_agent session
     */
    public $emptyUserAgentGcMaxLifeTime = 0;

    /**
     * @var int The maximum lifetime of a empty user_id session
     */
    public $emptyUserIdGcMaxLifeTime = 0;

    /**
     * @var int The maximum lifetime of a NOT empty user_id session
     */
    public $notEmptyUserIdGcMaxLifeTime = 0;

    /**
     * Creates an instance of a modSessionHandler class.
     *
     * @param modX &$modx A reference to a {@link modX} instance.
     */
    function __construct(modX &$modx)
    {
        parent::__construct($modx);

        $botGcMaxLifeTime = (int)$this->modx->getOption('extsession_bot_gc_maxlifetime');
        $this->botGcMaxLifeTime = $botGcMaxLifeTime > 0 ? $botGcMaxLifeTime : $this->gcMaxLifetime;

        $emptyUserAgentGcMaxLifeTime = (int)$this->modx->getOption('extsession_empty_user_agent_gc_maxlifetime');
        $this->emptyUserAgentGcMaxLifeTime = $emptyUserAgentGcMaxLifeTime > 0 ? $emptyUserAgentGcMaxLifeTime : $this->gcMaxLifetime;

        $emptyUserIdGcMaxLifeTime = (int)$this->modx->getOption('extsession_empty_user_id_agent_gc_maxlifetime');
        $this->emptyUserIdGcMaxLifeTime = $emptyUserIdGcMaxLifeTime > 0 ? $emptyUserIdGcMaxLifeTime : $this->gcMaxLifetime;

        $notEmptyUserIdGcMaxLifeTime = (int)$this->modx->getOption('extsession_not_empty_user_id_gc_maxlifetime');
        $this->notEmptyUserIdGcMaxLifeTime = $notEmptyUserIdGcMaxLifeTime > 0 ? $notEmptyUserIdGcMaxLifeTime : $this->gcMaxLifetime;
    }

    /**
     * Opens the connection for the session handler.
     *
     * @access public
     * @return boolean Always returns true; actual connection is managed by
     * {@link modX}.
     */
    public function open()
    {
        return true;
    }

    /**
     * Closes the connection for the session handler.
     *
     * @access public
     * @return boolean Always returns true; actual connection is managed by
     * {@link modX}
     */
    public function close()
    {
        return true;
    }

    /**
     * Reads a specific {@link modSession} record's data.
     *
     * @access public
     *
     * @param integer $id The pk of the {@link modSession} object.
     *
     * @return string The data read from the {@link modSession} object.
     */
    public function read($id)
    {
        if ($this->_getSession($id)) {
            $data = $this->session->get('data');
        } else {
            $data = '';
        }

        return (string)$data;
    }

    /**
     * Writes data to a specific {@link Session} object.
     *
     * @access public
     *
     * @param integer $id The PK of the modSession object.
     * @param mixed $data The data to write to the session.
     *
     * @return boolean True if successfully written.
     */
    public function write($id, $data)
    {
        $written = false;
        if ($this->_getSession($id, true)) {
            $this->session->set('data', $data);
            if ($this->session->isNew() || $this->session->isDirty('data') || ($this->cacheLifetime > 0 && (time() - strtotime($this->session->get('access'))) > $this->cacheLifetime)) {
                $this->session->set('access', time());
            }
            $written = $this->session->save($this->cacheLifetime);
        }

        return $written;
    }

    /**
     * Destroy a specific {@link Session} record.
     *
     * @access public
     *
     * @param integer $id
     *
     * @return boolean True if the session record was destroyed.
     */
    public function destroy($id)
    {
        if ($this->_getSession($id)) {
            $destroyed = $this->session->remove();
        } else {
            $destroyed = true;
        }

        return $destroyed;
    }

    /**
     * Remove any expired sessions.
     *
     * @access public
     *
     * @param integer $max The amount of time since now to expire any session
     *                     longer than.
     *
     * @return boolean True if session records were removed.
     */
    public function gc($max)
    {
        $c = $this->modx->newQuery(Session::class);
        $alias = $this->modx->getTableName(Session::class);
        $c->setClassAlias($alias);

        $time = time() - $this->botGcMaxLifeTime;
        $c->query['where'][] = new xPDOQueryCondition([
            'sql' => "(`user_bot` = 1 AND `access` < {$time})",
            'conjunction' => "OR",
        ]);

        $time = time() - $this->emptyUserAgentGcMaxLifeTime;
        $c->query['where'][] = new xPDOQueryCondition([
            'sql' => "(`user_agent` = '' AND `access` < {$time})",
            'conjunction' => "OR",
        ]);

        $time = time() - $this->emptyUserIdGcMaxLifeTime;
        $c->query['where'][] = new xPDOQueryCondition([
            'sql' => "(`user_id` < 1 AND `access` < {$time})",
            'conjunction' => "OR",
        ]);

        $time = time() - $this->notEmptyUserIdGcMaxLifeTime;
        $c->query['where'][] = new xPDOQueryCondition([
            'sql' => "(`user_id` > 0 AND `access` < {$time})",
            'conjunction' => "OR",
        ]);

        $c->query['command'] = "DELETE {$alias}";
        $result = ($c->prepare() && $c->stmt->execute());
        if (!$result) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Error clearing the session table. Sql: ' . print_r($c->toSQL(), true));
            $this->modx->log(modX::LOG_LEVEL_ERROR, print_r($c->stmt->errorInfo(), true));
        } else {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'The session was successfully cleared. Sql: ' . print_r($c->toSQL(), true));
        }

        return $result;
    }

    /**
     * Gets the {@link Session} object, respecting the cache flag represented by cacheLifetime.
     *
     * @access protected
     *
     * @param integer $id The PK of the {@link Session} record.
     * @param boolean $autoCreate If true, will automatically create the session
     *                            record if none is found.
     *
     * @return Session|null The modSession instance loaded from db or auto-created; null if it
     * could not be retrieved and/or created.
     */
    protected function _getSession($id, $autoCreate = false)
    {
        $this->session = $this->modx->getObject(Session::class, ['id' => $id], $this->cacheLifetime);
        if ($autoCreate && !is_object($this->session)) {
            $this->session = $this->modx->newObject(Session::class);
            $this->session->set('id', $id);
        }
        if (!($this->session instanceof modSession) || $id != $this->session->get('id') || !$this->session->validate()) {
            if ($this->modx->getSessionState() == modX::SESSION_STATE_INITIALIZED) {
                $this->modx->log(modX::LOG_LEVEL_INFO, 'There was an error retrieving or creating session id: ' . $id);
            }
        }

        return $this->session;
    }

}
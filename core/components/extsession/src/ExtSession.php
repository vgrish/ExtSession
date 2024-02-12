<?php

namespace ExtSession;

use MODX\Revolution\modX;

class ExtSession
{
    /** @var \modX $modx */
    public $modx;

    /**
     * The namespace
     *
     * @var string $namespace
     */
    public $namespace = '';

    /**
     * The version
     *
     * @var string $version
     */
    public $version = '';

    /**
     * The class options
     *
     * @var array $options
     */
    public $options = [];

    /**
     * @param         $n
     * @param array $p
     */
    public function __call($n, array $p)
    {
        echo __METHOD__ . " says: " . $n;
    }

    public function __construct(modX $modx, array $options = [])
    {
        $this->modx = $modx;
        $this->version = ExtSessionConfig::VERSION;
        $this->namespace = ExtSessionConfig::NAMESPACE;

        $this->modx->lexicon->load('extsession:default');
    }

    public function getOption($key, $options = [], $default = null)
    {
        $option = $default;
        if (!empty($key) && is_string($key)) {
            if ($options != null && array_key_exists($key, $options)) {
                $option = $options[$key];
            } elseif (array_key_exists($key, $this->options)) {
                $option = $this->options[$key];
            } elseif (array_key_exists("{$this->namespace}_{$key}", $this->modx->config)) {
                $option = $this->modx->getOption("{$this->namespace}_{$key}");
            }
        }
        return $option;
    }

}

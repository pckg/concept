<?php

namespace Pckg\Concept\Command;

/**
 * Class Stated
 *
 * @package Pckg\Concept\Command
 */
trait Stated
{

    /**
     * @var
     */
    protected $onSuccessCallback;

    /**
     * @var
     */
    protected $onErrorCallback;

    /**
     * @param Closure $onSuccess
     *
     * @return $this
     */
    public function onSuccess(callable $onSuccess)
    {
        $this->onSuccessCallback = $onSuccess;

        return $this;
    }

    /**
     * @param Closure $onError
     *
     * @return $this
     */
    public function onError(callable $onError)
    {
        $this->onErrorCallback = $onError;

        return $this;
    }

    /**
     * @param bool|true $success
     *
     * @return mixed
     */
    public function executeStated($success = true)
    {
        return $success
            ? $this->successful()
            : $this->error();
    }

    /**
     * @return mixed
     */
    public function successful($data = null)
    {
        $func = $this->onSuccessCallback;

        return $func($data);
    }

    /**
     * @return mixed
     */
    public function error($data = null)
    {
        $func = $this->onErrorCallback;
        
        return $func
            ? $func($data)
            : null;
    }

}
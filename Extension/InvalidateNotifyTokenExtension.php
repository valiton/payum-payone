<?php

/*
 * Copyright 2016 Valiton GmbH
 *
 * This file is part of a package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Valiton\Payum\Payone\Extension;

use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Storage\StorageInterface;
use Valiton\Payum\Payone\Action\NotifyAction;
use Valiton\Payum\Payone\Api;

/**
 * Invalidate Notify Token Extension
 *
 * @author     David Fuhr
 */
class InvalidateNotifyTokenExtension implements ExtensionInterface
{
    /**
     * @var StorageInterface
     */
    private $tokenStorage;

    /**
     * Constructor
     */
    public function __construct(StorageInterface $tokenStorage = null)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @var Context $context
     */
    public function onPreExecute(Context $context)
    {
    }

    /**
     * @var Context $context
     */
    public function onExecute(Context $context)
    {
    }

    /**
     * @var Context $context
     */
    public function onPostExecute(Context $context)
    {
        if (null === $this->tokenStorage) {
            return;
        }

        if (!$context->getAction() instanceof NotifyAction) {
            return;
        }
        return;

        /* @var $request \Payum\Core\Request\Notify */
        $request = $context->getRequest();
        $model = $request->getModel();

        if (array_key_exists('completed_status', $model) && $model['completed_status'] === $model[Api::FIELD_STATUS]) {
            $token = $this->tokenStorage->find($model['param']);
            $this->tokenStorage->delete($token);
        }
    }
}

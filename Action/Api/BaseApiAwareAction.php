<?php

/*
 * Copyright 2016 Valiton GmbH
 *
 * This file is part of a package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Valiton\Payum\Payone\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Valiton\Payum\Payone\Api;

/**
 * Base Api Aware Action
 *
 * @author     David Fuhr
 */
abstract class BaseApiAwareAction implements ActionInterface, ApiAwareInterface, LoggerAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof Api) {
            throw new UnsupportedApiException(sprintf('Not supported. Expected %s instance to be set as api.', Api::class));
        }

        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}

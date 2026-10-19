<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

class DownloadLinkProcessor implements ProcessorInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected DownloadableProductProcessor $downloadableProductProcessor,
    ) {}

    /**
     * Process the download link generation request.
     *
     * @param  mixed  $data  Input data from BagistoApi mutation
     * @param  Operation|null  $operation  API Platform operation metadata
     * @param  array  $uriVariables  URI variables from route
     * @param  array  $context  Request context
     * @return array Download link data with token, URL, and expiration
     */
    public function process($data, ?Operation $operation = null, array $uriVariables = [], array $context = [])
    {
        return $this->downloadableProductProcessor->process($data, $operation, $uriVariables, $context);
    }
}

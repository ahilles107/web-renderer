<?php

/**
 * This file is part of the Superdesk Web Publisher Bridge Bundle.
 *
 * Copyright 2016 Sourcefabric z.ú. and contributors.
 *
 * For the full copyright and license information, please see the
 * AUTHORS and LICENSE files distributed with this source code.
 *
 * @copyright 2016 Sourcefabric z.ú.
 * @license http://www.superdesk.org/license
 */
namespace SWP\Bundle\BridgeBundle\Controller;

use League\Pipeline\Pipeline;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class ContentPushController extends FOSRestController
{
    /**
     * Recieves HTTP Push Request's payload which is then processed by the pipeline.
     *
     * @ApiDoc(
     *     resource=true,
     *     description="Adds a new content from HTTP Push",
     *     statusCodes={
     *         201="Returned on successful post."
     *     }
     * )
     * @Route("/api/{version}/content/push/", options={"expose"=true}, defaults={"version"="v1"}, name="swp_api_content_push")
     * @Method("POST")
     */
    public function pushAction(Request $request)
    {
        $pipeline = (new Pipeline())
            ->pipe([$this->get('swp_bridge.transformer.json_to_package'), 'transform'])
            ->pipe(function ($package) {
                $this->get('swp.repository.package')->add($package);

                return $package;
            })
            ->pipe([$this->get('swp_bridge.transformer.package_to_article'), 'transform'])
            ->pipe([$this->get('swp.repository.article'), 'add']);

        $pipeline->process($request->getContent());

        return new JsonResponse(['status' => 'OK'], 201);
    }
}
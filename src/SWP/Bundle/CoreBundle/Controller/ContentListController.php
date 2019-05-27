<?php

/*
 * This file is part of the Superdesk Web Publisher Core Bundle.
 *
 * Copyright 2015 Sourcefabric z.u. and contributors.
 *
 * For the full copyright and license information, please see the
 * AUTHORS and LICENSE files distributed with this source code.
 *
 * @copyright 2015 Sourcefabric z.ú
 * @license http://www.superdesk.org/license
 */

namespace SWP\Bundle\CoreBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\Operation;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;
use SWP\Bundle\ContentListBundle\Form\Type\ContentListType;
use SWP\Bundle\CoreBundle\Model\ArticleInterface;
use SWP\Component\Common\Criteria\Criteria;
use SWP\Component\Common\Pagination\PaginationData;
use SWP\Component\Common\Request\RequestParser;
use SWP\Component\Common\Response\ResourcesListResponse;
use SWP\Component\Common\Response\ResponseContext;
use SWP\Component\Common\Response\SingleResourceResponse;
use SWP\Component\ContentList\ContentListEvents;
use SWP\Component\ContentList\Model\ContentListInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentListController extends Controller
{
    /**
     * @Operation(
     *     tags={""},
     *     summary="Lists all content lists",
     *     @SWG\Parameter(
     *         name="sorting",
     *         in="query",
     *         description="todo",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned on success."
     *     )
     * )
     *
     * @Route("/api/{version}/content/lists/", options={"expose"=true}, defaults={"version"="v2"}, methods={"GET"}, name="swp_api_content_list_lists")
     */
    public function listAction(Request $request)
    {
        $repository = $this->get('swp.repository.content_list');

        $lists = $repository->getPaginatedByCriteria(new Criteria(), $request->query->get('sorting', []), new PaginationData($request));

        return new ResourcesListResponse($lists);
    }

    /**
     * @Operation(
     *     tags={""},
     *     summary="Show single content list",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned on success."
     *     )
     * )
     *
     * @Route("/api/{version}/content/lists/{id}", options={"expose"=true}, defaults={"version"="v2"}, methods={"GET"}, name="swp_api_content_show_lists", requirements={"id"="\d+"})
     */
    public function getAction($id)
    {
        return new SingleResourceResponse($this->findOr404($id));
    }

    /**
     * @Operation(
     *     tags={""},
     *     summary="Create new content list",
     *     @SWG\Parameter(
     *         name="name",
     *         in="body",
     *         description="List name",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Parameter(
     *         name="type",
     *         in="body",
     *         description="List type",
     *         required=false,
     *         type="choice",
     *         @SWG\Schema(type="choice")
     *     ),
     *     @SWG\Parameter(
     *         name="description",
     *         in="body",
     *         description="List description",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Parameter(
     *         name="limit",
     *         in="body",
     *         description="List limit",
     *         required=false,
     *         @SWG\Schema(type="integer")
     *     ),
     *     @SWG\Parameter(
     *         name="cacheLifeTime",
     *         in="body",
     *         description="List cache life time",
     *         required=false,
     *         @SWG\Schema(type="integer")
     *     ),
     *     @SWG\Parameter(
     *         name="filters",
     *         in="body",
     *         description="Content list filters in JSON format.",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="201",
     *         description="Returned on success."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when not valid data."
     *     )
     * )
     *
     * @Route("/api/{version}/content/lists/", options={"expose"=true}, defaults={"version"="v2"}, methods={"POST"}, name="swp_api_content_create_lists")
     */
    public function createAction(Request $request)
    {
        /* @var ContentListInterface $contentList */
        $contentList = $this->get('swp.factory.content_list')->create();
        $form = $form = $this->get('form.factory')->createNamed('', ContentListType::class, $contentList, ['method' => $request->getMethod()]);

        $form->handleRequest($request);
        $this->ensureContentListExists($contentList->getName());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('swp.repository.content_list')->add($contentList);

            return new SingleResourceResponse($contentList, new ResponseContext(201));
        }

        return new SingleResourceResponse($form, new ResponseContext(400));
    }

    /**
     * @Operation(
     *     tags={""},
     *     summary="Update single content list",
     *     @SWG\Parameter(
     *         name="name",
     *         in="body",
     *         description="List name",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Parameter(
     *         name="type",
     *         in="body",
     *         description="List type",
     *         required=false,
     *         @SWG\Schema(type="choice")
     *     ),
     *     @SWG\Parameter(
     *         name="description",
     *         in="body",
     *         description="List description",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Parameter(
     *         name="limit",
     *         in="body",
     *         description="List limit",
     *         required=false,
     *         @SWG\Schema(type="integer")
     *     ),
     *     @SWG\Parameter(
     *         name="cacheLifeTime",
     *         in="body",
     *         description="List cache life time",
     *         required=false,
     *         @SWG\Schema(type="integer")
     *     ),
     *     @SWG\Parameter(
     *         name="filters",
     *         in="body",
     *         description="Content list filters in JSON format.",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned on success."
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when not valid data."
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when not found."
     *     ),
     *     @SWG\Response(
     *         response="409",
     *         description="Returned on conflict."
     *     )
     * )
     *
     * @Route("/api/{version}/content/lists/{id}", options={"expose"=true}, defaults={"version"="v2"}, methods={"PATCH"}, name="swp_api_content_update_lists", requirements={"id"="\d+"})
     */
    public function updateAction(Request $request, $id)
    {
        $objectManager = $this->get('swp.object_manager.content_list');
        /** @var ContentListInterface $contentList */
        $contentList = $this->findOr404($id);
        $filters = $contentList->getFilters();

        $form = $form = $this->get('form.factory')->createNamed('', ContentListType::class, $contentList, ['method' => $request->getMethod()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('event_dispatcher')->dispatch(
                ContentListEvents::LIST_CRITERIA_CHANGE,
                new GenericEvent($contentList, ['filters' => $filters])
            );

            $objectManager->flush();

            return new SingleResourceResponse($contentList);
        }

        return new SingleResourceResponse($form, new ResponseContext(400));
    }

    /**
     * @Operation(
     *     tags={""},
     *     summary="Delete single content list",
     *     @SWG\Response(
     *         response="204",
     *         description="Returned on success."
     *     )
     * )
     *
     * @Route("/api/{version}/content/lists/{id}", options={"expose"=true}, defaults={"version"="v2"}, methods={"DELETE"}, name="swp_api_content_delete_lists", requirements={"id"="\d+"})
     */
    public function deleteAction($id)
    {
        $repository = $this->get('swp.repository.content_list');
        $contentList = $this->findOr404($id);

        $repository->remove($contentList);

        return new SingleResourceResponse(null, new ResponseContext(204));
    }

    /**
     * Link or Unlink resource with Content List.
     *
     * **link or unlink content**:
     *
     *     header name: "link"
     *     header value: "</api/{version}/content/articles/{id}; rel="article">"
     *
     * or with specific position:
     *
     *     header name: "link"
     *     header value: "</api/{version}/content/articles/{id}; rel="article">,<1; rel="position">"
     *
     * @Operation(
     *     tags={""},
     *     summary="Link or Unlink resource with Content List.",
     *     @SWG\Response(
     *         response="201",
     *         description="Returned when successful"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when resource not found"
     *     ),
     *     @SWG\Response(
     *         response="409",
     *         description="Returned when the link already exists"
     *     )
     * )
     *
     *
     * @Route("/api/{version}/content/lists/{id}", requirements={"id"="\w+"}, defaults={"version"="v2"}, methods={"LINK","UNLINK"}, name="swp_api_content_list_link_unlink")
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws NotFoundHttpException
     * @throws \Exception
     *
     * @return SingleResourceResponse
     */
    public function linkUnlinkToContentListAction(Request $request, $id)
    {
        $objectManager = $this->get('swp.object_manager.content_list');
        /** @var ContentListInterface $contentList */
        $contentList = $this->findOr404($id);

        $matched = false;
        foreach ($request->attributes->get('links', []) as $key => $objectArray) {
            if (!is_array($objectArray)) {
                continue;
            }

            $object = $objectArray['object'];
            if ($object instanceof \Exception) {
                throw $object;
            }

            if ($object instanceof ArticleInterface) {
                $contentListItem = $this->get('swp.repository.content_list_item')
                    ->findOneBy([
                        'contentList' => $contentList,
                        'content' => $object,
                    ]);

                if ('LINK' === $request->getMethod()) {
                    $position = 0;
                    if (count($notConvertedLinks = RequestParser::getNotConvertedLinks($request->attributes->get('links'))) > 0) {
                        foreach ($notConvertedLinks as $link) {
                            if (isset($link['resourceType']) && 'position' == $link['resourceType']) {
                                $position = $link['resource'];
                            }
                        }
                    }

                    if (false === $position && $contentListItem) {
                        throw new ConflictHttpException('This content is already linked to Content List');
                    }

                    if (!$contentListItem) {
                        $contentListItem = $this->get('swp.service.content_list')->addArticleToContentList($contentList, $object, $position);
                        $objectManager->persist($contentListItem);
                    } else {
                        $contentListItem->setPosition($position);
                    }

                    $objectManager->flush();
                } elseif ('UNLINK' === $request->getMethod()) {
                    if ($contentListItem->getContentList() !== $contentList) {
                        throw new ConflictHttpException('Content is not linked to content list');
                    }
                    $objectManager->remove($contentListItem);
                }

                $matched = true;

                break;
            }
        }
        if (false === $matched) {
            throw new NotFoundHttpException('Any supported link object was not found');
        }

        $objectManager->flush();

        return new SingleResourceResponse($contentList, new ResponseContext(201));
    }

    private function findOr404($id)
    {
        if (null === $list = $this->get('swp.repository.content_list')->findOneById($id)) {
            throw new NotFoundHttpException(sprintf('Content list with id "%s" was not found.', $id));
        }

        return $list;
    }

    private function ensureContentListExists($name)
    {
        if (null !== $this->get('swp.repository.content_list')->findOneByName($name)) {
            throw new ConflictHttpException(sprintf('Content list named "%s" already exists!', $name));
        }
    }
}

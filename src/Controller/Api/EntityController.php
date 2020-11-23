<?php

namespace App\Controller\Api;

use App\Form\EntityType;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as FOSAnnotations;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Entity\Entity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\Security;


/**
 * @FOSAnnotations\RouteResource("Entity", pluralize=false)
 */
class EntityController extends AbstractFOSRestController
{

    /**
     * Get all entities.
     *
     * @SWG\Tag(name="Entity")
     *
     * @SWG\Response(
     *     response=200,
     *     description="An array of all the entities.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Entity::class, groups={"read"}))
     *     )
     * )
     *
     * @Security(name="client_credentials")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cgetAction()
    {
        $entities = $this->getDoctrine()->getRepository(Entity::class)->findAll();
        $view     = View::create()->setData($entities);
        $view->getContext()->setGroups(['read']);

        return $this->getViewHandler()->handle($view);
    }

    /**
     * Get a single entity.
     *
     * @SWG\Tag(name="Entity")
     *
     * @SWG\Response(
     *     response=200,
     *     description="A entity object.",
     *     @Model(type=Entity::class, groups={"read"})
     * )
     * @SWG\Parameter(
     *     name="code",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="A entity code"
     * )
     *
     * @Security(name="client_credentials")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction($code)
    {
        $entity = $this->getDoctrine()->getRepository(Entity::class)->findOneBy(['code' => $code]);
        if ($entity === null) {
            throw new NotFoundHttpException(sprintf("Resource with code '%s' could not be found.", $code));
        }

        $view = View::create()->setData($entity);
        $view->getContext()->setGroups(['read']);

        return $this->getViewHandler()->handle($view);
    }

    /**
     * Update a single entity.
     *
     * @SWG\Tag(name="Entity")
     *
     * @SWG\Response(
     *     response=200,
     *     description="A entity object.",
     *     @Model(type=Entity::class, groups={"read"})
     * )
     * @SWG\Parameter(
     *     name="code",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="A entity code"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref=@Model(type=Entity::class, groups={"update"})),
     *     required=true,
     *     description="To be updated entity object."
     * )
     *
     * @Security(name="client_credentials")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putAction($code)
    {
        $request = Request::createFromGlobals();
        $entity  = $this->getDoctrine()->getRepository(Entity::class)->findOneBy(['code' => $code]);
        if ($entity === null) {
            throw new NotFoundHttpException(sprintf("Resource with code '%s' could not be found.", $code));
        }

        $form = $this->createForm(EntityType::class, $entity);
        $data = json_decode($request->getContent(), true);
        try {
            $form->submit($data, false);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($entity);
            $this->getDoctrine()->getManager()->flush();
            $view = View::create()->setData($entity);
            $view->getContext()->setGroups(['read']);

            return $this->getViewHandler()->handle($view);
        }

        $view = View::create()
                    ->setStatusCode(Response::HTTP_BAD_REQUEST)
                    ->setData([
                        'form' => $form,
                    ]);

        return $this->getViewHandler()->handle($view);
    }

    /**
     * Create a single entity.
     *
     * @SWG\Tag(name="Entity")
     *
     * @SWG\Response(
     *     response=200,
     *     description="A entity object.",
     *     @Model(type=Entity::class, groups={"read", "secret"})
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref=@Model(type=Entity::class, groups={"create"})),
     *     required=true,
     *     description="Created entity object."
     * )
     *
     * @Security(name="client_credentials")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postAction(Request $request)
    {
        $entity = new Entity();
        $form   = $this->createForm(EntityType::class, $entity);
        $data   = json_decode($request->getContent(), true);
        try {
            $form->submit($data);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($form->isValid()) {
            // Check for duplicates
            if ($this->getDoctrine()->getRepository(Entity::class)->findOneBy(['code' => $entity->getCode()]) !== null) {
                throw new BadRequestHttpException(sprintf("Duplicate resource code '%s'.", $form->getData()->getCode()));
            }

            // Save new entity and return it
            $this->getDoctrine()->getManager()->persist($entity);
            $this->getDoctrine()->getManager()->flush();

            $view = View::create()->setData($entity);
            $view->getContext()->setGroups(['read', 'secret']);

            return $this->getViewHandler()->handle($view);
        }

        $view = View::create()
                    ->setStatusCode(Response::HTTP_BAD_REQUEST)
                    ->setData([
                        'form' => $form,
                    ]);

        return $this->getViewHandler()->handle($view);
    }

    /**
     * Delete a single entity.
     *
     * @SWG\Tag(name="Entity")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Successful operation."
     * )
     * @SWG\Parameter(
     *     name="code",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="A entity code"
     * )
     *
     * @Security(name="client_credentials")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($code)
    {
        $entity = $this->getDoctrine()->getRepository(Entity::class)->findOneBy(['code' => $code]);

        if ($entity === null) {
            throw new NotFoundHttpException(sprintf("Resource with code '%s' could not be found.", $code));
        }

        $this->getDoctrine()->getManager()->remove($entity);
        $this->getDoctrine()->getManager()->flush();

        $view = View::create();

        return $this->getViewHandler()->handle($view);
    }
}

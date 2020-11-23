<?php

namespace App\Controller\Api;

use App\Form\FamilyType;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as FOSAnnotations;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Entity\Family;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\Security;


/**
 * @FOSAnnotations\RouteResource("Family", pluralize=false)
 */
class FamilyController extends AbstractFOSRestController
{

    /**
     * Get all families.
     *
     * @SWG\Tag(name="Family")
     *
     * @SWG\Response(
     *     response=200,
     *     description="An array of all the families.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Family::class, groups={"read"}))
     *     )
     * )
     *
     * @Security(name="client_credentials")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cgetAction()
    {
        $families = $this->getDoctrine()->getRepository(Family::class)->findAll();
        $view     = View::create()->setData($families);
        $view->getContext()->setGroups(['read']);

        return $this->getViewHandler()->handle($view);
    }

    /**
     * Get a single family.
     *
     * @SWG\Tag(name="Family")
     *
     * @SWG\Response(
     *     response=200,
     *     description="A family object.",
     *     @Model(type=Family::class, groups={"read"})
     * )
     * @SWG\Parameter(
     *     name="code",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="A family code"
     * )
     *
     * @Security(name="client_credentials")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction($code)
    {
        $family = $this->getDoctrine()->getRepository(Family::class)->findOneBy(['code' => $code]);
        if ($family === null) {
            throw new NotFoundHttpException(sprintf("Resource with code '%s' could not be found.", $code));
        }

        $view = View::create()->setData($family);
        $view->getContext()->setGroups(['read']);

        return $this->getViewHandler()->handle($view);
    }

    /**
     * Update a single family.
     *
     * @SWG\Tag(name="Family")
     *
     * @SWG\Response(
     *     response=200,
     *     description="A family object.",
     *     @Model(type=Family::class, groups={"read"})
     * )
     * @SWG\Parameter(
     *     name="code",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="A family code"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref=@Model(type=Family::class, groups={"update"})),
     *     required=true,
     *     description="To be updated family object."
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
        $family  = $this->getDoctrine()->getRepository(Family::class)->findOneBy(['code' => $code]);
        if ($family === null) {
            throw new NotFoundHttpException(sprintf("Resource with code '%s' could not be found.", $code));
        }

        $form = $this->createForm(FamilyType::class, $family);
        $data = json_decode($request->getContent(), true);
        try {
            $form->submit($data, false);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($family);
            $this->getDoctrine()->getManager()->flush();
            $view = View::create()->setData($family);
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
     * Create a single family.
     *
     * @SWG\Tag(name="Family")
     *
     * @SWG\Response(
     *     response=200,
     *     description="A family object.",
     *     @Model(type=Family::class, groups={"read"})
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref=@Model(type=Family::class, groups={"create"})),
     *     required=true,
     *     description="Created family object."
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
        $family = new Family();
        $form   = $this->createForm(FamilyType::class, $family);
        $data   = json_decode($request->getContent(), true);
        try {
            $form->submit($data);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($form->isValid()) {
            // Check for duplicates
            if ($this->getDoctrine()->getRepository(Family::class)->findOneBy(['code' => $family->getCode()]) !== null) {
                throw new BadRequestHttpException(sprintf("Duplicate resource code '%s'.", $form->getData()->getCode()));
            }

            // Save new family and return it
            $this->getDoctrine()->getManager()->persist($family);
            $this->getDoctrine()->getManager()->flush();

            $view = View::create()->setData($family);
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
     * Delete a single family.
     *
     * @SWG\Tag(name="Family")
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
     *     description="A family code"
     * )
     *
     * @Security(name="client_credentials")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($code)
    {
        $family = $this->getDoctrine()->getRepository(Family::class)->findOneBy(['code' => $code]);

        if ($family === null) {
            throw new NotFoundHttpException(sprintf("Resource with code '%s' could not be found.", $code));
        }

        $this->getDoctrine()->getManager()->remove($family);
        $this->getDoctrine()->getManager()->flush();

        $view = View::create();

        return $this->getViewHandler()->handle($view);
    }
}

<?php

namespace App\Controller\Api;

use App\Form\StructureType;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as FOSAnnotations;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Entity\Structure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\Security;


/**
 * @FOSAnnotations\RouteResource("Structure", pluralize=false)
 */
class StructureController extends AbstractFOSRestController
{

    /**
     * Get all structures.
     *
     * @SWG\Tag(name="Structure")
     *
     * @SWG\Response(
     *     response=200,
     *     description="An array of all the structures.",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Structure::class, groups={"read"}))
     *     )
     * )
     *
     * @Security(name="client_credentials")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cgetAction()
    {
        $structures = $this->getDoctrine()->getRepository(Structure::class)->findAll();
        $view     = View::create()->setData($structures);
        $view->getContext()->setGroups(['read']);

        return $this->getViewHandler()->handle($view);
    }

    /**
     * Get a single structure.
     *
     * @SWG\Tag(name="Structure")
     *
     * @SWG\Response(
     *     response=200,
     *     description="A structure object.",
     *     @Model(type=Structure::class, groups={"read"})
     * )
     * @SWG\Parameter(
     *     name="code",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="A structure code"
     * )
     *
     * @Security(name="client_credentials")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction($code)
    {
        $structure = $this->getDoctrine()->getRepository(Structure::class)->findOneBy(['code' => $code]);
        if ($structure === null) {
            throw new NotFoundHttpException(sprintf("Resource with code '%s' could not be found.", $code));
        }

        $view = View::create()->setData($structure);
        $view->getContext()->setGroups(['read']);

        return $this->getViewHandler()->handle($view);
    }

    /**
     * Update a single structure.
     *
     * @SWG\Tag(name="Structure")
     *
     * @SWG\Response(
     *     response=200,
     *     description="A structure object.",
     *     @Model(type=Structure::class, groups={"read"})
     * )
     * @SWG\Parameter(
     *     name="code",
     *     in="path",
     *     type="string",
     *     required=true,
     *     description="A structure code"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref=@Model(type=Structure::class, groups={"update"})),
     *     required=true,
     *     description="To be updated structure object."
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
        $structure  = $this->getDoctrine()->getRepository(Structure::class)->findOneBy(['code' => $code]);
        if ($structure === null) {
            throw new NotFoundHttpException(sprintf("Resource with code '%s' could not be found.", $code));
        }

        $form = $this->createForm(StructureType::class, $structure);
        $data = json_decode($request->getContent(), true);
        try {
            $form->submit($data, false);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($structure);
            $this->getDoctrine()->getManager()->flush();
            $view = View::create()->setData($structure);
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
     * Create a single structure.
     *
     * @SWG\Tag(name="Structure")
     *
     * @SWG\Response(
     *     response=200,
     *     description="A structure object.",
     *     @Model(type=Structure::class, groups={"read"})
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(ref=@Model(type=Structure::class, groups={"create"})),
     *     required=true,
     *     description="Created structure object."
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
        $structure = new Structure();
        $form   = $this->createForm(StructureType::class, $structure);
        $data   = json_decode($request->getContent(), true);
        try {
            $form->submit($data);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($form->isValid()) {
            // Check for duplicates
            if ($this->getDoctrine()->getRepository(Structure::class)->findOneBy(['code' => $structure->getCode()]) !== null) {
                throw new BadRequestHttpException(sprintf("Duplicate resource code '%s'.", $form->getData()->getCode()));
            }

            // Save new structure and return it
            $this->getDoctrine()->getManager()->persist($structure);
            $this->getDoctrine()->getManager()->flush();

            $view = View::create()->setData($structure);
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
     * Delete a single structure.
     *
     * @SWG\Tag(name="Structure")
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
     *     description="A structure code"
     * )
     *
     * @Security(name="client_credentials")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($code)
    {
        $structure = $this->getDoctrine()->getRepository(Structure::class)->findOneBy(['code' => $code]);

        if ($structure === null) {
            throw new NotFoundHttpException(sprintf("Resource with code '%s' could not be found.", $code));
        }

        $this->getDoctrine()->getManager()->remove($structure);
        $this->getDoctrine()->getManager()->flush();

        $view = View::create();

        return $this->getViewHandler()->handle($view);
    }
}

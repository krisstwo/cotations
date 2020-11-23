<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Controller\Web;


use App\Service\OptionsResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SearchController extends AbstractController
{
    protected $optionsResolver;

    public function __construct(OptionsResolverInterface $optionsResolver)
    {
        $this->optionsResolver = $optionsResolver;
    }

    /**
     * @Route("/", name="homepage", defaults={"title": "EasyPrice"})
     */
    public function index(Request $request)
    {
        $options = $this->optionsResolver->resolve([], [
            'entity' => $request->get('entity'),
            'family' => $request->get('family'),
            'structure' => $request->get('structure')
        ]);

        return $this->render('web/search/index.html.twig', [
            'api_base_url' => $this->generateUrl('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL) . 'api/v1',
            'access_token' => $request->get('access_token'),
            'options' => $options
        ]);
    }
}
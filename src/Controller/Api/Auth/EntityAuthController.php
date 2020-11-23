<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Controller\Api\Auth;


use App\Entity\OAuth2\Client;
use App\Service\Hash\HashInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSAnnotations;
use FOS\RestBundle\View\View;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EntityAuthController extends AbstractFOSRestController
{
    const OAUTH2_CLIENT_LABEL = 'ENTITY-AUTH-DEFAULT';

    /**
     * @var OAuth2
     */
    private $oauth2Server;

    /**
     * @var ClientManagerInterface
     */
    private $clientManager;

    /**
     * @var Client
     */
    private $oauth2Client;

    /**
     * @var HashInterface
     */
    private $hash;

    /**
     * EntityAuthController constructor.
     *
     * @param OAuth2 $oauth2Server
     * @param ClientManagerInterface $clientManager
     * @param HashInterface $hash
     *
     * @throws \Exception
     */
    public function __construct(OAuth2 $oauth2Server, ClientManagerInterface $clientManager, HashInterface $hash)
    {
        $this->oauth2Server  = $oauth2Server;
        $this->clientManager = $clientManager;
        $this->hash          = $hash;

        $this->oauth2Client = $this->clientManager->findClientBy(['label' => self::OAUTH2_CLIENT_LABEL]);

        if ( ! $this->oauth2Client) {
            $this->oauth2Client = $this->clientManager->createClient();
            $this->oauth2Client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_USER_CREDENTIALS]);
            $this->oauth2Client->setLabel(static::OAUTH2_CLIENT_LABEL);
            $this->clientManager->updateClient($this->oauth2Client);
        }
    }

    /**
     * Entity authentication for api use
     *
     * @FOSAnnotations\Post("/entity")
     *
     * @SWG\Tag(name="Authentication")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Granted access token information.",
     *     @SWG\Schema(type="object",
     *         @SWG\Property(property="token_type", type="string"),
     *         @SWG\Property(property="access_token", type="string"),
     *         @SWG\Property(property="expires_in", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @SWG\Schema(type="object",
     *         @SWG\Property(property="entity", type="string"),
     *         @SWG\Property(property="secret", type="string"),
     *         @SWG\Property(property="hash", type="string")
     *     ),
     *     required=true,
     *     description="Entity credentials."
     * )
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function authAction(Request $request)
    {
        $inputData = $request->request->all();

        // Check hash
        if (empty($inputData['date']) || date_create_from_format('Y-m-d\TH:i:sP', $inputData['date']) === null) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Date data mandatory');
        }

        try {
            if (
                empty($inputData['hash'])
                || ! $this->hash->verifyHash($inputData['hash'], array_diff_key($inputData, ['hash' => null]))
            ) {
                throw new HttpException(Response::HTTP_BAD_REQUEST,
                    'Invalid hash'); //TODO: must return a response like oauth2
            }
        } catch (\Exception $e) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR,
                'Invalid hash');
        }

        // Submit credentials to OAuth2 as is and forward response (remove refresh token as not used)
        $oauth2Data['client_id']     = $this->oauth2Client->getPublicId();
        $oauth2Data['client_secret'] = $this->oauth2Client->getSecret();
        $oauth2Data['grant_type']    = OAuth2::GRANT_TYPE_USER_CREDENTIALS;
        $oauth2Data['username']      = $inputData['entity'];
        $oauth2Data['password']      = $inputData['secret'];

        $oauth2Request = Request::create($this->generateUrl('fos_oauth_server_token'), 'POST', $oauth2Data);
        try {
            $oauth2Response     = $this->oauth2Server->grantAccessToken($oauth2Request);
            $oauth2ResponseData = json_decode($oauth2Response->getContent(), true);
            unset($oauth2ResponseData['refresh_token']);

            // Send response
            $view = View::create()->setData($oauth2ResponseData);

            return $this->getViewHandler()->handle($view);
        } catch (OAuth2ServerException $e) {
            return $e->getHttpResponse();
        }
    }
}
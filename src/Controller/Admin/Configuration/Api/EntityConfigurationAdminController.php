<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace App\Controller\Admin\Configuration\Api;

use App\Controller\Api\Auth\EntityAuthController;
use App\Entity\Entity;
use App\Entity\OAuth2\Client;
use App\Service\Hash\HashInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Admin\Api\ConfigurationFormBuilderInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use FOS\OAuthServerBundle\Util\Random;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class EntityConfigurationAdminController extends ConfigurationAdminController
{
    /**
     * @var HashInterface
     */
    protected $hash;

    /**
     * @var Client
     */
    protected $oauth2Client;

    /**
     * @var OAuth2
     */
    protected $oauth2Server;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * GenerateWebUrlCommand constructor.
     *
     * @param HashInterface $hash
     * @param EntityManagerInterface $em
     * @param ClientManagerInterface $clientManager
     * @param OAuth2 $oauth2Server
     */
    public function __construct(
        ConfigurationFormBuilderInterface $configurationFormBuilder, 
        EntityManagerInterface $em,
        HashInterface $hash,
        ClientManagerInterface $clientManager,
        OAuth2 $oauth2Server,
        RouterInterface $router
    ) {
        parent::__construct($configurationFormBuilder, $em);
        $this->hash         = $hash;
        $this->oauth2Server = $oauth2Server;
        $this->router       = $router;

        $this->oauth2Client = $clientManager->findClientBy(['label' => EntityAuthController::OAUTH2_CLIENT_LABEL]);

        if ( ! $this->oauth2Client) {
            $this->oauth2Client = $clientManager->createClient();
            $this->oauth2Client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_USER_CREDENTIALS]);
            $this->oauth2Client->setLabel(EntityAuthController::OAUTH2_CLIENT_LABEL);
            $clientManager->updateClient($this->oauth2Client);
        }
    }
    
    public function regenerateSecretAction()
    {
        $this->admin->checkAccess('regenerateSecret');

        /**
         * @var $entity Entity
         */
        $entity = $this->admin->getSubject();

        $entity->setSecret(Random::generateToken());
        $this->getDoctrine()->getManager()->persist($entity);
        $this->getDoctrine()->getManager()->flush();

        return new RedirectResponse(
            $this->admin->generateUrl('list', ['filter' => $this->admin->getFilterParameters()])
        );
    }

    public function batchActionRegenerateSecret(ProxyQueryInterface $selectedModelQuery, Request $request = null)
    {
        $this->admin->checkAccess('edit');

        $modelManager = $this->admin->getModelManager();

        $subjects = $selectedModelQuery->execute();

        try {
            foreach ($subjects as $subject) {
                /**
                 * @var $subject Entity
                 */
                $subject->setSecret(Random::generateToken());
                $modelManager->update($subject);
            }
        } catch (\Exception $e) {
            $this->addFlash('sonata_flash_error', $this->trans('flash_batch_regenerate_secret_error', ['%error%' => $e->getMessage()]));

            return new RedirectResponse(
                $this->admin->generateUrl('list', [
                    'filter' => $this->admin->getFilterParameters()
                ])
            );
        }

        $this->addFlash('sonata_flash_success', $this->trans('flash_batch_regenerate_secret_success'));

        return new RedirectResponse(
            $this->admin->generateUrl('list', [
                'filter' => $this->admin->getFilterParameters()
            ])
        );
    }

    public function generateWebUrlAction()
    {
        $this->admin->checkAccess('generateWebUrl');

        /**
         * @var $entity Entity
         */
        $entity = $this->admin->getSubject();

        // Submit credentials to OAuth2 as is and forward response (remove refresh token as not used)
        $oauth2Data['client_id']     = $this->oauth2Client->getPublicId();
        $oauth2Data['client_secret'] = $this->oauth2Client->getSecret();
        $oauth2Data['grant_type']    = OAuth2::GRANT_TYPE_USER_CREDENTIALS;
        $oauth2Data['username']      = $entity->getCode();
        $oauth2Data['password']      = $entity->getSecret();

        $oauth2Request = Request::create($this->router->generate('fos_oauth_server_token'), 'POST', $oauth2Data);
        try {
            $oauth2Response     = $this->oauth2Server->grantAccessToken($oauth2Request);
            $oauth2ResponseData = json_decode($oauth2Response->getContent(), true);

            // Set url data to be hashed
            $urlData            = [
                'entity' => $entity->getCode()
            ];
            $urlData['access_token'] = $oauth2ResponseData['access_token'];
            $urlData['date'] = date('Y-m-d\TH:i:sP');
            $urlData['hash'] = $this->hash->hash($urlData);

            return new RedirectResponse(sprintf('/?%s', http_build_query($urlData)));
        } catch (OAuth2ServerException $e) {
            throw $e;
        }
    }
}
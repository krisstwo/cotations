<?php

namespace App\Tests;

use App\Entity\Family;
use Codeception\Util\HttpCode;
use Faker\Factory;

class FamilySenarioCest
{
    public function _before(ApiTester $I)
    {
    }

    // tests
    public function endpointIsReachable(ApiTester $I)
    {
        $I->sendGET('family');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }

    /**
     * @depends endpointIsReachable
     *
     * @param ApiTester $I
     */
    public function canListFamilies(ApiTester $I)
    {
        $I->have('App\Entity\Family');
        $I->sendGET('family');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('$[0].code');
    }

    /**
     * @depends endpointIsReachable
     *
     * @param ApiTester $I
     */
    public function canGetFamily(ApiTester $I)
    {
        /**
         * @var $family Family
         */
        $family = $I->have('App\Entity\Family');
        $I->sendGET('family/' . $family->getCode());
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('$.code');
        $I->seeResponseContainsJson([
            'code' => $family->getCode()
        ]);
    }

    /**
     * @depends endpointIsReachable
     *
     * @param ApiTester $I
     */
    public function cannotGetInexistantFamily(ApiTester $I)
    {
        $faker = Factory::create();

        // Check for 404
        $I->sendGET('family/' . $faker->word);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseIsJson();
    }

    /**
     * @depends endpointIsReachable
     *
     * @param ApiTester $I
     */
    public function canCreateFamily(ApiTester $I)
    {
        $faker = Factory::create();
        $code = $faker->word;
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('family', ['code' => $code, 'label' => $faker->word]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('$.code');
        $I->seeResponseContainsJson([
            'code' => $code
        ]);

        // Verify data saved
        $I->seeInRepository(Family::class, ['code' => $code]);
    }

    /**
     * @depends endpointIsReachable
     *
     * @param ApiTester $I
     */
    public function cannotCreateInvalidFamily(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('family', []);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();
    }

    /**
     * @depends endpointIsReachable
     *
     * @param ApiTester $I
     */
    public function canUpdateFamily(ApiTester $I)
    {
        /**
         * @var $family Family
         */
        $family = $I->have('App\Entity\Family');
        $faker = Factory::create();
        $updatedLabel = $family->getLabel() . $faker->numberBetween(0, 100);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('family/' . $family->getCode(), ['label' => $updatedLabel]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('$.code');
        $I->seeResponseContainsJson([
            'code' => $family->getCode(),
            'label' => $updatedLabel
        ]);

        // Verify data saved
        $I->seeInRepository(Family::class, ['code' => $family->getCode(), 'label' => $updatedLabel]);
    }

    /**
     * @depends endpointIsReachable
     *
     * @param ApiTester $I
     */
    public function cannotUpdateInvalidFamily(ApiTester $I)
    {
        /**
         * @var $family Family
         */
        $family = $I->have('App\Entity\Family');
        $updatedLabel = '';
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('family/' . $family->getCode(), ['label' => $updatedLabel]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();

        // Verify data untouched
        $I->seeInRepository(Family::class, ['code' => $family->getCode(), 'label' => $family->getLabel()]);
    }

    /**
     * @depends endpointIsReachable
     *
     * @param ApiTester $I
     */
    public function canDeleteFamily(ApiTester $I)
    {
        /**
         * @var $family Family
         */
        $family = $I->have('App\Entity\Family');

        // The actual delete
        $I->sendDELETE('family/' . $family->getCode());
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $I->dontSeeInRepository(Family::class, ['code' => $family->getCode()]);
    }

    /**
     * @depends endpointIsReachable
     *
     * @param ApiTester $I
     */
    public function cannotDeleteInexistantFamily(ApiTester $I)
    {
        $faker = Factory::create();

        // Check for 404
        $I->sendDELETE('family/' . $faker->word);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseIsJson();
    }
}

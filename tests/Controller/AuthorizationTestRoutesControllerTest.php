<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class AuthorizationTestRoutesControllerTest
 * @package App\Tests\Controller
 */
class AuthorizationTestRoutesControllerTest extends AbstractControllerTest
{
    /**
     * @return void
     * @throws \Exception
     */
    public function testUserRoleRequired()
    {
        $this->runCommand('doctrine:fixtures:load --append --group=UserFixtures');
        $this->output->writeln("\r\n<info>Test a route where user role (ROLE_USER) is required:</info>");
        $this->client->catchExceptions(false);

        // Valid request without JWT token
        $this->output->writeln("<info>Invalid request without JWT token ...</info>");
        $exceptionThrown = false;
        try {
            $this->client->request('GET', '/authorization-tests/user-role');
        } catch (AccessDeniedException $e) {
            $exceptionThrown = true;
        }
        $this->assertEquals(true, $exceptionThrown);

        // Valid request with ROLE_USER
        $this->output->writeln("\n<info>Valid request with ROLE_USER ...</info>");
        $this->client->request('POST', '/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode(['username' => 'eleven@cvlt.dev', 'password' => 'Eggo']));

        if (!$token = $this->getToken()) {
            $this->output->writeln("<info>Secure httponly cookie token extractor is enabled ... Great!</info>");
            $this->client->request('GET', '/authorization-tests/user-role');
        } else {
            $this->output->writeln("<error>lexik_jwt_authentication.token_extractors.authorization_header enabled</error>");
            $this->output->writeln("<error>This type of autentication is don't provide security against XSS attacks!</error>");
            $this->output->writeln("<error>more info: https://blog.liplex.de/improve-security-when-working-with-jwt-and-symfony/</error>");
            $this->client->request('GET', '/authorization-tests/user-role', [], [], parent::getAuthHeaders($token));
        }
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testAdminRoleRequired()
    {
        $this->runCommand('doctrine:fixtures:load --append --group=UserFixtures');
        $this->output->writeln("\r\n<info>Test a route where admin role (ROLE_ADMIN) is required:</info>");
        $this->client->catchExceptions(false);

        // Valid request without JWT token
        $this->output->writeln("<info>Invalid request without JWT token ...</info>");
        $exceptionThrown = false;
        try {
            $this->client->request('GET', '/authorization-tests/admin-role');
        } catch (AccessDeniedException $e) {
            $exceptionThrown = true;
        }
        $this->assertEquals(true, $exceptionThrown);

        // Valid request with ROLE_USER
        $this->output->writeln("\n<info>Invalid request with ROLE_USER ...</info>");
        $this->client->request('POST', '/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode(['username' => 'eleven@cvlt.dev', 'password' => 'Eggo']));

        $exceptionThrown = false;
        try {
            if (!$token = $this->getToken()) {
                $this->client->request('GET', '/authorization-tests/admin-role');
            } else {
                $this->client->request('GET', '/authorization-tests/admin-role', [], [], parent::getAuthHeaders($token));
            }
        } catch (AccessDeniedException $e) {
            $exceptionThrown = true;
        }
        $this->assertEquals(true, $exceptionThrown);

        // Valid request with ROLE_ADMIN
        $this->output->writeln("\n<info>Valid request with ROLE_ADMIN ...</info>");
        $this->client->request('POST', '/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode(['username' => 'dextermorgan@cvlt.dev', 'password' => 'Debra']));

        if (!$token = $this->getToken()) {
            $this->output->writeln("<info>Secure httponly cookie token extractor is enabled ... Great!</info>");
            $this->client->request('GET', '/authorization-tests/admin-role');
        } else {
            $this->output->writeln("<error>lexik_jwt_authentication.token_extractors.authorization_header enabled</error>");
            $this->output->writeln("<error>This type of autentication is don't provide security against XSS attacks!</error>");
            $this->output->writeln("<error>more info: https://blog.liplex.de/improve-security-when-working-with-jwt-and-symfony/</error>");
            $this->client->request('GET', '/authorization-tests/admin-role', [], [], parent::getAuthHeaders($token));
        }
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }
}
<?php

namespace Cyve\PasswordManagerBundle\Tests\Controller;

use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ResetPasswordTest extends WebTestCase
{
    use RecreateDatabaseTrait;

    public function test()
    {
        $client = self::createClient();
        $userProvider = self::getContainer()->get('security.user.provider.concrete.app_user_provider');
        $user = $userProvider->loadUserByIdentifier('lorem@mail.com');
        $loginLinkHandler = self::getContainer()->get('security.authenticator.login_link_handler.main');
        $loginLinkDetails = $loginLinkHandler->createLoginLink($user);

        $client->request('GET', $loginLinkDetails->getUrl());
        $this->assertResponseRedirects('http://localhost/password/update');

        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier le mot de passe');
    }

    public function testInvalidToken()
    {
        $client = self::createClient();
        $client->request('GET', '/password/reset?user=admin@mail.com&expires=0&hash=foo');

        $this->assertResponseRedirects('http://localhost/login');

        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-danger', 'Lien de connexion invalide ou expiré.');
    }
}

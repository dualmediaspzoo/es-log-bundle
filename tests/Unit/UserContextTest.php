<?php

namespace DualMedia\EsLogBundle\Tests\Unit;

use DualMedia\EsLogBundle\UserContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[Group('unit')]
#[CoversClass(UserContext::class)]
class UserContextTest extends TestCase
{
    use ServiceMockHelperTrait;

    #[TestWith([true, true, true])]
    #[TestWith([false, true, true])]
    #[TestWith([false, false, false])]
    #[TestWith([false, true, false])]
    public function testGetUser(
        bool $hasAlreadyUser,
        bool $hasToken,
        bool $hasUserFromToken
    ): void {
        $alreadyUser = $this->createMock(UserInterface::class);
        $userFromToken = $this->createMock(UserInterface::class);
        $userContext = $this->createRealMockedServiceInstance(UserContext::class);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')
            ->willReturn($hasUserFromToken ? $userFromToken : null);

        $this->getMockedService(TokenStorageInterface::class)
            ->expects(static::exactly((int)!$hasAlreadyUser))
            ->method('getToken')
            ->willReturn($hasToken ? $token : null);

        if ($hasAlreadyUser) {
            $userContext->setUser($alreadyUser);
        }
        $result = $userContext->getUser();
        match (true) {
            $hasAlreadyUser => static::assertSame($alreadyUser, $result),
            $hasUserFromToken && $hasToken => static::assertSame($userFromToken, $result),
            default => static::assertSame(null, $result),
        };
    }

    #[TestWith([])]
    #[TestWith([false])]
    public function testSetUser(
        bool $hasUser = true
    ): void {
        $user = $this->createMock(UserInterface::class);
        $userContext = $this->createRealMockedServiceInstance(UserContext::class);
        $userContext->setUser($hasUser ? $user : null);
        static::assertSame($hasUser ? $user : null, $userContext->getUser());
    }
}

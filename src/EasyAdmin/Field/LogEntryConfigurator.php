<?php

namespace DualMedia\EsLogBundle\EasyAdmin\Field;

use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;

class LogEntryConfigurator implements FieldConfiguratorInterface
{
    public function __construct(
        private readonly AdminUrlGeneratorInterface|null $adminUrlGenerator = null
    ) {
    }

    public function supports(
        FieldDto $field,
        EntityDto $entityDto
    ): bool {
        return LogEntryField::class === $field->getFieldFqcn();
    }

    public function configure(
        FieldDto $field,
        EntityDto $entityDto,
        AdminContext $context
    ): void {
        /** @var Request $request */
        $request = $field->getCustomOption(LogEntryField::OPT_REQUEST);
        $pageSize = $field->getCustomOption(LogEntryField::OPT_PAGE_SIZE) ?? 5;

        if (!$request->query->has(LogEntryField::OPT_PAGE_SIZE)) {
            $request->query->set(LogEntryField::OPT_PAGE_SIZE, $pageSize);
        }

        $field->setLabel(false);
        $field->setCustomOption('controllerUrl', $this->getControllerUrl($request, $context));
    }

    private function getControllerUrl(
        Request $request,
        AdminContext $context
    ): string {
        $routeParametersForReferrer = $request->query->all();
        unset($routeParametersForReferrer[EA::REFERRER]);
        $currentPageReferrer = sprintf('%s%s?%s', $request->getBaseUrl(), $request->getPathInfo(), http_build_query($routeParametersForReferrer));

        $this->adminUrlGenerator->setReferrer($currentPageReferrer); // this must be done before we start doing link stuff

        $url = $this->adminUrlGenerator->unsetAll()
            ->setAll($request->query->all())
            ->setController($context->getCrud()->getControllerFqcn())
            ->setEntityId($context->getEntity()->getPrimaryKeyValueAsString())
            ->setAction('listLogsHtml')
            ->generateUrl();

        $this->adminUrlGenerator->removeReferrer();

        return $url;
    }
}

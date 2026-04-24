<?php

namespace DualMedia\EsLogBundle\EasyAdmin\Field;

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

    #[\Override]
    public function supports(
        FieldDto $field,
        EntityDto $entityDto
    ): bool {
        return LogEntryField::class === $field->getFieldFqcn();
    }

    #[\Override]
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

    /**
     * @param AdminContext<object> $context
     */
    private function getControllerUrl(
        Request $request,
        AdminContext $context
    ): string {
        return $this->adminUrlGenerator->unsetAll()
            ->setAll($request->query->all())
            ->setController($context->getCrud()->getControllerFqcn())
            ->setEntityId($context->getEntity()->getPrimaryKeyValueAsString())
            ->setAction('listLogsHtml')
            ->generateUrl();
    }
}

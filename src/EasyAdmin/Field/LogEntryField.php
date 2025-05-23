<?php

namespace DualMedia\EsLogBundle\EasyAdmin\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\HttpFoundation\Request;

class LogEntryField implements FieldInterface
{
    use FieldTrait;

    public const string OPT_REQUEST = 'request';
    public const string OPT_PAGE_SIZE = 'pageSize';

    public static function new(
        string $propertyName,
        string|null $label = null
    ): self {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel(false)
            ->addCssClass('field-es-logs')
            ->setTemplatePath('@EsLog/loader.html.twig')
            ->addJsFiles('bundles/eslog/js/logs-field.js')
            ->onlyOnDetail()
            ->setRequest(new Request());
    }

    public function setRequest(
        Request $request
    ): self {
        $this->setCustomOption(self::OPT_REQUEST, $request);

        return $this;
    }

    public function getRequest(): Request
    {
        return $this->getAsDto()->getCustomOption(self::OPT_REQUEST);
    }

    public function setPageSize(
        int $pageSize
    ): self {
        $this->setCustomOption(self::OPT_PAGE_SIZE, $pageSize);

        return $this;
    }
}

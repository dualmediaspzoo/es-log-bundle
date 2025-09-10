<?php

namespace DualMedia\EsLogBundle\EasyAdmin\Trait;

use DualMedia\EsLogBundle\Builder\QueryBuilder;
use DualMedia\EsLogBundle\Builder\SearchBuilder;
use DualMedia\EsLogBundle\EasyAdmin\ElasticPaginator;
use DualMedia\EsLogBundle\Search\Processor;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;

/** @phpstan-ignore trait.unused */
trait LogActionTrait
{
    protected SearchBuilder $logsBuilder;
    protected QueryBuilder $queryBuilder;
    protected Processor $logsProcessor;
    protected AdminUrlGeneratorInterface $logsAdminUrlGenerator;

    #[Required]
    public function setListLogsServices(
        SearchBuilder $builder,
        Processor $processor,
        AdminUrlGenerator $generator,
        QueryBuilder $queryBuilder
    ): void {
        $this->logsBuilder = $builder;
        $this->logsProcessor = $processor;
        $this->logsAdminUrlGenerator = $generator;
        $this->queryBuilder = $queryBuilder;
    }

    public function listLogsHtml(
        AdminContext $context
    ): Response {
        if (!$this->isGranted(Permission::EA_EXECUTE_ACTION, ['action' => Action::INDEX, 'entity' => null])) {
            throw new ForbiddenActionException($context);
        }

        $request = $context->getRequest();

        $context->getCrud()->setPageName('listLogsHtml');

        $pageSize = (int)($request->query->get('pageSize') ?? 10);
        $page = (int)($request->query->get('page') ?? 1);

        if ($pageSize > 50) {
            $pageSize = 50;
        }

        $entityDto = $context->getEntity();

        $results = $this->logsProcessor->process(
            $this->logsBuilder->start()
                ->perPage($pageSize)
                ->page($page - 1)
                ->build(
                    $this->queryBuilder->start()
                        ->class($entityDto->getFqcn())
                        ->id($entityDto->getPrimaryKeyValueAsString())
                        ->build()
                )
        );

        $paginator = new ElasticPaginator(
            $results,
            $this->logsAdminUrlGenerator
        );

        $templateParameters = [
            // new context should be passed to twig here cause easy admin passes only globally context from container as ea variable
            'admin_context' => $context,
            'pageName' => 'listLogsHtml',
            'entities' => $results->entries,
            'paginator' => $paginator,
        ];

        return new Response(
            $this->container->get(Environment::class)
                ->render(
                    '@EsLog/main.html.twig',
                    $templateParameters
                )
        );
    }
}

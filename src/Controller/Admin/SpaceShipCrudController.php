<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use Cake\Core\App;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use App\Entity\SpaceShip;


class SpaceShipCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SpaceShip::class;
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Корабль')
            ->setEntityLabelInPlural('Космические корабли')
            ->setSearchFields(['name'])
            ->setDefaultSort(['createdAt' => 'DESC'])
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('publisher', 'Автор'))
            ->add(EntityFilter::new('likes', 'Лайки'))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Название');
        yield AssociationField::new('publisher', 'Автор');

        yield TextareaField::new('description', 'Описание')
            ->hideOnIndex()
        ;
        yield TextField::new('image', 'Изображение')
            ->onlyOnIndex()
        ;
        $createdAt = DateTimeField::new('createdAt')->setFormTypeOptions([
            'years' => range(date('Y '), date('Y') + 5),
            'widget' => 'single_text',
        ]);
        if (Crud::PAGE_EDIT === $pageName) {
            yield $createdAt->setFormTypeOption('disabled', true);
        } else {
            yield $createdAt;
        }
    }
}

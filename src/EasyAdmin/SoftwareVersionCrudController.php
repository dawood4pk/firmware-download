<?php

declare(strict_types=1);

namespace App\EasyAdmin;

use App\Entity\SoftwareVersion;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

final class SoftwareVersionCrudController extends AbstractCrudController
{
    /** All known product names. Update this list when a new product line is introduced. */
    private const PRODUCT_NAMES = [
        'MMI Prime CIC'     => 'MMI Prime CIC',
        'MMI Prime NBT'     => 'MMI Prime NBT',
        'MMI Prime EVO'     => 'MMI Prime EVO',
        'MMI PRO CIC'       => 'MMI PRO CIC',
        'MMI PRO NBT'       => 'MMI PRO NBT',
        'MMI PRO EVO'       => 'MMI PRO EVO',
        'LCI MMI Prime CIC' => 'LCI MMI Prime CIC',
        'LCI MMI Prime NBT' => 'LCI MMI Prime NBT',
        'LCI MMI Prime EVO' => 'LCI MMI Prime EVO',
        'LCI MMI PRO CIC'   => 'LCI MMI PRO CIC',
        'LCI MMI PRO NBT'   => 'LCI MMI PRO NBT',
        'LCI MMI PRO EVO'   => 'LCI MMI PRO EVO',
    ];

    public static function getEntityFqcn(): string
    {
        return SoftwareVersion::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Software Version')
            ->setEntityLabelInPlural('Software Versions')
            ->setPageTitle(Crud::PAGE_INDEX, 'All Software Versions')
            ->setPageTitle(Crud::PAGE_NEW, 'Add Software Version')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Software Version')
            ->setDefaultSort(['name' => 'ASC', 'systemVersion' => 'ASC'])
            ->setSearchFields(['name', 'systemVersion', 'systemVersionAlt'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, Action::DELETE]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('name')->setChoices(self::PRODUCT_NAMES))
            ->add(TextFilter::new('systemVersion'))
            ->add(BooleanFilter::new('isLatest'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield ChoiceField::new('name', 'Product Name')
            ->setChoices(self::PRODUCT_NAMES)
            ->setHelp(
                'Select the product family. Names starting with <strong>LCI</strong> are for '.
                'the LCI-generation hardware (B_C_, B_N_G_, B_E_G_ HW versions). '.
                'All other names are for standard hardware (CPAA_ HW versions).'
            );

        yield TextField::new('systemVersion', 'System Version')
            ->setHelp(
                'The full version string <em>with</em> the leading "v", '.
                'e.g. <code>v3.3.7.mmipri.c</code>. This is displayed in admin lists only.'
            );

        yield TextField::new('systemVersionAlt', 'System Version (customer input)')
            ->setHelp(
                'The version string <em>without</em> the leading "v", '.
                'e.g. <code>3.3.7.mmipri.c</code>. '.
                'This is what the customer types into the download form. '.
                'It must exactly match what their device reports (case-insensitive).'
            );

        yield UrlField::new('link', 'Generic Download Link')
            ->setRequired(false)
            ->setHelp(
                'The general firmware download link. Used for standard (non-LCI) entries. '.
                'Leave empty for LCI entries — they use the ST or GD links below.'
            );

        yield UrlField::new('st', 'ST Download Link (CIC)')
            ->setRequired(false)
            ->setHelp(
                'Download link for <strong>ST hardware</strong> (CIC systems, CPAA_ or B_C_ HW versions). '.
                'Leave empty if not applicable for this product.'
            );

        yield UrlField::new('gd', 'GD Download Link (NBT / EVO)')
            ->setRequired(false)
            ->setHelp(
                'Download link for <strong>GD hardware</strong> (NBT / EVO systems, CPAA_G_, B_N_G_, B_E_G_ HW versions). '.
                'Leave empty if not applicable for this product.'
            );

        yield BooleanField::new('isLatest', 'Is Latest Version')
            ->setHelp(
                'Enable this for the <strong>most recent firmware</strong> in this product family. '.
                'Customers already on this version will see "Your system is up to date" '.
                'and will not receive any download links. '.
                'Only one entry per product group should be marked as latest.'
            )
            ->renderAsSwitch(true);

        yield DateTimeField::new('createdAt')->hideOnForm()->hideOnIndex();
        yield DateTimeField::new('updatedAt')->hideOnForm()->hideOnIndex();
    }
}

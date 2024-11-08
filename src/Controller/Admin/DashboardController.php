<?php

namespace App\Controller\Admin;

use App\Entity\SpaceShip;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardController extends AbstractDashboardController
{
    private string $avatarBasePath = '';
    
    
    // public function __construct(
    //     private TranslatorInterface $translator,
    //     #[Autowire('%upload_dir_name%')] string $uploadDirName,
    //     #[Autowire('%avatar_dir_name%')] string $avatarDirName,
    // ) {
    //     $this->avatarBasePath = "/{$uploadDirName}/{$avatarDirName}";
    // }
    #[Route('/admin', name: 'admin')]    public function index(): Response
    {

        $routeBuilder = $this->container->get(AdminUrlGenerator::class);
        $url = $routeBuilder->setController(SpaceShipCrudController::class)->generateUrl();

        return $this->redirect($url);

    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Symfony Learning')
            ->setFaviconPath('favicon.ico')
            ->setTranslationDomain('admin')
            ->renderContentMaximized();
        }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linktoRoute('Back to the website', 'fas fa-home', 'front');
        yield MenuItem::linkToCrud('SpaceShips', 'fas fa-list', SpaceShip::class);
    }

    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->setDefaultSort([
                'id' => 'ASC',
            ])
            ->showEntityActionsInlined()
            ->setPaginatorPageSize(10)
            ->setPaginatorRangeSize(2)
            ->hideNullValues()
            ->overrideTemplate('crud/field/id', 'easyadmin/crud/field/id_with_icon.html.twig');
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        /** @var User $user */
        $avatarName = $user->getAvatar();

        $userMenu = parent::configureUserMenu($user);

        if ($avatarName) {
            $userMenu->setAvatarUrl($this->avatarBasePath . DIRECTORY_SEPARATOR . $avatarName);
        }

        return $userMenu;
    }
}

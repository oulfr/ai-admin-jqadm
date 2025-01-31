<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016-2021
 * @package Admin
 * @subpackage JQAdm
 */


namespace Aimeos\Admin\JQAdm\Common\Decorator;


/**
 * Page decorator for JQAdm clients
 *
 * @package Admin
 * @subpackage JQAdm
 */
class Page extends Base
{
	/**
	 * Sets the view object and adds the required page data to the view
	 *
	 * @param \Aimeos\MW\View\Iface $view The view object which generates the admin output
	 * @return \Aimeos\Admin\JQAdm\Iface Reference to this object for fluent calls
	 */
	public function setView( \Aimeos\MW\View\Iface $view ) : \Aimeos\Admin\JQAdm\Iface
	{
		parent::setView( $view );

		// set first to be able to show errors occuring afterwards
		$view->pageParams = $this->getClientParams();
		$context = $this->getContext();

		$siteManager = \Aimeos\MShop::create( $context, 'locale/site' );
		$langManager = \Aimeos\MShop::create( $context, 'locale/language' );
		$customerManager = \Aimeos\MShop::create( $context, 'customer' );

		if( $view->access( ['super'] ) )
		{
			$view->pageSiteItem = $siteManager->find( $view->param( 'site', 'default' ) );
		}
		else
		{
			$siteid = $customerManager->get( $context->getUserId() )->getSiteId();
			$search = $siteManager->filter();

			$search->add( $search->and( [
				$search->is( 'locale.site.code', '==', $view->param( 'site', 'default' ) ),
				$search->is( 'locale.site.siteid', $view->access( ['admin'] ) ? '=~' : '==', $siteid )
			] ) )->slice( 0, 1 );

			$view->pageSiteItem = $siteManager->search( $search )->first();
		}

		$view->pageInfo = $context->getSession()->pull( 'info', [] );
		$view->pageI18nList = $this->getAimeos()->getI18nList( 'admin' );
		$view->pageLangItems = $langManager->search( $langManager->filter( true ) );
		$view->pageSitePath = $siteManager->getPath( $view->pageSiteItem->getId() );

		$this->getClient()->setView( $view );
		return $this;
	}
}

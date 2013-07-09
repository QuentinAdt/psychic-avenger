<?php

//Sdz/BlogBundle/Controller/BlogController.php

namespace Sdz\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\SolariumBundle\DependencyInjection\NelmioSolariumExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class BlogController extends Controller
{
	public function lister_sejours_paysAction($pays,$start)
	{
		// Requete
		$my_query = 'manufacturer:'.$pays.' AND description:*inclus* AND terms:[* TO *]';
		
		// Create new solarium instance
		$client = $this->get('solarium.client');
		$select = $client->createSelect();
		$select->setQuery($my_query);
		$select->addSort('price', $select::SORT_ASC);
		$select->setRows(1500);
		if (isset($start))
		{
			$select->setStart($start);
		}
		$resultsolr = $client->select($select);
		$nb_result_pays =  $resultsolr->getNumFound();
	
		// Nav facettes
		$query = $client->createSelect();
		$query->createFilterQuery('inclus')->setQuery('description:*inclus* AND terms:[* TO *]');
		$facetSet = $query->getFacetSet();
		$facetSet->createFacetField('pays')->setField('manufacturer')->setMincount(1);
		$resultset = $client->select($query);
		
		$facet = $resultset->getFacetSet()->getFacet('pays');
	
		/* Pagination
		
		$paginator  = $this->get('knp_paginator');
		$pagination = $paginator->paginate(
		        $resultsolr,
		        $this->get('request')->query->get('page', 1),10);
		
		*/
		return $this->render('SdzBlogBundle:Blog:sejours_par_pays.html.twig',
				array('resultsolr' => $resultsolr,
					'pays' => $pays,
					'facet' => $facet,
					'nb_result_pays' => $nb_result_pays/*,
					'pagination' => $pagination*/));
	}			

	public function indexAction()
	{
	
		//FACETTE - TOUS LES VOYAGES - Return destination offers + number of offers available
		$client = $this->get('solarium.client');
		$query = $client->createSelect();
		$query->createFilterQuery('inclus')->setQuery('description:*inclus* AND terms:[* TO *]'); // [* TO *] means "not empty"
		$facetSet = $query->getFacetSet();
		$facetSet->createFacetField('pays')->setField('manufacturer')->setMincount(1);
		$resultset = $client->select($query);
		$facet = $resultset->getFacetSet()->getFacet('pays');

		return $this->render('SdzBlogBundle:Blog:index.html.twig', array(
	                        'facet' => $facet,
				 ));
	}

}

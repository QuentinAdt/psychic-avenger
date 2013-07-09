<?php

//Sdz/BlogBundle/Controller/BlogController.php

namespace Sdz\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sdz\BlogBundle\Entity\Article; // Mapping BDD Article
use Sdz\BlogBundle\Entity\Image; // Mapping BDD Images
use Sdz\BlogBundle\Entity\Commentaire; // Mapping BDD Commentaires
use Sdz\BlogBundle\Entity\Categorie; //Mapping BDD Categories
use Sdz\BlogBundle\Entity\ArticleCompetence; //Mapping BDD Competences
use Nelmio\SolariumBundle\DependencyInjection\NelmioSolariumExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

//use Nelmio\SolariumBundle\NelmioSolariumBundle;

class BlogController extends Controller
{
	public function lister_sejours_paysAction($pays,$start)
	{
// Requete
$my_query = 'manufacturer:'.$pays.' AND description:*inclus* AND terms:[* TO *]';

// Rendered result in body
		$client = $this->get('solarium.client');
		$select = $client->createSelect();
		$select->setQuery($my_query);
//		$select->setQuery('description:*inclu*');
//		$select->createFilterQuery('inclus')->setQuery('description:*inclu*');
		$select->addSort('price', $select::SORT_ASC);
		$select->setRows(1500);
		if (isset($start)) {$select->setStart($start);}
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

	public function indexAction($page)
	{

//FACETTE - TOUS LES VOYAGES - Retourne liste des desti + nombre offres
$client = $this->get('solarium.client');
$query = $client->createSelect();
$query->createFilterQuery('inclus')->setQuery('description:*inclus* AND terms:[* TO *]');

$facetSet = $query->getFacetSet();
$facetSet->createFacetField('pays')->setField('manufacturer')->setMincount(1);
$resultset = $client->select($query);

$facet = $resultset->getFacetSet()->getFacet('pays');

               return $this->render('SdzBlogBundle:Blog:index.html.twig', array(
    //                    'articles' => $articles,
                        'facet' => $facet,
  //                      'page' => $page,
//                        'nombrePage' => ceil(count($articles)/3)
			 ));


	}

	public function rechercherAction()
	{
//	return $this->render('SdzBlogBundle:Blog:solarium.html.twig');
	return $this->render('NelmioSolariumBundle:DataCollector:solarium.html.twig');
	}
	public function menuAction()
	{

	$em = $this->getDoctrine()->getManager();
	$liste_articles = $em->getRepository('SdzBlogBundle:Article')->findAll();
	return $this->render('SdzBlogBundle:Blog:menu.html.twig', array(
        	'liste_articles' => $liste_articles // C'est ici tout l'intért $: le contrôleur passe les variables nécessaires au template !
                ));
        }

	public function menu_headerAction()
	{
		$em = $this->getDoctrine()->getManager();
		$liste_articles = $em->getRepository('SdzBlogBundle:Article')->findAll();
		return $this->render('SdzBlogBundle:Blog:menu-header.html.twig', array(
			'liste_articles' => $liste_articles // C'est ici tout l'intért : le contrôleur passe les variables nécessaires au template !
		));
	}

	public function supprimerAction($id)
	{

    // On récupère l'EntityManager
    $em = $this->getDoctrine()
               ->getManager();
 
    // On récupère l'entité correspondant à l'id $id
    $article = $em->getRepository('SdzBlogBundle:Article')
                  ->find($id);
// MARCHE PO	$article->removeArticle(); 
    // Si l'article n'existe pas, on affiche une erreur 404
    if ($article == null) {
      throw $this->createNotFoundException('Article[id='.$id.'] inexistant');
    }
 
    if ($this->get('request')->getMethod() == 'POST') {
      // Si la requête est en POST, on supprimera l'article
       
      $this->get('session')->getFlashBag()->add('info', 'Article bien supprimé');
 
      // Puis on redirige vers l'accueil
      return $this->redirect( $this->generateUrl('sdzblog_index') );
    }
 
    // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
    return $this->render('SdzBlogBundle:Blog:supprimer.html.twig', array(
     	 'article' => $article
 	    ));

	}

	public function ajouterAction()
	{
   // Création de l'entité

	if($this->get('request')->getMethod() == 'POST')
	{
		// TODO : creation et gestion du formulaire 

		$this->get('session')->getFlashBag()->add('notice', 'article ajoute');
		return $this->redirect($this->generateUrl('sdzblog_voir', array('id' => 1)) );
	}

		// Pas POST ? Afficher formulaire !

	return $this->render('SdzBlogBundle:Blog:ajouter.html.twig');

	}

  public function voirAction(Article $article)
  {

   $listeArticleCompetence = $this->getDoctrine()
                                   ->getManager()
                                   ->getRepository('SdzBlogBundle:ArticleCompetence')
                                   ->findByArticle($article->getId());
 
    return $this->render('SdzBlogBundle:Blog:voir.html.twig', array(
      'article'                 => $article,
      'listeArticleCompetence'  => $listeArticleCompetence,
	'liste_commentaires' => 'a',
	'' => '',
	'' => '',
    ));

/*

    // On récupère l'EntityManager
    $em = $this->getDoctrine()
               ->getManager();
 
    // On récupère l'entité correspondant à l'id $id
    $article = $em->getRepository('SdzBlogBundle:Article')
                  ->find($id);
 
    if($article === null)
    {
      throw $this->createNotFoundException('Article[id='.$id.'] inexistant.');
    }
 
    // On récupère la liste des commentaires
    $liste_commentaires = $em->getRepository('SdzBlogBundle:Commentaire')
                             ->findAll();

    // On récupère la liste d categories
    $liste_categories = $em->getRepository('SdzBlogBundle:Categorie')
                             ->findAll();

    // On récupère la liste d competences
    $liste_articleCompetence = $em->getRepository('SdzBlogBundle:ArticleCompetence')
                             ->findByArticle($article->getId());


    // Puis modifiez la ligne du render comme ceci, pour prendre en compte l'article :
    return $this->render('SdzBlogBundle:Blog:voir.html.twig', array(
     	 'article'        => $article, 'liste_categories' => $liste_categories, 'liste_articleCompetence' => $liste_articleCompetence
	    ));
*/
	}

	public function modifierAction($id)
	{
		// Ici, on récupérera l'article correspondant à $id
		$em = $this->getDoctrine()->getManager();
		$article = $em->getRepository('SdzBlogBundle:Article')->find($id);
		if ($article === null) { throw $this->createNotFoundException('Article[id='.$id.'] inexistant.');}

		// recup cat
		$liste_categories = $em->getRepository('SdzBlogBundle:Categorie')->findAll();

		// Attache chaque cat a article

		foreach($liste_categories as $categorie) { $article->addCategorie($categorie); }

		$em->flush();

		return new Response('Ok');

		// Ici, on s'occupera de la création et de la gestion du formulaire

	}
}
